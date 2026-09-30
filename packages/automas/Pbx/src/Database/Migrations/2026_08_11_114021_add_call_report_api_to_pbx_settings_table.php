<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbx_settings', function (Blueprint $table) {
            $table->string('call_report_api_url')
                ->nullable()
                ->after('ami_password');

            $table->text('call_report_api_key')
                ->nullable()
                ->after('call_report_api_url');
        });
    }

    public function down(): void
    {
        Schema::table('pbx_settings', function (Blueprint $table) {
            $table->dropColumn([
                'call_report_api_url',
                'call_report_api_key',
            ]);
        });
    }
};