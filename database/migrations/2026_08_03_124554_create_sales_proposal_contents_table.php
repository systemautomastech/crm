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
        if (!Schema::hasTable('sales_proposal_contents')) {
            Schema::create('sales_proposal_contents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('proposal_id')->constrained('sales_proposals')->cascadeOnDelete();
                $table->longText('proposal_content');
                $table->bigInteger('order')->default(1);
                $table->timestamps();

                $table->unique(['proposal_id', 'order']);
                $table->index('proposal_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_proposal_contents');
    }
};
