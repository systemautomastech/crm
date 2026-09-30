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
        Schema::table('quotation_default_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('quotation_default_pages', 'page_type')) {
                $table->string('page_type')->default('general')->after('content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_default_pages', function (Blueprint $table) {
            if (Schema::hasColumn('quotation_default_pages', 'page_type')) {
                $table->dropColumn('page_type');
            }
        });
    }
};
