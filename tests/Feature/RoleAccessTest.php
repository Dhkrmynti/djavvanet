<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Create Staff with NO permissions initially
        $this->staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'teknisi',
            'permissions' => []
        ]);
    }

    public function test_admin_has_full_access_bypass()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.mikrotik'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('admin.staff.index'));
        $response->assertStatus(200);
    }

    public function test_staff_without_permissions_is_blocked()
    {
        // Should be redirected to dashboard from forbidden routes
        $response = $this->actingAs($this->staff)->get(route('admin.customers'));
        $response->assertRedirect(route('admin.dashboard.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur ini.');

        // AJAX request should get 403
        $response = $this->actingAs($this->staff)->getJson(route('admin.customers.next_id'));
        $response->assertStatus(403);
    }

    public function test_staff_with_specific_permission_can_access()
    {
        $this->staff->update(['permissions' => ['customers.view']]);

        $response = $this->actingAs($this->staff)->get(route('admin.customers'));
        $response->assertStatus(200);

        // Still blocked from create
        $response = $this->actingAs($this->staff)->post(route('admin.customers.store'), [
            'full_name' => 'Test Customer',
            'email' => 'test@cust.com',
            'monthly_price' => 100000
        ]);
        $response->assertRedirect(route('admin.dashboard.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke fitur ini.');
    }

    public function test_staff_can_access_dashboard_if_granted()
    {
        $this->staff->update(['permissions' => ['dashboard.view']]);
        $response = $this->actingAs($this->staff)->get(route('admin.dashboard.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Mikrotik Real-time Status'); // Missing dashboard.mikrotik

        $this->staff->update(['permissions' => ['dashboard.view', 'dashboard.mikrotik']]);
        $response = $this->actingAs($this->staff)->get(route('admin.dashboard.index'));
        $response->assertSee('Mikrotik Real-time Status');
    }

    public function test_sensitive_settings_are_protected()
    {
        $this->staff->update(['permissions' => ['customers.view']]);
        
        $response = $this->actingAs($this->staff)->get(route('admin.settings.mikrotik'));
        $response->assertRedirect(route('admin.dashboard.index'));
        
        $response = $this->actingAs($this->staff)->get(route('admin.staff.index'));
        $response->assertRedirect(route('admin.dashboard.index'));
    }
}
