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
        Schema::create('quotation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('option');
            $table->string('value')->nullable();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['creator_id', 'option']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_settings');
    }
};
