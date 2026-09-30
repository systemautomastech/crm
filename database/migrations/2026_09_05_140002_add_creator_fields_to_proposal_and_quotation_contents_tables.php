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
        // Handle sales_proposal_contents column update and creator fields
        if (Schema::hasTable('sales_proposal_contents')) {
            if (Schema::hasColumn('sales_proposal_contents', 'proposal_content') && Schema::hasColumn('sales_proposal_contents', 'content')) {
                DB::statement("UPDATE sales_proposal_contents SET content = proposal_content WHERE content IS NULL AND proposal_content IS NOT NULL");
                Schema::table('sales_proposal_contents', function (Blueprint $table) {
                    $table->dropColumn('proposal_content');
                });
            } elseif (Schema::hasColumn('sales_proposal_contents', 'proposal_content') && !Schema::hasColumn('sales_proposal_contents', 'content')) {
                Schema::table('sales_proposal_contents', function (Blueprint $table) {
                    $table->renameColumn('proposal_content', 'content');
                });
            }

            Schema::table('sales_proposal_contents', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_proposal_contents', 'creator_id')) {
                    $table->foreignId('creator_id')->nullable()->after('order')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('sales_proposal_contents', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('creator_id')->constrained('users')->nullOnDelete();
                }
            });
        }
        if (Schema::hasTable('sales_quotation_contents')) {
            Schema::table('sales_quotation_contents', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_quotation_contents', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('creator_id')->constrained('users')->nullOnDelete();
                }
            });
        }

        // Add created_by to proposal_settings table if missing
        if (Schema::hasTable('proposal_settings')) {
            Schema::table('proposal_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('proposal_settings', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('creator_id')->constrained('users')->nullOnDelete();
                }
            });
        }

        // Add created_by to quotation_settings table if missing
        if (Schema::hasTable('quotation_settings')) {
            Schema::table('quotation_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('quotation_settings', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('creator_id')->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_proposal_contents')) {
            Schema::table('sales_proposal_contents', function (Blueprint $table) {
                if (Schema::hasColumn('sales_proposal_contents', 'creator_id')) {
                    $table->dropForeign(['creator_id']);
                    $table->dropColumn('creator_id');
                }
                if (Schema::hasColumn('sales_proposal_contents', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
            });
        }

        if (Schema::hasTable('sales_quotation_contents')) {
            Schema::table('sales_quotation_contents', function (Blueprint $table) {
                if (Schema::hasColumn('sales_quotation_contents', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
            });
        }

        if (Schema::hasTable('proposal_settings')) {
            Schema::table('proposal_settings', function (Blueprint $table) {
                if (Schema::hasColumn('proposal_settings', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
            });
        }

        if (Schema::hasTable('quotation_settings')) {
            Schema::table('quotation_settings', function (Blueprint $table) {
                if (Schema::hasColumn('quotation_settings', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
            });
        }
    }
};
