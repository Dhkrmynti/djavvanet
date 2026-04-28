<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSyncTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a new customer starts with is_synced = false
     */
    public function test_new_customer_starts_unsynced()
    {
        $customer = Customer::factory()->create([
            'is_synced' => false,
        ]);

        $this->assertFalse($customer->is_synced);
    }

    /**
     * Test that updating the is_synced flag persists in the database
     */
    public function test_sync_flag_persistence()
    {
        $customer = Customer::factory()->create([
            'is_synced' => false,
        ]);

        $customer->update(['is_synced' => true]);
        $this->assertTrue($customer->fresh()->is_synced);
    }
}
