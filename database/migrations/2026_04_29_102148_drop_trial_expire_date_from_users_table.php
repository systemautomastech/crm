<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Drop trial_expire_date column if exists
                if (Schema::hasColumn('users', 'trial_expire_date')) {
                    $table->dropColumn('trial_expire_date');
                }
                
                // Change is_trial_done from string to integer
                if (Schema::hasColumn('users', 'is_trial_done')) {
                    $table->integer('is_trial_done')->default(0)->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Add back trial_expire_date column
                $table->date('trial_expire_date')->nullable();
                
                // Revert is_trial_done back to string
                if (Schema::hasColumn('users', 'is_trial_done')) {
                    $table->string('is_trial_done')->default('0')->change();
                }
            });
        }
    }
};