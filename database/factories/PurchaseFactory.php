<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'branch_id' => Branch::factory(),
            'invoice_no' => fake()->unique()->bothify('INV-######'),
            'total_amount' => fake()->randomFloat(2, 10000, 500000),
            'purchased_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
