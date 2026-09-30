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
            if (!Schema::hasColumn('sales_proposals', 'converted_to_quotation')) {
                $table->boolean('converted_to_quotation')->default(false)->after('status');
            }
            if (!Schema::hasColumn('sales_proposals', 'quotation_id')) {
                $table->unsignedBigInteger('quotation_id')->nullable()->after('converted_to_quotation')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_proposals', function (Blueprint $table) {
            if (Schema::hasColumn('sales_proposals', 'quotation_id')) {
                $table->dropColumn('quotation_id');
            }
            if (Schema::hasColumn('sales_proposals', 'converted_to_quotation')) {
                $table->dropColumn('converted_to_quotation');
            }
        });
    }
};
