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
        if (Schema::hasTable('sales_invoice_items') && !Schema::hasColumn('sales_invoice_items', 'discount_type')) {
            Schema::table('sales_invoice_items', function (Blueprint $table) {
                $table->string('discount_type', 20)->default('percentage')->after('unit_price');
            });
        }

        if (Schema::hasTable('sales_invoices') && !Schema::hasColumn('sales_invoices', 'discount_type')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->string('discount_type', 20)->default('percentage')->after('discount_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_invoice_items') && Schema::hasColumn('sales_invoice_items', 'discount_type')) {
            Schema::table('sales_invoice_items', function (Blueprint $table) {
                $table->dropColumn('discount_type');
            });
        }

        if (Schema::hasTable('sales_invoices') && Schema::hasColumn('sales_invoices', 'discount_type')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropColumn('discount_type');
            });
        }
    }
};
