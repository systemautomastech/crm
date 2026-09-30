<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_proposal_items')) {
            Schema::table('sales_proposal_items', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_proposal_items', 'section')) {
                    $table->string('section')->nullable()->default('general')->after('product_id');
                }
                if (!Schema::hasColumn('sales_proposal_items', 'product_type')) {
                    $table->string('product_type')->nullable()->default('product')->after('section');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_proposal_items')) {
            Schema::table('sales_proposal_items', function (Blueprint $table) {
                if (Schema::hasColumn('sales_proposal_items', 'product_type')) {
                    $table->dropColumn('product_type');
                }
                if (Schema::hasColumn('sales_proposal_items', 'section')) {
                    $table->dropColumn('section');
                }
            });
        }
    }
};
