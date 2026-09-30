<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pos_items')) {
            Schema::table('pos_items', function (Blueprint $table) {
                if (!Schema::hasColumn('pos_items', 'item_discount_value')) {
                    $table->decimal('item_discount_value', 10, 2)->default(0)->nullable()->after('total_amount');
                }
                if (!Schema::hasColumn('pos_items', 'item_discount_amount')) {
                    $table->decimal('item_discount_amount', 10, 2)->default(0)->nullable()->after('item_discount_value');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos_items')) {
            Schema::table('pos_items', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('pos_items', 'item_discount_amount')) {
                    $columnsToDrop[] = 'item_discount_amount';
                }
                if (Schema::hasColumn('pos_items', 'item_discount_value')) {
                    $columnsToDrop[] = 'item_discount_value';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
