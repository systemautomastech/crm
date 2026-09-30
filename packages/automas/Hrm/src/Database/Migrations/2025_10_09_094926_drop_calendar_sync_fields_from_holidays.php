<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('holidays')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropColumn(['is_sync_google_calendar', 'is_sync_outlook_calendar']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('holidays')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->boolean('is_sync_google_calendar')->default(0);
                $table->boolean('is_sync_outlook_calendar')->default(0);
            });
        }
    }
};
