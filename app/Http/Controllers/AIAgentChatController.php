<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AIAgentChatMessage;
use App\Models\AIAgentChatSession;
use App\Services\AIAgentService;

class AIAgentChatController extends Controller
{
    public function chat(Request $request, AIAgentService $service)
    {
        if (Auth::user()->can('manage-ai-agent')) 
        {
            $request->validate([
                'message' => 'required|string|max:500',
                'session_id' => 'nullable|integer',
                'history' => 'array|max:10',
            ]);

            try {
                $result = $service->chat(
                    $request->input('message'),
                    $request->input('history', []),
                    Auth::user()
                );

                // Only save to DB if we got a successful reply
                if (isset($result['reply']) && !empty($result['reply'])) {
                    if ($sessionId = $request->input('session_id')) {
                        $session = AIAgentChatSession::where('id', $sessionId)
                            ->where('user_id', Auth::id())
                            ->where('created_by', creatorId())
                            ->first();

                        if ($session) {
                            if ($session->title === 'New Chat') {
                                $session->title = mb_substr($request->input('message'), 0, 60);
                                $session->save();
                            }

                            AIAgentChatMessage::create([
                                'session_id' => $session->id,
                                'role' => 'user',
                                'content' => $request->input('message'),
                            ]);

                            AIAgentChatMessage::create([
                                'session_id' => $session->id,
                                'role' => 'assistant',
                                'content' => $result['reply'],
                            ]);

                            $session->touch();
                        }
                    }
                }

                return response()->json(['success' => true, 'reply' => $result['reply']]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'reply' => __('Error occurred.')], 500);
            }

        }
        else
        {
            return response()->json(['success' => false, 'reply' => __('Permission denied')], 403);
        }
    }
}
