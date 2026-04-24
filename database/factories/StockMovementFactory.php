<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $movementType = fake()->randomElement(['in', 'out', 'adjustment']);

        return [
            'medicine_id' => Medicine::factory(),
            'type' => $movementType,
            'quantity' => fake()->numberBetween(1, 200),
            'reference' => $movementType === 'adjustment' ? null : fake()->randomElement(['purchase', 'sale']),
        ];
    }
}
