<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
use App\Models\Customer;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV/' . now()->format('Ymd') . '/' . $this->faker->unique()->numberBetween(100, 999),
            'customer_id' => Customer::factory(),
            'amount' => 100000,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'status' => 'unpaid',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'due_date' => now()->addMonth()->startOfMonth()->setDay(25),
        ];
    }
}
