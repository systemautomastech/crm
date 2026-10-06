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
                if (!Schema::hasColumn($table, 'description')) {
                    $tableBlueprint->longText('description')->nullable()->after('product_id');
                }
                if (Schema::hasColumn($table, 'product_description')) {
                    $tableBlueprint->dropColumn('product_description');
                }
            });
        }
    }

    public function down(): void
    {
        $table = Schema::hasTable('proposal_items') ? 'proposal_items' : (Schema::hasTable('sales_proposal_items') ? 'sales_proposal_items' : null);
        if ($table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (Schema::hasColumn($table, 'description')) {
                    $tableBlueprint->dropColumn('description');
                }
            });
        }
    }
};
