<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedesignVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $package;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->package = Package::factory()->create();
    }

    /** @test */
    public function branding_is_correctly_configured()
    {
        $this->assertEquals('PT Djavva Network Solution', config('app.name'));
    }

    /** @test */
    public function customer_portal_allows_login_with_only_id()
    {
        $customer = Customer::factory()->create([
            'customer_code' => '188175701',
            'username' => 'testuser@djavva.net'
        ]);

        // Test login with customer_code
        $response = $this->post(route('portal.authenticate'), [
            'login' => '188175701',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($customer, 'customer');

        // Logout
        auth()->guard('customer')->logout();

        // Test login with username
        $response = $this->post(route('portal.authenticate'), [
            'login' => 'testuser@djavva.net',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    /** @test */
    public function customer_registration_requires_password_on_create()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.customers.store'), [
                'customer_code' => '188175702',
                'full_name' => 'New Customer',
                'whatsapp_number' => '081234567890',
                'address' => 'Test Address',
                'package_id' => $this->package->id,
                'username' => 'newuser',
                'password' => 'secret123',
                'billing_cycle' => 1,
                'domain' => 'djavva.net',
            ]);

        $response->assertRedirect(route('admin.customers'));
        $this->assertDatabaseHas('customers', ['customer_code' => '188175702']);
    }

    /** @test */
    public function customer_update_does_not_require_password()
    {
        $customer = Customer::factory()->create(['full_name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.customers.update', $customer), [
                'customer_code' => $customer->customer_code,
                'full_name' => 'Updated Name',
                'whatsapp_number' => '08999999999',
                'address' => 'Updated Address',
                'package_id' => $this->package->id,
                'username' => 'updateduser',
                'billing_cycle' => 15,
                'status' => 'active',
                'domain' => 'djavva.net'
            ]);

        $response->assertStatus(302);
        $this->assertEquals('Updated Name', $customer->fresh()->full_name);
    }
}
