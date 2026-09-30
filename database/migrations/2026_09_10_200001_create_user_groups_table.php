<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_groups')) {
            Schema::create('user_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['created_by', 'is_active']);
                $table->index('created_by');
            });
        }

        if (!Schema::hasTable('user_group_users')) {
            Schema::create('user_group_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_group_id')->constrained('user_groups')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('created_at')->nullable();

                $table->unique(['user_group_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_group_users');
        Schema::dropIfExists('user_groups');
    }
};
