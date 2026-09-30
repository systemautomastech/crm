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
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            if (Schema::hasColumn('sales_quotation_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->change();
            }
            if (!Schema::hasColumn('sales_quotation_items', 'section')) {
                $table->string('section')->nullable()->default('general')->after('product_id');
            }
            if (!Schema::hasColumn('sales_quotation_items', 'item_type')) {
                $table->string('item_type')->nullable()->default('product')->after('section');
            }
            if (!Schema::hasColumn('sales_quotation_items', 'description')) {
                $table->longText('description')->nullable()->after('item_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_quotation_items', function (Blueprint $table) {
            $columns = [];
            foreach (['section', 'item_type', 'description'] as $col) {
                if (Schema::hasColumn('sales_quotation_items', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
