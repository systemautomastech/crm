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
        if (Schema::hasTable('sales_quotations')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_quotations', 'subject')) {
                    $table->string('subject')->nullable()->after('quotation_number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_quotations')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                if (Schema::hasColumn('sales_quotations', 'subject')) {
                    $table->dropColumn('subject');
                }
            });
        }
    }
};
