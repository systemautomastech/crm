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
        $proposalItemsTable = Schema::hasTable('proposal_items') ? 'proposal_items' : (Schema::hasTable('sales_proposal_items') ? 'sales_proposal_items' : null);
        if ($proposalItemsTable && !Schema::hasColumn($proposalItemsTable, 'discount_type')) {
            Schema::table($proposalItemsTable, function (Blueprint $table) {
                $table->string('discount_type')->default('percentage')->after('unit_price');
            });
        }

        if (Schema::hasTable('sales_quotation_items') && !Schema::hasColumn('sales_quotation_items', 'discount_type')) {
            Schema::table('sales_quotation_items', function (Blueprint $table) {
                $table->string('discount_type')->default('percentage')->after('unit_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $proposalItemsTable = Schema::hasTable('proposal_items') ? 'proposal_items' : (Schema::hasTable('sales_proposal_items') ? 'sales_proposal_items' : null);
        if ($proposalItemsTable && Schema::hasColumn($proposalItemsTable, 'discount_type')) {
            Schema::table($proposalItemsTable, function (Blueprint $table) {
                $table->dropColumn('discount_type');
            });
        }

        if (Schema::hasTable('sales_quotation_items') && Schema::hasColumn('sales_quotation_items', 'discount_type')) {
            Schema::table('sales_quotation_items', function (Blueprint $table) {
                $table->dropColumn('discount_type');
            });
        }
    }
};
