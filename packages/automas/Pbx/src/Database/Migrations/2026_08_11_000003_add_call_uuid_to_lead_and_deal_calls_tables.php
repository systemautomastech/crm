<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_calls', function (Blueprint $table): void {
            $table
                ->string('call_uuid', 100)
                ->nullable()
                ->after('lead_id');

            $table->unique('call_uuid');
        });

        Schema::table('deal_calls', function (Blueprint $table): void {
            $table
                ->string('call_uuid', 100)
                ->nullable()
                ->after('deal_id');

            $table->unique('call_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('lead_calls', function (Blueprint $table): void {
            $table->dropUnique(['call_uuid']);
            $table->dropColumn('call_uuid');
        });

        Schema::table('deal_calls', function (Blueprint $table): void {
            $table->dropUnique(['call_uuid']);
            $table->dropColumn('call_uuid');
        });
    }
};
