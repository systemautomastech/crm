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
                if (!Schema::hasColumn('sales_proposal_items', 'description')) {
                    $table->longText('description')->nullable()->after('product_id');
                }
                if (Schema::hasColumn('sales_proposal_items', 'product_description')) {
                    $table->dropColumn('product_description');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_proposal_items')) {
            Schema::table('sales_proposal_items', function (Blueprint $table) {
                if (Schema::hasColumn('sales_proposal_items', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};
