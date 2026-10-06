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
                $tableBlueprint->unsignedBigInteger('customer_id')->nullable()->change();
                if (!Schema::hasColumn($table, 'customer_name')) {
                    $tableBlueprint->string('customer_name')->nullable()->after('customer_id');
                }
                if (!Schema::hasColumn($table, 'customer_email')) {
                    $tableBlueprint->string('customer_email')->nullable()->after('customer_name');
                }
                if (!Schema::hasColumn($table, 'customer_phone')) {
                    $tableBlueprint->string('customer_phone')->nullable()->after('customer_email');
                }
                if (!Schema::hasColumn($table, 'customer_address')) {
                    $tableBlueprint->text('customer_address')->nullable()->after('customer_phone');
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
                $tableBlueprint->dropColumn(['customer_name', 'customer_email', 'customer_phone', 'customer_address']);
            });
        }
    }
};
