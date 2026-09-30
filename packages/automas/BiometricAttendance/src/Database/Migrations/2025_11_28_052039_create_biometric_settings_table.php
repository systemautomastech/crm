<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('biometric_settings')) {
            Schema::create('biometric_settings', function (Blueprint $table) {
                $table->id();
                $table->string('zkteco_api_url')->nullable();
                $table->string('username')->nullable();
                $table->string('password')->nullable();
                $table->text('auth_token')->nullable();
                $table->boolean('is_zkteco_sync')->default(false);
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_settings');
    }
};