<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = Schema::hasTable('proposal_items') ? 'proposal_items' : (Schema::hasTable('sales_proposal_items') ? 'sales_proposal_items' : null);
        if ($table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (!Schema::hasColumn($table, 'section')) {
                    $tableBlueprint->string('section')->nullable()->default('general')->after('product_id');
                }
                if (!Schema::hasColumn($table, 'product_type')) {
                    $tableBlueprint->string('product_type')->nullable()->default('product')->after('section');
                }
            });
        }
    }

    public function down(): void
    {
        $table = Schema::hasTable('proposal_items') ? 'proposal_items' : (Schema::hasTable('sales_proposal_items') ? 'sales_proposal_items' : null);
        if ($table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (Schema::hasColumn($table, 'product_type')) {
                    $tableBlueprint->dropColumn('product_type');
                }
                if (Schema::hasColumn($table, 'section')) {
                    $tableBlueprint->dropColumn('section');
                }
            });
        }
    }
};
