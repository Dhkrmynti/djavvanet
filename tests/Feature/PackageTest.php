<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = \App\Models\User::factory()->create();
    }

    public function test_admin_can_create_package()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.packages.store'), [
                'name' => 'New Premium Package',
                'monthly_price' => 200000,
                'profile' => 'premium_profile',
                'description' => 'Test description'
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('packages', ['name' => 'New Premium Package']);
    }

    public function test_admin_can_update_package()
    {
        $package = \App\Models\Package::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.packages.update', $package), [
                'name' => 'Updated Name',
                'monthly_price' => 250000,
                'profile' => 'updated_profile',
                'description' => 'Updated desc'
            ]);

        $response->assertStatus(302);
        $this->assertEquals('Updated Name', $package->fresh()->name);
    }

    public function test_admin_can_delete_package()
    {
        $package = \App\Models\Package::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.packages.destroy', $package));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }
}
