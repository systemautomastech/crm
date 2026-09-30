<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pos') && !Schema::hasColumn('pos', 'billing_counter_id')) {
            Schema::table('pos', function (Blueprint $table) {
                $table->foreignId('billing_counter_id')->nullable()->after('warehouse_id')->index();
                $table->foreign('billing_counter_id')->references('id')->on('pos_billing_counters')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos') && Schema::hasColumn('pos', 'billing_counter_id')) {
            Schema::table('pos', function (Blueprint $table) {
                $table->dropForeign(['billing_counter_id']);
                $table->dropColumn('billing_counter_id');
            });
        }
    }
};
