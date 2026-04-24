<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'generic_name' => fake()->words(2, true),
            'category_id' => Category::factory(),
            'brand' => fake()->company(),
            'strength' => fake()->randomElement(['250mg', '500mg', '5mg', '10mg', '20mg']),
            'dosage_form' => fake()->randomElement(['tablet', 'capsule', 'syrup', 'injection', 'cream']),
            'barcode' => fake()->unique()->ean13(),
            'description' => fake()->sentence(),
        ];
    }
}
