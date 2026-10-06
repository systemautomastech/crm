<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : null);
        if ($table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (!Schema::hasColumn($table, 'type')) {
                    $tableBlueprint->string('type')->default('product')->after('warehouse_id');
                }
            });
        }
    }

    public function down(): void
    {
        $table = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : null);
        if ($table && Schema::hasColumn($table, 'type')) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropColumn('type');
            });
        }
    }
};
