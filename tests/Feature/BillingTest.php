<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_generate_monthly_invoices()
    {
        // 1. Create a package
        $package = Package::factory()->create(['monthly_price' => 150000]);

        // 2. Create active customers starting this month
        Customer::factory()->count(3)->create([
            'package_id' => $package->id,
            'status' => 'active',
            'active_since' => now()->startOfMonth()
        ]);

        // 3. Admin triggers invoice generation for current month
        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), [
                'period' => now()->format('Y-m')
            ]);

        $response->assertStatus(302);
        
        // 4. Verify 3 invoices were created (1 per customer since they start this month)
        $this->assertEquals(3, Invoice::count());
        
        $invoice = Invoice::first();
        $this->assertEquals(150000, $invoice->amount);
    }

    public function test_customer_can_access_portal_dashboard()
    {
        $package = Package::factory()->create();
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'password' => bcrypt('password123')
        ]);

        $response = $this->post(route('portal.authenticate'), [
            'login' => $customer->customer_code,
            'password' => 'password123'
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($customer, 'customer');

        $dashboardResponse = $this->actingAs($customer, 'customer')
            ->get(route('portal.dashboard'));

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($customer->name);
    }

    public function test_customer_can_view_their_own_invoice()
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

        $otherCustomer = Customer::factory()->create();

        // Acting as own customer
        $response = $this->actingAs($customer, 'customer')
            ->get(route('portal.invoice.show', $invoice));

        $response->assertStatus(200);

        // Acting as other customer (should be forbidden or redirected)
        $forbiddenResponse = $this->actingAs($otherCustomer, 'customer')
            ->get(route('portal.invoice.show', $invoice));

        // Depending on implementation, it might be 403 or 404 or redirect
        $forbiddenResponse->assertStatus(403);
    }

    public function test_admin_can_update_invoice()
    {
        $invoice = Invoice::factory()->create(['amount' => 100000, 'status' => 'unpaid']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.invoices.update', $invoice), [
                'amount' => 125000,
                'due_date' => now()->addDays(5)->format('Y-m-d'),
                'status' => 'paid'
            ]);

        $response->assertStatus(302);
        $this->assertEquals(125000, $invoice->fresh()->amount);
        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_admin_can_delete_invoice()
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.invoices.destroy', $invoice));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_admin_can_filter_invoices_by_period()
    {
        // Create invoices for different periods
        $invoice1 = Invoice::factory()->create(['period_start' => '2026-04-01 00:00:00']);
        $invoice2 = Invoice::factory()->create(['period_start' => '2026-05-01 00:00:00']);
        
        // Filter for April via ajax data endpoint
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.invoices.data', ['period' => '2026-04']));
            
        $response->assertStatus(200);
        $response->assertJsonFragment(['invoice_number' => $invoice1->invoice_number]);
        
        // Filter for May
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.invoices.data', ['period' => '2026-05']));
            
        $response->assertStatus(200);
        $response->assertJsonFragment(['invoice_number' => $invoice2->invoice_number]);
    }

    public function test_invoices_data_endpoint_returns_json()
    {
        Invoice::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.invoices.data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data'
        ]);
        $this->assertEquals(3, $response->json('recordsTotal'));
    }

    public function test_invoices_page_loads_without_data()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices'));

        $response->assertStatus(200);
        $response->assertSee('Billing / Tagihan');
    }

    public function test_staff_data_endpoint_returns_json()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.staff.data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data'
        ]);
    }

    public function test_staff_page_loads()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Staff');
    }
}
