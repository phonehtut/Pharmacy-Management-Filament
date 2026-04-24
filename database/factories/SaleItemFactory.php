<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stock = Stock::query()
            ->where('quantity', '>', 0)
            ->inRandomOrder()
            ->first() ?? Stock::factory()->create([
            'quantity' => fake()->numberBetween(50, 120),
        ]);

        $sale = Sale::query()
            ->where('branch_id', $stock->branch_id)
            ->inRandomOrder()
            ->first() ?? Sale::factory()->create([
            'branch_id' => $stock->branch_id,
        ]);

        return [
            'sale_id' => $sale->id,
            'medicine_id' => $stock->medicine_id,
            'quantity' => fake()->numberBetween(1, max(1, min(2, (int) $stock->quantity))),
            'price' => 0,
            'batch_no' => null,
        ];
    }
}
