<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('resignations')) {
            Schema::table('resignations', function (Blueprint $table) {
                $table->date('last_working_date')->change();
                $table->longText('reason')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('resignations')) {
            Schema::table('resignations', function (Blueprint $table) {
                $table->string('last_working_date')->change();
                $table->string('reason')->nullable()->change();
            });
        }
    }
};
