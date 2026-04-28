<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-04-28 10:00:00');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_generates_full_monthly_invoice_for_active_customers()
    {
        $this->withoutExceptionHandling();
        $package = Package::factory()->create(['monthly_price' => 200000]);
        // Set active_since to exactly start of current month to isolate to 1 invoice
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'status' => 'active',
            'active_since' => '2026-04-01',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), ['period' => '2026-04']);

        $invoices = Invoice::where('customer_id', $customer->id)->get();
        $this->assertCount(1, $invoices, 'Should generate exactly 1 invoice for April');
        $this->assertEquals(200000, (float)$invoices->first()->amount);
        $this->assertEquals('2026-04-01', $invoices->first()->period_start->toDateString());
    }

    /** @test */
    public function it_calculates_prorata_accurately_for_mid_month_activation()
    {
        $this->withoutExceptionHandling();
        $monthlyPrice = 300000;
        $package = Package::factory()->create(['monthly_price' => $monthlyPrice]);
        
        // Aktif 16 April. (300000 / 30) * 15 hari = 150000
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'status' => 'active',
            'active_since' => '2026-04-16',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), ['period' => '2026-04']);

        $invoice = Invoice::where('customer_id', $customer->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(150000, (float)$invoice->amount);
    }

    /** @test */
    public function it_prevents_duplicate_invoices_for_the_same_period()
    {
        $package = Package::factory()->create();
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'active_since' => '2026-04-01',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), ['period' => '2026-04']);

        $this->assertEquals(1, Invoice::where('customer_id', $customer->id)->count());

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), ['period' => '2026-04']);

        $this->assertEquals(1, Invoice::where('customer_id', $customer->id)->count(), 'Duplicate generation should be skipped');
    }

    /** @test */
    public function it_performs_scan_and_fill_for_missing_backlog_months()
    {
        $package = Package::factory()->create(['monthly_price' => 100000]);
        // Aktif 3 bulan lalu (Januari)
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'status' => 'active',
            'active_since' => '2026-01-01',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), ['period' => '2026-04']);

        // Jan, Feb, Mar, Apr = 4 Invoices
        $this->assertEquals(4, Invoice::where('customer_id', $customer->id)->count());
    }

    /** @test */
    public function it_only_generates_invoices_for_active_customers()
    {
        $package = Package::factory()->create();
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'status' => 'inactive',
            'active_since' => '2026-01-01',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), ['period' => '2026-04']);

        $this->assertEquals(0, Invoice::where('customer_id', $customer->id)->count());
    }

    /** @test */
    public function it_tracks_whatsapp_sent_status()
    {
        $this->withoutExceptionHandling();
        $package = Package::factory()->create();
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'whatsapp_number' => '628123456789',
            'active_since' => '2026-04-01',
        ]);

        // Generate invoice
        $this->actingAs($this->admin)
            ->post(route('admin.invoices.generate'), ['period' => '2026-04']);

        $invoice = Invoice::where('customer_id', $customer->id)->first();
        $this->assertFalse($invoice->is_wa_sent);

        // Send Blast
        $response = $this->actingAs($this->admin)
            ->post(route('admin.invoices.bulk_whatsapp'), ['period' => '2026-04']);

        $invoice->refresh();
        $this->assertTrue($invoice->is_wa_sent);
        $this->assertNotNull($invoice->wa_sent_at);

        // Send Blast again - should not process anyone
        $response2 = $this->actingAs($this->admin)
            ->post(route('admin.invoices.bulk_whatsapp'), ['period' => '2026-04']);
        
        $response2->assertSessionHas('error', 'Tidak ada tagihan Belum Bayar yang ditemukan untuk dikirimkan.');
    }
}
