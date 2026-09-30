<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_business_recommendations')) {
            Schema::create('ai_business_recommendations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('health_score_id')->constrained('ai_business_health_scores')->onDelete('cascade');
                $table->text('recommendation');
                $table->text('reason')->nullable();
                $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
                $table->enum('status', ['pending', 'done', 'dismissed'])->default('pending');
                $table->string('related_module')->nullable();
                $table->date('analysis_date');
                $table->timestamp('actioned_at')->nullable();
                $table->foreignId('created_by')->nullable()->index();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_business_recommendations');
    }
};