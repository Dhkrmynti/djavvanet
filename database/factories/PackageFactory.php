<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Mbps',
            'profile' => 'pppoe-' . $this->faker->numberBetween(10, 100) . 'M',
            'monthly_price' => $this->faker->randomElement([150000, 200000, 300000]),
            'installation_fee' => 0,
            'features' => ['Unlimited', 'Support 24/7'],
            'is_active' => true,
        ];
    }
}
