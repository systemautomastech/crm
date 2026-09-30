<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'emp_id_prefix')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('emp_id_prefix')->nullable()->after('branch_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'emp_id_prefix')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('emp_id_prefix');
            });
        }
    }
};
