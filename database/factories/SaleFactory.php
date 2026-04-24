<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 10000, 500000);
        $discount = fake()->randomFloat(2, 0, 5000);
        $tax = fake()->randomFloat(2, 0, 3000);
        $netAmount = $total - $discount + $tax;
        $paidAmount = $netAmount + fake()->randomFloat(2, 0, 5000);

        return [
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'customer_id' => fake()->boolean(70) ? Customer::factory() : null,
            'total' => $total,
            'discount' => $discount,
            'tax' => $tax,
            'paid_amount' => $paidAmount,
            'change' => max(0, $paidAmount - $netAmount),
            'sold_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
