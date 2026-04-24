<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medicine_id' => Medicine::factory(),
            'branch_id' => Branch::factory(),
            'batch_no' => fake()->bothify('BATCH-#####'),
            'expiry_date' => fake()->dateTimeBetween('now', '+2 years'),
            'quantity' => fake()->numberBetween(5, 300),
            'buy_price' => fake()->randomFloat(2, 500, 50000),
            'sell_price' => fake()->randomFloat(2, 1000, 70000),
        ];
    }
}
