<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('proposal_default_pages') && !Schema::hasColumn('proposal_default_pages', 'created_by')) {
            Schema::table('proposal_default_pages', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('creator_id')->constrained('users')->onDelete('cascade');
            });

            // Backfill existing records so created_by matches creator_id
            DB::statement('UPDATE proposal_default_pages SET created_by = creator_id WHERE created_by IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('proposal_default_pages') && Schema::hasColumn('proposal_default_pages', 'created_by')) {
            Schema::table('proposal_default_pages', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }
    }
};
