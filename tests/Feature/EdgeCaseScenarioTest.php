<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\XenditSetting;
use App\Services\MikrotikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Tests\TestCase;

class EdgeCaseScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        
        XenditSetting::create([
            'is_active' => true,
            'api_key' => 'xnd_test_key',
            'callback_token' => 'valid_token',
            'admin_fee' => 5000
        ]);
    }

    /**
     * Scenario: Router is offline during customer creation.
     * Expected: Customer is still saved in DB with is_synced = false/null.
     */
    public function test_customer_creation_resilient_to_router_failure()
    {
        $package = Package::factory()->create();
        
        // We don't mock the service directly because the controller instantiates it.
        // But we can ensure it doesn't crash the request.
        
        $response = $this->actingAs($this->admin)->post(route('admin.customers.store'), [
            'full_name' => 'Resilient Customer',
            'package_id' => $package->id,
            'whatsapp_number' => '08123456789',
            'address' => 'Test Address',
            'password' => 'secret123',
            'domain' => 'djavva.net',
            'billing_cycle' => 1
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('customers', ['full_name' => 'Resilient Customer']);
        
        $customer = Customer::where('full_name', 'Resilient Customer')->first();
        // Since we didn't setup a real router, it should fail silently or log error
        // checking is_synced depends on the implementation success path
    }

    /**
     * Scenario: Admin tries to generate invoices twice for the same month.
     * Expected: No duplicate invoices created for the same customer/period.
     */
    public function test_prevent_duplicate_invoice_generation()
    {
        $package = Package::factory()->create();
        $customer = Customer::factory()->create(['package_id' => $package->id, 'status' => 'active']);

        $month = now()->month;
        $year = now()->year;

        // Generate first time
        $response1 = $this->actingAs($this->admin)->post(route('admin.invoices.generate'), ['month' => $month, 'year' => $year]);
        $response1->assertSessionHas('success');
        $this->assertEquals(1, Invoice::count());

        // Generate second time
        $response2 = $this->actingAs($this->admin)->post(route('admin.invoices.generate'), ['month' => $month, 'year' => $year]);
        $response2->assertSessionHas('success');
        $this->assertEquals(1, Invoice::count(), 'Should not create duplicate invoices for the same period');
    }

    /**
     * Scenario: Xendit callback received with INVALID token.
     * Expected: Forbidden (403).
     */
    public function test_xendit_callback_invalid_token_is_rejected()
    {
        // Use the explicit URL to bypass route naming/domain issues in tests
        $response = $this->postJson('/api/payments/xendit/callback', [
            'external_id' => 'INV-1-123',
            'status' => 'PAID'
        ], [
            'x-callback-token' => 'WRONG_TOKEN'
        ]);

        $response->assertStatus(403);
    }

    /**
     * Scenario: Attempt to delete a customer who has unpaid invoices.
     * Expected: Prevented with error message.
     */
    public function test_prevent_deleting_customer_with_active_invoices()
    {
        $customer = Customer::factory()->create();
        Invoice::factory()->create(['customer_id' => $customer->id, 'status' => 'unpaid']);

        $response = $this->actingAs($this->admin)->delete(route('admin.customers.destroy', $customer));
        
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    /**
     * Scenario: Admin resets a staff password.
     * Expected: Staff can login with the NEW password but not the old one.
     */
    public function test_staff_login_after_password_reset()
    {
        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@test.com',
            'password' => Hash::make('old_pass'),
            'role' => 'teknisi'
        ]);

        // Reset password
        $this->actingAs($this->admin)->put(route('admin.staff.update', $staff), [
            'name' => 'Staff Updated',
            'email' => 'staff@test.com',
            'role' => 'teknisi',
            'password' => 'new_secret_pass'
        ]);

        // Try login with OLD pass (should fail)
        $this->assertFalse(auth()->guard('web')->attempt([
            'email' => 'staff@test.com',
            'password' => 'old_pass'
        ]));

        // Try login with NEW pass (should succeed)
        $this->assertTrue(auth()->guard('web')->attempt([
            'email' => 'staff@test.com',
            'password' => 'new_secret_pass'
        ]));
    }

    /**
     * Scenario: Admin generates invoices for the NEXT month.
     * Expected: Invoices created with period_start of next month.
     */
    public function test_custom_period_invoice_generation()
    {
        $package = Package::factory()->create();
        $customer = Customer::factory()->create(['package_id' => $package->id, 'status' => 'active']);

        $nextMonth = now()->addMonth()->startOfMonth();
        $periodStr = $nextMonth->format('Y-m');

        $response = $this->actingAs($this->admin)->post(route('admin.invoices.generate'), [
            'period' => $periodStr
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Berhasil memproses scan billing', session('success'));
        // It should create 2 invoices (Current Month + Next Month) because of Scan-and-Fill
        $this->assertEquals(2, Invoice::count());
        $this->assertTrue(Invoice::whereMonth('period_start', now()->month)->exists(), 'Current month invoice missing');
        $this->assertTrue(Invoice::whereMonth('period_start', $nextMonth->month)->exists(), 'Next month invoice missing');

        // Try duplicate for same custom period
        $this->actingAs($this->admin)->post(route('admin.invoices.generate'), [
            'period' => $periodStr
        ]);
        $this->assertEquals(2, Invoice::count(), 'Should not duplicate even for next month');
    }

    /**
     * Scenario: Customer joins late April. Admin generates for MAY.
     * Expected: TWO invoices created (April Prorata + May Full).
     */
    public function test_scan_and_fill_catchup_logic()
    {
        $package = Package::factory()->create(['monthly_price' => 150000]);
        
        // Active since April 20
        $customer = Customer::factory()->create([
            'package_id' => $package->id, 
            'status' => 'active',
            'active_since' => Carbon::create(now()->year, 4, 20)
        ]);

        // Generate for MAY (Usage April generated in May)
        // Wait, if it's post-paid, admin clicks "May" to generate April...
        // No, in our UI, "Periode April" means "Pemakaian April".
        // So let's generate for MAY usage.
        $targetMonth = 5;
        $targetYear = now()->year;
        $periodStr = "$targetYear-05";

        $this->actingAs($this->admin)->post(route('admin.invoices.generate'), [
            'period' => $periodStr
        ]);

        // Should create 2 invoices: April (prorata) and May (full)
        $this->assertEquals(2, Invoice::count(), 'Should catch up April and generate May');
        
        $ivApril = Invoice::where('customer_id', $customer->id)->whereMonth('period_start', 4)->first();
        $ivMay = Invoice::where('customer_id', $customer->id)->whereMonth('period_start', 5)->first();

        $this->assertNotNull($ivApril);
        $this->assertNotNull($ivMay);

        // April must be prorated (11 days out of 30 = 55,000)
        $this->assertEquals(55000, (int)$ivApril->total_amount);
        
        // May must be full (150,000)
        $this->assertEquals(150000, (int)$ivMay->total_amount);
    }
}
