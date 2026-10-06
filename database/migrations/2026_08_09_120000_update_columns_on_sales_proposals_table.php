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
        $table = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : null);
        if (!$table) return;

        if (Schema::hasColumn($table, 'invoice_id')) {
            try {
                DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$table}_invoice_id_foreign");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE {$table} DROP COLUMN invoice_id");
            } catch (\Throwable $e) {}
        }

        if (Schema::hasColumn($table, 'converted_to_invoice')) {
            try {
                DB::statement("ALTER TABLE {$table} DROP COLUMN converted_to_invoice");
            } catch (\Throwable $e) {}
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
            if (Schema::hasColumn($table, 'proposal_id')) {
                $tableBlueprint->renameColumn('proposal_id', 'proposal_number');
            } elseif (!Schema::hasColumn($table, 'proposal_number')) {
                $tableBlueprint->string('proposal_number')->after('id');
            }

            if (!Schema::hasColumn($table, 'reference')) {
                $tableBlueprint->string('reference')->nullable()->after('proposal_number');
            }

            if (!Schema::hasColumn($table, 'subject')) {
                $tableBlueprint->string('subject')->nullable()->after('reference');
            }

            if (!Schema::hasColumn($table, 'converted_to_invoice')) {
                $tableBlueprint->unsignedBigInteger('converted_to_invoice')->nullable()->after('status');
            }

            if (!Schema::hasColumn($table, 'converted_to_deal')) {
                $tableBlueprint->unsignedBigInteger('converted_to_deal')->nullable()->after('converted_to_invoice');
            }

            if (!Schema::hasColumn($table, 'notes')) {
                $tableBlueprint->text('notes')->nullable()->after('payment_terms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = Schema::hasTable('proposals') ? 'proposals' : (Schema::hasTable('sales_proposals') ? 'sales_proposals' : null);
        if (!$table) return;

        Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
            if (Schema::hasColumn($table, 'reference')) {
                $tableBlueprint->dropColumn('reference');
            }
            if (Schema::hasColumn($table, 'subject')) {
                $tableBlueprint->dropColumn('subject');
            }
            if (Schema::hasColumn($table, 'converted_to_deal')) {
                $tableBlueprint->dropColumn('converted_to_deal');
            }
        });
    }
};
