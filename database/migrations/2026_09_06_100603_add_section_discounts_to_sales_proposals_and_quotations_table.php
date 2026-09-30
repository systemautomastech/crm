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
        Schema::table('sales_proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_proposals', 'otc_discount_type')) {
                $table->string('otc_discount_type')->default('percentage')->after('is_tax_enabled');
            }
            if (!Schema::hasColumn('sales_proposals', 'otc_discount_value')) {
                $table->decimal('otc_discount_value', 15, 2)->default(0)->after('otc_discount_type');
            }
            if (!Schema::hasColumn('sales_proposals', 'mrc_discount_type')) {
                $table->string('mrc_discount_type')->default('percentage')->after('otc_discount_value');
            }
            if (!Schema::hasColumn('sales_proposals', 'mrc_discount_value')) {
                $table->decimal('mrc_discount_value', 15, 2)->default(0)->after('mrc_discount_type');
            }
        });

        Schema::table('sales_quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_quotations', 'otc_discount_type')) {
                $table->string('otc_discount_type')->default('percentage')->after('is_tax_enabled');
            }
            if (!Schema::hasColumn('sales_quotations', 'otc_discount_value')) {
                $table->decimal('otc_discount_value', 15, 2)->default(0)->after('otc_discount_type');
            }
            if (!Schema::hasColumn('sales_quotations', 'mrc_discount_type')) {
                $table->string('mrc_discount_type')->default('percentage')->after('otc_discount_value');
            }
            if (!Schema::hasColumn('sales_quotations', 'mrc_discount_value')) {
                $table->decimal('mrc_discount_value', 15, 2)->default(0)->after('mrc_discount_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_proposals', function (Blueprint $table) {
            $table->dropColumn(['otc_discount_type', 'otc_discount_value', 'mrc_discount_type', 'mrc_discount_value']);
        });

        Schema::table('sales_quotations', function (Blueprint $table) {
            $table->dropColumn(['otc_discount_type', 'otc_discount_value', 'mrc_discount_type', 'mrc_discount_value']);
        });
    }
};
