<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\XenditFee;
use App\Services\XenditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->package = Package::factory()->create();
        $this->customer = Customer::factory()->create([
            'package_id' => $this->package->id,
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_customer_failed_login()
    {
        $response = $this->from(route('portal.login'))
            ->post(route('portal.authenticate'), [
                'login' => 'invalid_id',
            ]);

        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHasErrors(['login']);
        $this->assertGuest('customer');
    }

    public function test_customer_can_login_and_see_dashboard()
    {
        $response = $this->post(route('portal.authenticate'), [
            'login' => $this->customer->customer_code,
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($this->customer, 'customer');

        $dashboardResponse = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.dashboard'));

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($this->customer->full_name);
        $dashboardResponse->assertSee($this->package->name);
    }

    public function test_customer_can_view_invoice_list()
    {
        Invoice::factory()->count(3)->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $this->assertEquals(3, $this->customer->invoices()->count());
    }

    public function test_customer_can_view_specific_invoice()
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.invoice.show', $invoice));

        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);
    }

    public function test_customer_cannot_access_other_customer_invoice()
    {
        $otherCustomer = Customer::factory()->create();
        $otherInvoice = Invoice::factory()->create(['customer_id' => $otherCustomer->id]);

        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.invoice.show', $otherInvoice));

        // It should return 403 Forbidden due to policy/gate check
        $response->assertStatus(403);
    }

    public function test_customer_can_download_pdf()
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.invoice.pdf', $invoice));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_customer_can_logout()
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->post(route('portal.logout'));

        $response->assertRedirect(route('portal.login'));
        $this->assertGuest('customer');
    }

    public function test_old_portal_url_redirects_to_subdomain()
    {
        $response = $this->get('/portal');
        
        $subdomain = config('app.payment_domain', 'payment.djavva.net');
        $response->assertRedirect('https://' . $subdomain . '/login');
    }

    public function test_checkout_cannot_be_triggered_with_get_request()
    {
        $invoice = Invoice::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->get(route('payment.checkout', ['hash' => $invoice->payment_hash]));

        $response->assertStatus(405);
    }

    public function test_public_maintenance_endpoints_are_not_registered()
    {
        $this->get('/run-migrations')->assertNotFound();
        $this->get('/check-missing-invoices')->assertNotFound();
    }

    public function test_repeated_checkout_reuses_recent_xendit_invoice()
    {
        Cache::clear();

        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'unpaid',
        ]);
        $fee = XenditFee::create([
            'name' => 'QRIS',
            'code' => 'qris',
            'type' => 'fixed',
            'value' => 1000,
            'is_active' => true,
        ]);

        $xendit = Mockery::mock(XenditService::class);
        $xendit->shouldReceive('createInvoice')
            ->once()
            ->andReturn(['invoice_url' => 'https://checkout.example.test/invoice']);
        $this->app->instance(XenditService::class, $xendit);

        $checkoutUrl = route('payment.checkout', ['hash' => $invoice->payment_hash]);
        $payload = ['fee_id' => $fee->id];

        $this->post($checkoutUrl, $payload)->assertRedirect('https://checkout.example.test/invoice');
        $this->post($checkoutUrl, $payload)->assertRedirect('https://checkout.example.test/invoice');
    }
}
