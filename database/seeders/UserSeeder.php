<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::query()->first() ?? Branch::factory()->create();

        User::query()->updateOrCreate(
            ['email' => 'admin@pharmacy.test'],
            [
                'name' => 'System Admin',
                'branch_id' => $branch->id,
                'role' => 'admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'pharmacist@pharmacy.test'],
            [
                'name' => 'Branch Pharmacist',
                'branch_id' => $branch->id,
                'role' => 'Pharmacist',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'cashier@pharmacy.test'],
            [
                'name' => 'Branch Cashier',
                'branch_id' => $branch->id,
                'role' => 'cashier',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::factory()->count(8)->create();
    }
}
