<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_business_insights')) {
            Schema::create('ai_business_insights', function (Blueprint $table) {
                $table->id();
                $table->foreignId('health_score_id')->constrained('ai_business_health_scores')->onDelete('cascade');
                $table->string('title');
                $table->text('description');
                $table->enum('severity', ['positive', 'info', 'warning', 'critical'])->default('info');
                $table->string('module')->nullable();
                $table->boolean('is_read')->default(false);
                $table->boolean('is_dismissed')->default(false);
                $table->date('analysis_date');
                $table->foreignId('created_by')->nullable()->index();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_business_insights');
    }
};
