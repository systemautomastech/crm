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
        $contentTable = Schema::hasTable('proposal_contents') ? 'proposal_contents' : (Schema::hasTable('sales_proposal_contents') ? 'sales_proposal_contents' : null);
        $proposalTable = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : 'proposals');
        if (!$contentTable) return;

        Schema::table($contentTable, function (Blueprint $table) use ($contentTable, $proposalTable) {
            if (!Schema::hasColumn($contentTable, 'proposal_id')) {
                $table->foreignId('proposal_id')->after('id')->constrained($proposalTable)->cascadeOnDelete();
            }
            if (!Schema::hasColumn($contentTable, 'title')) {
                $table->string('title')->nullable()->after('proposal_id');
            }
            if (!Schema::hasColumn($contentTable, 'content')) {
                $table->longText('content')->nullable()->after('title');
            }
            if (!Schema::hasColumn($contentTable, 'page_type')) {
                $table->string('page_type')->nullable()->after('content');
            }
            if (!Schema::hasColumn($contentTable, 'background_image')) {
                $table->string('background_image')->nullable()->after('page_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $contentTable = Schema::hasTable('proposal_contents') ? 'proposal_contents' : (Schema::hasTable('sales_proposal_contents') ? 'sales_proposal_contents' : null);
        if (!$contentTable) return;

        Schema::table($contentTable, function (Blueprint $table) use ($contentTable) {
            if (Schema::hasColumn($contentTable, 'proposal_id')) {
                $table->dropForeign(['proposal_id']);
                $table->dropColumn('proposal_id');
            }
            if (Schema::hasColumn($contentTable, 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn($contentTable, 'content')) {
                $table->dropColumn('content');
            }
            if (Schema::hasColumn($contentTable, 'page_type')) {
                $table->dropColumn('page_type');
            }
            if (Schema::hasColumn($contentTable, 'background_image')) {
                $table->dropColumn('background_image');
            }
        });
    }
};
