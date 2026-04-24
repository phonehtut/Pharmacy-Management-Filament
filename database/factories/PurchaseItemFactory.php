<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'medicine_id' => Medicine::factory(),
            'quantity' => fake()->numberBetween(5, 200),
            'buy_price' => fake()->randomFloat(2, 500, 50000),
            'expiry_date' => fake()->dateTimeBetween('now', '+2 years'),
            'batch_no' => fake()->bothify('BATCH-#####'),
        ];
    }
}
