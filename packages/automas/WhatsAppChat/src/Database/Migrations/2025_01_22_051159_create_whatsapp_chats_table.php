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
        if (!Schema::hasTable('whatsapp_chats')) {
            Schema::create('whatsapp_chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('whatsapp_contact_id')->index();
                $table->boolean('is_send')->default(1);
                $table->boolean('is_seen')->default(0);
                $table->longText('message')->nullable();
                $table->string('image_path')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();

                $table->foreign('whatsapp_contact_id')->references('id')->on('whatsapp_contacts')->onDelete('cascade');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_chats');
    }
};