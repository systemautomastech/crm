<?php

namespace App\Http\Controllers;

use App\Events\CreateMetaWebhook;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    public function handleWebhook(Request $request)
    {
        if ($request->isMethod('get')) {
            if (
                $request->query('hub.mode') === 'subscribe' &&
                $request->query('hub.verify_token') === '12345678'
            ) {
                return response($request->query('hub.challenge'), 200)
                    ->header('Content-Type', 'text/plain');
            }

            return response('Invalid verification token', 403);
        }

        $payload = $request->all();

        if (empty($payload) || !is_array($payload)) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid payload'
            ], 400);
        }

        CreateMetaWebhook::dispatch($payload);

        return response('EVENT_RECEIVED', 200);
    }
}
