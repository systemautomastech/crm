<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIAgentChatMessage extends Model
{
    protected $table = 'ai_agent_chat_messages';

    protected $fillable = ['session_id', 'role', 'content'];

    public function session()
    {
        return $this->belongsTo(AIAgentChatSession::class, 'session_id');
    }
}
