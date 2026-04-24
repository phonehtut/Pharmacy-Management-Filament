<?php

namespace Database\Seeders;

use App\Models\PurchaseItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PurchaseItem::factory()->count(80)->create();
    }
}
