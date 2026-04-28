<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;

class InvoiceIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_generation_sets_due_date_to_25th()
    {
        // 1. Setup Data dan Admin
        $admin = User::factory()->create(['role' => 'admin']);
        
        $package = Package::factory()->create(['monthly_price' => 200000]);
        $customer = Customer::factory()->create([
            'package_id' => $package->id,
            'active_since' => Carbon::create(2026, 4, 15),
            'status' => 'active'
        ]);

        $targetPeriod = '2026-04'; // Kita scan untuk bulan April (Usage)

        // 2. Action
        $response = $this->actingAs($admin)
            ->post(route('admin.invoices.generate'), [
                'period' => $targetPeriod
            ]);

        // 3. Assertions
        $response->assertStatus(302);
        
        // Cek tagihan period April (prorata) yang terbuat
        $invoiceApril = Invoice::where('customer_id', $customer->id)
            ->whereMonth('period_start', 4)
            ->first();
        
        $this->assertNotNull($invoiceApril);
        // Jatuh tempo harus 2026-05-25 (Bulan penggunaan April + 1 Bulan)
        $this->assertEquals('2026-05-25', $invoiceApril->due_date->format('Y-m-d'));
    }

    public function test_check_overdue_command_isolates_customer()
    {
        // 1. Setup Customer and Overdue Invoice
        $package = Package::factory()->create();
        $customer = Customer::factory()->create([
            'status' => 'active',
            'username' => 'testuser@djavva.net'
        ]);

        // Tagihan yang sudah lewat jatuh tempo
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'unpaid',
            'due_date' => now()->subDays(1)->toDateString() // Kemarin
        ]);

        // 2. Run Command
        $this->artisan('invoices:check-overdue')
            ->expectsOutputToContain('Menemukan 1 tagihan jatuh tempo')
            ->assertExitCode(0);

        // 3. Assertions
        $invoice->refresh();
        $customer->refresh();

        $this->assertEquals('overdue', $invoice->status);
        $this->assertEquals('suspend', $customer->status);
    }

    public function test_isolation_landing_page_is_accessible()
    {
        $response = $this->get(route('portal.isolated'));

        $response->assertStatus(200);
        $response->assertSee('Layanan Terisolasi');
        $response->assertSee('PT DJAVVA NETWORK SOLUTION');
    }
}
