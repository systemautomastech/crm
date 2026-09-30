<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('award_types')) {
            Schema::table('award_types', function (Blueprint $table) {
                $table->longText('description')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('award_types')) {
            Schema::table('award_types', function (Blueprint $table) {
                $table->string('description')->nullable()->change();
            });
        }
    }
};
