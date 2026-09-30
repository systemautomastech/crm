<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temporarily disable foreign key checks to safely swap values and indexes
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Swap creator_id and created_by in sales_proposals
        if (Schema::hasTable('sales_proposals') && Schema::hasColumn('sales_proposals', 'creator_id') && Schema::hasColumn('sales_proposals', 'created_by')) {
            DB::statement("
                UPDATE sales_proposals 
                JOIN sales_proposals AS sp_source ON sales_proposals.id = sp_source.id
                SET 
                    sales_proposals.created_by = sp_source.creator_id,
                    sales_proposals.creator_id = sp_source.created_by
                WHERE sales_proposals.creator_id IS NOT NULL AND sales_proposals.created_by IS NOT NULL
            ");
        }

        // 2. Drop unique constraint on creator_id, swap, and re-create unique on created_by for proposal_default_pages
        if (Schema::hasTable('proposal_default_pages') && Schema::hasColumn('proposal_default_pages', 'creator_id') && Schema::hasColumn('proposal_default_pages', 'created_by')) {
            // Drop foreign key and unique index cleanly
            Schema::table('proposal_default_pages', function (Blueprint $table) {
                $table->dropForeign(['creator_id']);
                $table->dropUnique('proposal_default_pages_creator_id_sort_order_unique');
            });

            DB::statement("
                UPDATE proposal_default_pages 
                JOIN proposal_default_pages AS pdp_source ON proposal_default_pages.id = pdp_source.id
                SET 
                    proposal_default_pages.created_by = pdp_source.creator_id,
                    proposal_default_pages.creator_id = pdp_source.created_by
                WHERE proposal_default_pages.creator_id IS NOT NULL AND proposal_default_pages.created_by IS NOT NULL
            ");

            Schema::table('proposal_default_pages', function (Blueprint $table) {
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['created_by', 'sort_order']);
            });
        }

        // 3. Swap creator_id and created_by in sales_quotations
        if (Schema::hasTable('sales_quotations') && Schema::hasColumn('sales_quotations', 'creator_id') && Schema::hasColumn('sales_quotations', 'created_by')) {
            DB::statement("
                UPDATE sales_quotations 
                JOIN sales_quotations AS sq_source ON sales_quotations.id = sq_source.id
                SET 
                    sales_quotations.created_by = sq_source.creator_id,
                    sales_quotations.creator_id = sq_source.created_by
                WHERE sales_quotations.creator_id IS NOT NULL AND sales_quotations.created_by IS NOT NULL
            ");
        }

        // 4. Drop unique constraints on creator_id, swap, and re-create on created_by for quotation_default_pages
        if (Schema::hasTable('quotation_default_pages') && Schema::hasColumn('quotation_default_pages', 'creator_id') && Schema::hasColumn('quotation_default_pages', 'created_by')) {
            Schema::table('quotation_default_pages', function (Blueprint $table) {
                $table->dropForeign(['creator_id']);
                $table->dropUnique('quotation_default_pages_creator_id_sort_order_unique');
                $table->dropUnique('quotation_default_pages_creator_id_title_unique');
            });

            DB::statement("
                UPDATE quotation_default_pages 
                JOIN quotation_default_pages AS qdp_source ON quotation_default_pages.id = qdp_source.id
                SET 
                    quotation_default_pages.created_by = qdp_source.creator_id,
                    quotation_default_pages.creator_id = qdp_source.created_by
                WHERE quotation_default_pages.creator_id IS NOT NULL AND quotation_default_pages.created_by IS NOT NULL
            ");

            Schema::table('quotation_default_pages', function (Blueprint $table) {
                $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['created_by', 'sort_order']);
                $table->unique(['created_by', 'title']);
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        if (Schema::hasTable('sales_proposals') && Schema::hasColumn('sales_proposals', 'creator_id') && Schema::hasColumn('sales_proposals', 'created_by')) {
            DB::statement("
                UPDATE sales_proposals 
                JOIN sales_proposals AS sp_source ON sales_proposals.id = sp_source.id
                SET 
                    sales_proposals.created_by = sp_source.creator_id,
                    sales_proposals.creator_id = sp_source.created_by
                WHERE sales_proposals.creator_id IS NOT NULL AND sales_proposals.created_by IS NOT NULL
            ");
        }

        if (Schema::hasTable('proposal_default_pages') && Schema::hasColumn('proposal_default_pages', 'creator_id') && Schema::hasColumn('proposal_default_pages', 'created_by')) {
            Schema::table('proposal_default_pages', function (Blueprint $table) {
                $table->dropForeign(['creator_id']);
                $table->dropUnique(['created_by', 'sort_order']);
            });

            DB::statement("
                UPDATE proposal_default_pages 
                JOIN proposal_default_pages AS pdp_source ON proposal_default_pages.id = pdp_source.id
                SET 
                    proposal_default_pages.created_by = pdp_source.creator_id,
                    proposal_default_pages.creator_id = pdp_source.created_by
                WHERE proposal_default_pages.creator_id IS NOT NULL AND proposal_default_pages.created_by IS NOT NULL
            ");

            Schema::table('proposal_default_pages', function (Blueprint $table) {
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['creator_id', 'sort_order']);
            });
        }

        if (Schema::hasTable('sales_quotations') && Schema::hasColumn('sales_quotations', 'creator_id') && Schema::hasColumn('sales_quotations', 'created_by')) {
            DB::statement("
                UPDATE sales_quotations 
                JOIN sales_quotations AS sq_source ON sales_quotations.id = sq_source.id
                SET 
                    sales_quotations.created_by = sq_source.creator_id,
                    sales_quotations.creator_id = sq_source.created_by
                WHERE sales_quotations.creator_id IS NOT NULL AND sales_quotations.created_by IS NOT NULL
            ");
        }

        if (Schema::hasTable('quotation_default_pages') && Schema::hasColumn('quotation_default_pages', 'creator_id') && Schema::hasColumn('quotation_default_pages', 'created_by')) {
            Schema::table('quotation_default_pages', function (Blueprint $table) {
                $table->dropForeign(['creator_id']);
                $table->dropUnique(['created_by', 'sort_order']);
                $table->dropUnique(['created_by', 'title']);
            });

            DB::statement("
                UPDATE quotation_default_pages 
                JOIN quotation_default_pages AS qdp_source ON quotation_default_pages.id = qdp_source.id
                SET 
                    quotation_default_pages.created_by = qdp_source.creator_id,
                    quotation_default_pages.creator_id = qdp_source.created_by
                WHERE quotation_default_pages.creator_id IS NOT NULL AND quotation_default_pages.created_by IS NOT NULL
            ");

            Schema::table('quotation_default_pages', function (Blueprint $table) {
                $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['creator_id', 'sort_order']);
                $table->unique(['creator_id', 'title']);
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
