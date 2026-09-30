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
        Schema::table('sales_proposal_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_proposal_contents', 'proposal_id')) {
                $table->foreignId('proposal_id')->after('id')->constrained('sales_proposals')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('sales_proposal_contents', 'title')) {
                $table->string('title')->nullable()->after('proposal_id');
            }
            if (!Schema::hasColumn('sales_proposal_contents', 'content')) {
                $table->longText('content')->nullable()->after('title');
            }
            if (!Schema::hasColumn('sales_proposal_contents', 'page_type')) {
                $table->string('page_type')->nullable()->after('content');
            }
            if (!Schema::hasColumn('sales_proposal_contents', 'background_image')) {
                $table->string('background_image')->nullable()->after('page_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_proposal_contents', function (Blueprint $table) {
            if (Schema::hasColumn('sales_proposal_contents', 'proposal_id')) {
                $table->dropForeign(['proposal_id']);
                $table->dropColumn('proposal_id');
            }
            if (Schema::hasColumn('sales_proposal_contents', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('sales_proposal_contents', 'content')) {
                $table->dropColumn('content');
            }
            if (Schema::hasColumn('sales_proposal_contents', 'page_type')) {
                $table->dropColumn('page_type');
            }
            if (Schema::hasColumn('sales_proposal_contents', 'background_image')) {
                $table->dropColumn('background_image');
            }
        });
    }
};
