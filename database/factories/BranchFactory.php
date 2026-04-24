<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->city();

        return [
            'pharmacy_id' => Pharmacy::factory(),
            'name' => $name,
            'location' => fake()->address(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
        ];
    }
}
