<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotions')) {
            Schema::table('promotions', function (Blueprint $table) {
                $table->longText('reason')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promotions')) {
            Schema::table('promotions', function (Blueprint $table) {
                $table->string('reason')->nullable()->change();
            });
        }
    }
};
