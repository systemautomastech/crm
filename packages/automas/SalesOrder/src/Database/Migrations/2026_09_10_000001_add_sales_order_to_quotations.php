<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add sales_order_id to sales_quotations to track conversion
        if (Schema::hasTable('sales_quotations') && !Schema::hasColumn('sales_quotations', 'sales_order_id')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_order_id')->nullable()->index()->after('invoice_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_quotations') && Schema::hasColumn('sales_quotations', 'sales_order_id')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                $table->dropColumn('sales_order_id');
            });
        }
    }
};
