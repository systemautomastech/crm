<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'assigned_group_id')) {
                $table->unsignedBigInteger('assigned_group_id')->nullable()->after('delivery_status');
                $table->foreign('assigned_group_id')->references('id')->on('user_groups')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales_orders', 'assignment_status')) {
                $table->string('assignment_status')->default('unassigned')->after('assigned_group_id');
                // Values: unassigned | group_assigned | acquired
            }
            if (!Schema::hasColumn('sales_orders', 'acquired_by')) {
                $table->unsignedBigInteger('acquired_by')->nullable()->after('assignment_status');
                $table->foreign('acquired_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('sales_orders', 'acquired_at')) {
                $table->timestamp('acquired_at')->nullable()->after('acquired_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_group_id']);
            $table->dropForeign(['acquired_by']);
            $table->dropColumn(['assigned_group_id', 'assignment_status', 'acquired_by', 'acquired_at']);
        });
    }
};
