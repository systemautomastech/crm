<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('ai_agent_chat_messages'))
        {
            Schema::create('ai_agent_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->enum('role', ['user', 'assistant']);
                $table->text('content');
                $table->timestamps();

                $table->index('session_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_chat_messages');
    }
};
