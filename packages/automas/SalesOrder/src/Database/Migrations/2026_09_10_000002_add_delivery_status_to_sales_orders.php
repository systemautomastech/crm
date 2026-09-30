<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_orders', 'delivery_status')) {
                    $table->string('delivery_status')->default('pending')->after('status')->index();
                }
                if (!Schema::hasColumn('sales_orders', 'expected_delivery_date')) {
                    $table->date('expected_delivery_date')->nullable()->after('order_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (Schema::hasColumn('sales_orders', 'delivery_status')) {
                    $table->dropColumn('delivery_status');
                }
                if (Schema::hasColumn('sales_orders', 'expected_delivery_date')) {
                    $table->dropColumn('expected_delivery_date');
                }
            });
        }
    }
};
