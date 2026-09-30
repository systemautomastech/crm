<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'biometric_emp_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('biometric_emp_id')->nullable()->after('employee_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'biometric_emp_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('biometric_emp_id');
            });
        }
    }
};