<?php

use App\Models\Branch;
use App\Models\Medicine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Medicine::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Branch::class)->constrained()->cascadeOnDelete();
            $table->string('batch_no');
            $table->date('expiry_date');
            $table->unsignedInteger('quantity');
            $table->decimal('buy_price', 12, 2);
            $table->decimal('sell_price', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
