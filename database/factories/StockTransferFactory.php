<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransfer>
 */
class StockTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourceStock = Stock::query()
            ->where('quantity', '>=', 10)
            ->inRandomOrder()
            ->first() ?? Stock::factory()->create([
            'quantity' => fake()->numberBetween(20, 120),
        ]);

        $targetBranch = Branch::query()
            ->whereKeyNot($sourceStock->branch_id)
            ->inRandomOrder()
            ->first() ?? Branch::factory()->create();

        $transferQuantity = fake()->numberBetween(1, max(1, min(10, (int) $sourceStock->quantity)));

        return [
            'medicine_id' => $sourceStock->medicine_id,
            'stock_id' => $sourceStock->id,
            'from_branch_id' => $sourceStock->branch_id,
            'to_branch_id' => $targetBranch->id,
            'batch_no' => $sourceStock->batch_no,
            'expiry_date' => $sourceStock->expiry_date,
            'quantity' => $transferQuantity,
            'buy_price' => $sourceStock->buy_price,
            'sell_price' => $sourceStock->sell_price,
            'transferred_by' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'transferred_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
