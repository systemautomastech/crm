<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facebook_chats')) {
            Schema::create('facebook_chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facebook_contact_id')->constrained('facebook_contacts')->onDelete('cascade');
                $table->boolean('is_send')->default(false);
                $table->boolean('is_seen')->default(false);
                $table->text('message')->nullable();
                $table->string('image_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_chats');
    }
};