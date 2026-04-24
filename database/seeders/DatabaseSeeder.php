<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PharmacySeeder::class,
            BranchSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            MedicineSeeder::class,
            PurchaseSeeder::class,
            PurchaseItemSeeder::class,
            StockSeeder::class,
            SaleSeeder::class,
            SaleItemSeeder::class,
            StockMovementSeeder::class,
        ]);
    }
}
