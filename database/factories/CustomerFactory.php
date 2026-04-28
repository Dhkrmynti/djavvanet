<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
use App\Models\Package;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_code' => 'DJV' . $this->faker->unique()->numberBetween(1000, 9999),
            'full_name' => $this->faker->name,
            'whatsapp_number' => '0812' . $this->faker->numerify('########'),
            'address' => $this->faker->address,
            'package_id' => Package::factory(),
            'username' => 'user' . $this->faker->unique()->numberBetween(100, 999),
            'password' => bcrypt('password'),
            'password_plain' => 'password',
            'status' => 'active',
            'billing_cycle' => 1,
            'active_since' => now(),
        ];
    }
}
