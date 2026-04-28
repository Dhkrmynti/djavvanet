<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ProratedInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_prorated_invoice_calculation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $package = Package::create([
            'name' => 'Home 10Mbps',
            'profile' => 'Home',
            'monthly_price' => 150000,
            'is_active' => true
        ]);

        $targetDate = Carbon::create(2024, 4, 25);
        Carbon::setTestNow($targetDate);

        $customer = Customer::create([
            'package_id' => $package->id,
            'customer_code' => '123456',
            'username' => 'test@djavva.net',
            'full_name' => 'Prorata Test',
            'whatsapp_number' => '628123456789',
            'address' => 'Jl. Test No. 123',
            'status' => 'active',
            'active_since' => $targetDate->toDateString(),
            'billing_cycle' => 1,
        ]);
        
        // Generate Invoices for April
        $this->actingAs($admin)
            ->post(route('admin.invoices.generate'));

        $invoice = Invoice::where('customer_id', $customer->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(30000, $invoice->amount);
    }

    public function test_full_month_invoice_calculation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $package = Package::create([
            'name' => 'Home 10Mbps',
            'profile' => 'Home',
            'monthly_price' => 150000,
            'is_active' => true
        ]);

        // Scenario: Joined on March 1st
        // Current month is April
        Carbon::setTestNow(Carbon::create(2024, 4, 1));
        
        $customer = Customer::create([
            'package_id' => $package->id,
            'customer_code' => '123456',
            'username' => 'test@djavva.net',
            'full_name' => 'Full Test',
            'whatsapp_number' => '628123456789',
            'address' => 'Jl. Test Full No. 456',
            'status' => 'active',
            'active_since' => Carbon::create(2024, 3, 1)->toDateString(),
            'billing_cycle' => 1,
        ]);

        // Generate Invoices for April
        $this->actingAs($admin)
            ->post(route('admin.invoices.generate'));

        $invoice = Invoice::where('customer_id', $customer->id)->first();
        $this->assertNotNull($invoice);

        // Expected amount: Full 150,000
        $this->assertEquals(150000, $invoice->amount);
    }
}
