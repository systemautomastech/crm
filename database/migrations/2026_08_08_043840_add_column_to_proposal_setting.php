<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('proposal_settings')) {
            Schema::create('proposal_settings', function (Blueprint $table) {
                $table->id();
                $table->string('proposal_prefix')->default('PRO');
                $table->unsignedInteger('proposal_starting_number')->default(1);
                $table->unsignedSmallInteger('default_validity_days')->default(30);
                $table->string('logo_image')->nullable();
                $table->string('background_image')->nullable();
                $table->string('template_color')->default('#E9591C')->nullable();
                $table->text('default_terms')->nullable();
                $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique('creator_id');
            });
        } else {
            Schema::table('proposal_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('proposal_settings', 'id')) {
                    $table->id();
                }
                if (!Schema::hasColumn('proposal_settings', 'proposal_prefix')) {
                    $table->string('proposal_prefix')->default('PRO');
                }
                if (!Schema::hasColumn('proposal_settings', 'proposal_starting_number')) {
                    $table->unsignedInteger('proposal_starting_number')->default(1);
                }
                if (!Schema::hasColumn('proposal_settings', 'default_validity_days')) {
                    $table->unsignedSmallInteger('default_validity_days')->default(30);
                }
                if (!Schema::hasColumn('proposal_settings', 'logo_image')) {
                    $table->string('logo_image')->nullable();
                }
                if (!Schema::hasColumn('proposal_settings', 'background_image')) {
                    $table->string('background_image')->nullable();
                }
                if (!Schema::hasColumn('proposal_settings', 'template_color')) {
                    $table->string('template_color')->default('#E9591C')->nullable();
                }
                if (!Schema::hasColumn('proposal_settings', 'default_terms')) {
                    $table->text('default_terms')->nullable();
                }
                if (!Schema::hasColumn('proposal_settings', 'creator_id')) {
                    $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
                    $table->unique('creator_id');
                }
                if (!Schema::hasColumn('proposal_settings', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_settings');
    }
};
