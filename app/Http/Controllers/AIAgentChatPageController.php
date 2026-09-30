<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\AIAgentChatSession;
use App\Models\AIAgentChatMessage;

class AIAgentChatPageController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-ai-agent'))
        {
            $sessions = $this->getUserSessions();

            return Inertia::render('ai-agent/chat/index', [
                'sessions' => $sessions,
            ]);
        }
        else
        {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function getSessions()
    {
        if (Auth::user()->can('manage-ai-agent'))
        {
            return response()->json($this->getUserSessions());
        }
        else
        {
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }

    public function createSession()
    {
        if (Auth::user()->can('manage-ai-agent'))
        {
            // Check if AI Agent is configured
            $provider = company_setting('ai_agent_provider');
            $model    = company_setting('ai_agent_model');
            $apiKey   = company_setting('ai_agent_api_key');

            if (!$provider || !$model || !$apiKey) {
                return response()->json(['error' => __('AI Agent is not configured. Please configure it in settings first.')], 400);
            }

            $session = AIAgentChatSession::create([
                'user_id'    => Auth::id(),
                'creator_id' => Auth::id(),
                'created_by' => creatorId(),
                'title'      => 'New Chat',
            ]);

            return response()->json($session);
        }
        else
        {
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }

    public function destroySession(AIAgentChatSession $session)
    {
        if (Auth::user()->can('manage-ai-agent'))
        {
            if (!$this->sessionBelongsToUser($session)) {
                return back()->with('error', __('Not found'));
            }

            $session->delete();

            return back()->with('success', __('Session deleted successfully'));
        }
        else
        {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function getMessages(AIAgentChatSession $session)
    {
        if (Auth::user()->can('manage-ai-agent'))
        {
            if (!$this->sessionBelongsToUser($session)) {
                return response()->json(['error' => __('Not found')], 404);
            }

            return response()->json($session->messages);
        }
        else
        {
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }

    private function getUserSessions(): array
    {
        return AIAgentChatSession::where('user_id', Auth::id())
            ->where('creator_id', Auth::id())
            ->where('created_by', creatorId())
            ->with('lastMessage')
            ->orderByDesc('updated_at')
            ->get()
            ->toArray();
    }

    private function sessionBelongsToUser(AIAgentChatSession $session): bool
    {
        return $session->user_id === Auth::id()
            && $session->creator_id === Auth::id()
            && $session->created_by === creatorId();
    }
}
