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
        $table = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : null);
        if ($table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (!Schema::hasColumn($table, 'is_recurring')) {
                    $tableBlueprint->boolean('is_recurring')->default(false)->after('type');
                }
                if (!Schema::hasColumn($table, 'is_prepaid')) {
                    $tableBlueprint->boolean('is_prepaid')->default(false)->after('is_recurring');
                }
                if (!Schema::hasColumn($table, 'is_tax_enabled')) {
                    $tableBlueprint->boolean('is_tax_enabled')->default(true)->after('is_prepaid');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : null);
        if ($table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropColumn(['is_recurring', 'is_prepaid', 'is_tax_enabled']);
            });
        }
    }
};
