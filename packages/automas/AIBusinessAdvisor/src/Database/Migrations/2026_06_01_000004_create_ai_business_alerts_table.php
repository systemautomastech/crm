<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_business_alerts')) {
            Schema::create('ai_business_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('health_score_id')->constrained('ai_business_health_scores')->onDelete('cascade');
                $table->string('title');
                $table->text('message');
                $table->enum('severity', ['warning', 'critical'])->default('warning');
                $table->string('module')->nullable();
                $table->boolean('is_resolved')->default(false);
                $table->timestamp('resolved_at')->nullable();
                $table->date('analysis_date');
                $table->foreignId('created_by')->nullable()->index();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_business_alerts');
    }
};
