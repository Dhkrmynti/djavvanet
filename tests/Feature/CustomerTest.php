<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_create_customer()
    {
        $package = Package::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->from('/admin/customers')
            ->post(route('admin.customers.store'), [
                'customer_code' => 'DJV' . rand(1000, 9999),
                'full_name' => 'Test Customer',
                'whatsapp_number' => '081234567890',
                'address' => 'Test Address',
                'package_id' => $package->id,
                'username' => 'testuser' . rand(100, 999),
                'password' => 'secret123',
                'billing_cycle' => 1,
                'domain' => 'djavva.net',
            ]);

        $response->assertRedirect(route('admin.customers'));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_check_customer_id_availability()
    {
        Customer::factory()->create(['customer_code' => 'DJV9999', 'username' => 'existing@djavva.net']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.customers.check_id'), [
                'code' => 'DJV9999',
                'domain' => 'djavva.net'
            ]);

        $response->assertJson(['exists' => true]);
    }

    public function test_admin_can_update_customer()
    {
        $customer = Customer::factory()->create(['full_name' => 'Old Name']);
        $newPackage = Package::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.customers.update', $customer), [
                'customer_code' => $customer->customer_code,
                'full_name' => 'New Name',
                'whatsapp_number' => '08999999999',
                'address' => 'New Address',
                'package_id' => $newPackage->id,
                'username' => 'newuser',
                'billing_cycle' => 15,
                'status' => 'active',
                'domain' => 'djavva.net'
            ]);

        $response->assertStatus(302);
        $this->assertEquals('New Name', $customer->fresh()->full_name);
    }

    public function test_admin_can_delete_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.customers.destroy', $customer));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
