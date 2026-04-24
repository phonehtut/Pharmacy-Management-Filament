<?php

use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (! $this->hasForeignKey('purchases', 'purchases_supplier_id_foreign')) {
            Schema::table('purchases', function (Blueprint $table): void {
                $table->foreign('supplier_id')
                    ->references('id')
                    ->on((new Supplier)->getTable())
                    ->cascadeOnDelete();
            });
        }

        if (! $this->hasForeignKey('sales', 'sales_customer_id_foreign')) {
            Schema::table('sales', function (Blueprint $table): void {
                $table->foreign('customer_id')
                    ->references('id')
                    ->on((new Customer)->getTable())
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if ($this->hasForeignKey('sales', 'sales_customer_id_foreign')) {
            Schema::table('sales', function (Blueprint $table): void {
                $table->dropForeign('sales_customer_id_foreign');
            });
        }

        if ($this->hasForeignKey('purchases', 'purchases_supplier_id_foreign')) {
            Schema::table('purchases', function (Blueprint $table): void {
                $table->dropForeign('purchases_supplier_id_foreign');
            });
        }
    }

    private function hasForeignKey(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->exists();
    }
};
