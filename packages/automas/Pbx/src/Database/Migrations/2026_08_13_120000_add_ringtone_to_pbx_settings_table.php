<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbx_settings', function (Blueprint $table) {
            $table->string('ringtone')
                ->nullable()
                ->default('ringtone.mp3')
                ->after('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('pbx_settings', function (Blueprint $table) {
            $table->dropColumn(['ringtone']);
        });
    }
};
