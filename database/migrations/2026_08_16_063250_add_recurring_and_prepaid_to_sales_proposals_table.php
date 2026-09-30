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
        Schema::table('sales_proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_proposals', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('type');
            }
            if (!Schema::hasColumn('sales_proposals', 'is_prepaid')) {
                $table->boolean('is_prepaid')->default(false)->after('is_recurring');
            }
            if (!Schema::hasColumn('sales_proposals', 'is_tax_enabled')) {
                $table->boolean('is_tax_enabled')->default(true)->after('is_prepaid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_proposals', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'is_prepaid', 'is_tax_enabled']);
        });
    }
};
