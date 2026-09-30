<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                if (!Schema::hasColumn('plans', 'is_most_popular')) {
                    $table->boolean('is_most_popular')->default(false)->after('trial_days');
                }
                if (!Schema::hasColumn('plans', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_most_popular');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['is_most_popular', 'sort_order']);
        });
    }
};