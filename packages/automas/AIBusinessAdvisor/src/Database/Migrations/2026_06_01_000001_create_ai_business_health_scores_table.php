<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_business_health_scores')) {
            Schema::create('ai_business_health_scores', function (Blueprint $table) {
                $table->id();
                $table->decimal('score', 5, 2);
                $table->decimal('financial_score', 5, 2);
                $table->decimal('team_score', 5, 2);
                $table->decimal('sales_score', 5, 2);
                $table->decimal('project_score', 5, 2);
                $table->decimal('operations_score', 5, 2);
                $table->enum('trend', ['improving', 'stable', 'declining'])->default('stable');
                $table->json('raw_metrics')->nullable();
                $table->date('analysis_date');
                $table->foreignId('created_by')->nullable()->index();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_business_health_scores');
    }
};
