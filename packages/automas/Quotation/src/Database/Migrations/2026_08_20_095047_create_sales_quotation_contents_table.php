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
        Schema::create('sales_quotation_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('sales_quotations')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('background_image')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_quotation_contents');
    }
};
