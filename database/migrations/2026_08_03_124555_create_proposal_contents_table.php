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
        if (!Schema::hasTable('proposal_contents')) {
            $proposalTable = Schema::hasTable('sales_proposals') ? 'sales_proposals' : (Schema::hasTable('proposals') ? 'proposals' : 'sales_proposals');
            Schema::create('proposal_contents', function (Blueprint $table) use ($proposalTable) {
                $table->id();
                $table->foreignId('proposal_id')->constrained($proposalTable)->cascadeOnDelete();
                $table->string('title')->nullable();
                $table->longText('content')->nullable();
                $table->string('page_type')->nullable();
                $table->string('background_image')->nullable();
                $table->bigInteger('order')->default(1);
                $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('proposal_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_contents');
    }
};
