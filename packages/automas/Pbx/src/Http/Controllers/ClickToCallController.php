<?php

namespace Automas\Pbx\Http\Controllers;

use Automas\Pbx\Services\AmiConnectionService;
use Automas\Pbx\Services\PbxContextService;
use Automas\Pbx\Models\PbxCallLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ClickToCallController extends Controller
{
    public function __construct(
        protected PbxContextService $context,
        protected AmiConnectionService $ami
    ) {}

    public function call(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || (method_exists($user, 'isAbleTo') && !$user->can('use dialer'))) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.'),
                ], 403);
            }

            $request->validate([
                'number' => 'required|string|max:50',
                'module' => 'nullable|string|max:50',
                'record_id' => 'nullable|integer',
            ]);

            $setting = $this->context->getCreatorSetting();
            $extension = $this->context->getUserExtension();

            if (!$setting || !$setting->is_enabled || !$extension) {
                return response()->json([
                    'success' => false,
                    'message' => __('Softphone is not available.'),
                ], 404);
            }

            $raw = (string) $request->input('number');

            $normalized = preg_replace('/[^0-9+]/', '', $raw);

            if (strpos($normalized, '+') === false) {
                $normalized = preg_replace('/[^0-9]/', '', $normalized);
            }

            $success = false;

            try {
                $success = $this->ami->originate(
                    $setting,
                    $extension->extension,
                    $normalized
                );
            } catch (\Throwable $e) {
                Log::warning('PBX click-to-call originate failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => __('Click-to-call request handled.'),
                'call_initiated' => $success,
                'number' => $normalized,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('PBX click-to-call unexpected error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Click-to-call failed.'),
            ], 500);
        }
    }
}
