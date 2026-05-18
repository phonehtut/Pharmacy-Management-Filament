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
        Schema::table('stocks', function (Blueprint $table) {
            $table->unique(
                ['medicine_id', 'branch_id', 'batch_no', 'expiry_date'],
                'stocks_unique_inventory_batch'
            );
            $table->index(
                ['branch_id', 'medicine_id', 'expiry_date', 'quantity'],
                'stocks_branch_medicine_expiry_qty_index'
            );
            $table->index(
                ['branch_id', 'expiry_date', 'quantity'],
                'stocks_branch_expiry_quantity_index'
            );
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index(['branch_id', 'sold_at'], 'sales_branch_sold_at_index');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['sale_id', 'medicine_id', 'batch_no'], 'sale_items_sale_medicine_batch_index');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['branch_id', 'purchased_at'], 'purchases_branch_purchased_at_index');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index(
                ['purchase_id', 'medicine_id', 'batch_no', 'expiry_date'],
                'purchase_items_purchase_medicine_batch_expiry_index'
            );
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['medicine_id', 'created_at'], 'stock_movements_medicine_created_at_index');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->index(['from_branch_id', 'transferred_at'], 'stock_transfers_from_branch_transferred_at_index');
            $table->index(['to_branch_id', 'transferred_at'], 'stock_transfers_to_branch_transferred_at_index');
            $table->index(['medicine_id', 'transferred_at'], 'stock_transfers_medicine_transferred_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('stocks_unique_inventory_batch');
            $table->dropIndex('stocks_branch_medicine_expiry_qty_index');
            $table->dropIndex('stocks_branch_expiry_quantity_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_branch_sold_at_index');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('sale_items_sale_medicine_batch_index');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('purchases_branch_purchased_at_index');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropIndex('purchase_items_purchase_medicine_batch_expiry_index');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_medicine_created_at_index');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropIndex('stock_transfers_from_branch_transferred_at_index');
            $table->dropIndex('stock_transfers_to_branch_transferred_at_index');
            $table->dropIndex('stock_transfers_medicine_transferred_at_index');
        });
    }
};
