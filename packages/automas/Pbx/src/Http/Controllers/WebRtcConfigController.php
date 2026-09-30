<?php

namespace Automas\Pbx\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Automas\Pbx\Services\WebRtcConfigService;

class WebRtcConfigController extends Controller
{
    public function index(WebRtcConfigService $service)
    {
        $config = $service->getConfigForUser();

        if (!$config) {
            return response()->json([
                'message' => 'PBX softphone is not configured for this user/workspace.',
            ], 403);
        }

        return response()->json($config);
    }
}