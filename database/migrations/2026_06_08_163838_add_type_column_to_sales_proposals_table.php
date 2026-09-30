<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_proposals', 'type')) {
                $table->enum('type', ['product', 'service'])->default('product')->after('warehouse_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_proposals', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
