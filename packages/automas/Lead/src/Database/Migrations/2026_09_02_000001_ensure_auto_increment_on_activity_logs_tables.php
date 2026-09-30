<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_activity_logs')) {
            DB::statement('ALTER TABLE lead_activity_logs MODIFY id BIGINT UNSIGNED AUTO_INCREMENT;');
        }

        if (Schema::hasTable('deal_activity_logs')) {
            DB::statement('ALTER TABLE deal_activity_logs MODIFY id BIGINT UNSIGNED AUTO_INCREMENT;');
        }
    }

    public function down(): void
    {
        // No-op
    }
};
