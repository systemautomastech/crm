<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_payment_allocations', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_payment_allocations', 'dues')) {
                $table->decimal('dues', 20, 2)->default(0)->after('allocated_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_payment_allocations', function (Blueprint $table) {
            if (Schema::hasColumn('customer_payment_allocations', 'dues')) {
                $table->dropColumn('dues');
            }
        });
    }
};
