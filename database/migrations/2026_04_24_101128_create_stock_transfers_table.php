<?php

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\User;
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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Medicine::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Stock::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignId('from_branch_id')->constrained((new Branch)->getTable())->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained((new Branch)->getTable())->cascadeOnDelete();
            $table->string('batch_no');
            $table->date('expiry_date');
            $table->unsignedInteger('quantity');
            $table->decimal('buy_price', 12, 2);
            $table->decimal('sell_price', 12, 2);
            $table->foreignIdFor(User::class, 'transferred_by')->nullable()->constrained((new User)->getTable())->nullOnDelete();
            $table->timestamp('transferred_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['from_branch_id', 'to_branch_id']);
            $table->index(['medicine_id', 'transferred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
