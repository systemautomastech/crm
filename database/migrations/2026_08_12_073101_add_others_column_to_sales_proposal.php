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
        $table = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : null);
        if ($table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (!Schema::hasColumn($table, 'others')) {
                    $tableBlueprint->longText('others')
                        ->nullable()
                        ->after('payment_terms');
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
        if ($table && Schema::hasColumn($table, 'others')) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropColumn('others');
            });
        }
    }
};
