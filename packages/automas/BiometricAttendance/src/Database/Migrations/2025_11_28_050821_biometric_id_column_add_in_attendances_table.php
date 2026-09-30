<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'biometric_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('biometric_id')->nullable()->after('employee_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'biometric_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('biometric_id');
            });
        }
    }
};