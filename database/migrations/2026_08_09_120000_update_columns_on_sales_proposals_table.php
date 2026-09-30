<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sales_proposals', 'invoice_id')) {
            try {
                DB::statement('ALTER TABLE sales_proposals DROP FOREIGN KEY sales_proposals_invoice_id_foreign');
            } catch (\Throwable $e) {}
            try {
                DB::statement('ALTER TABLE sales_proposals DROP COLUMN invoice_id');
            } catch (\Throwable $e) {}
        }

        if (Schema::hasColumn('sales_proposals', 'converted_to_invoice')) {
            try {
                DB::statement('ALTER TABLE sales_proposals DROP COLUMN converted_to_invoice');
            } catch (\Throwable $e) {}
        }

        Schema::table('sales_proposals', function (Blueprint $table) {
            if (Schema::hasColumn('sales_proposals', 'proposal_id')) {
                $table->renameColumn('proposal_id', 'proposal_number');
            } elseif (!Schema::hasColumn('sales_proposals', 'proposal_number')) {
                $table->string('proposal_number')->after('id');
            }

            if (!Schema::hasColumn('sales_proposals', 'reference')) {
                $table->string('reference')->nullable()->after('proposal_number');
            }

            if (!Schema::hasColumn('sales_proposals', 'subject')) {
                $table->string('subject')->nullable()->after('reference');
            }

            if (!Schema::hasColumn('sales_proposals', 'converted_to_invoice')) {
                $table->unsignedBigInteger('converted_to_invoice')->nullable()->after('status');
            }

            if (!Schema::hasColumn('sales_proposals', 'converted_to_deal')) {
                $table->unsignedBigInteger('converted_to_deal')->nullable()->after('converted_to_invoice');
            }

            if (!Schema::hasColumn('sales_proposals', 'notes')) {
                $table->text('notes')->nullable()->after('payment_terms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_proposals', function (Blueprint $table) {
            if (Schema::hasColumn('sales_proposals', 'reference')) {
                $table->dropColumn('reference');
            }
            if (Schema::hasColumn('sales_proposals', 'subject')) {
                $table->dropColumn('subject');
            }
            if (Schema::hasColumn('sales_proposals', 'converted_to_deal')) {
                $table->dropColumn('converted_to_deal');
            }
        });
    }
};
