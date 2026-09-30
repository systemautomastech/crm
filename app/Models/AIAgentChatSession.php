<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIAgentChatSession extends Model
{
    protected $table = 'ai_agent_chat_sessions';

    protected $fillable = ['user_id', 'creator_id', 'created_by', 'title'];

    public function messages()
    {
        return $this->hasMany(AIAgentChatMessage::class, 'session_id')->orderBy('created_at');
    }

    public function lastMessage()
    {
        return $this->hasOne(AIAgentChatMessage::class, 'session_id')->latestOfMany();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($session) {
            $session->messages()->delete();
        });
    }
}
