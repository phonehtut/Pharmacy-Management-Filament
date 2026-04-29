<?php

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
        if (Schema::hasColumn('sales', 'returned_by') || Schema::hasColumn('sales', 'returned_at') || Schema::hasColumn('sales', 'return_reason')) {
            Schema::table('sales', function (Blueprint $table): void {
                if (Schema::hasColumn('sales', 'returned_by')) {
                    $table->dropConstrainedForeignId('returned_by');
                }

                $columns = [];

                if (Schema::hasColumn('sales', 'returned_at')) {
                    $columns[] = 'returned_at';
                }

                if (Schema::hasColumn('sales', 'return_reason')) {
                    $columns[] = 'return_reason';
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasColumn('purchases', 'returned_by') || Schema::hasColumn('purchases', 'returned_at') || Schema::hasColumn('purchases', 'return_reason')) {
            Schema::table('purchases', function (Blueprint $table): void {
                if (Schema::hasColumn('purchases', 'returned_by')) {
                    $table->dropConstrainedForeignId('returned_by');
                }

                $columns = [];

                if (Schema::hasColumn('purchases', 'returned_at')) {
                    $columns[] = 'returned_at';
                }

                if (Schema::hasColumn('purchases', 'return_reason')) {
                    $columns[] = 'return_reason';
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (! Schema::hasTable('sale_item_returns')) {
            Schema::create('sale_item_returns', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
                $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->timestamp('returned_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_item_returns')) {
            Schema::create('purchase_item_returns', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
                $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->timestamp('returned_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep return history tables when rolling back this compatibility migration.
    }
};
