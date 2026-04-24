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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Branch::class)->after('email')->constrained()->cascadeOnDelete();
            $table->enum('role', ['admin', 'Pharmacist', 'cashier'])->after('branch_id')->default('cashier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_branch_foreign');
            $table->dropColumn('branch');
            $table->dropColumn('role');
        });
    }
};
