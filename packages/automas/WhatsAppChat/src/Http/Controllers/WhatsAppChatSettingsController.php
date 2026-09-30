<?php

namespace Automas\WhatsAppChat\Http\Controllers;

use App\Http\Controllers\Controller;
use Automas\WhatsAppChat\Http\Requests\UpdateWhatsAppChatSettingsRequest;
use Illuminate\Support\Facades\Auth;

class WhatsAppChatSettingsController extends Controller
{
    public function update(UpdateWhatsAppChatSettingsRequest $request)
    {
        if (Auth::user()->can('edit-whatsapp-chat-settings')) {
            $validated = $request->validated();

            $settings = $validated['settings'];
            try {
                foreach ($settings as $key => $value) {
                    setSetting($key, $value, creatorId(),false);
                }

                return redirect()->back()->with('success', __('WhatsApp Chat settings save successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __('Failed to update WhatsApp Chat settings: ') . $e->getMessage());
            }
        }else{
            return back()->with('error', __('Permission denied'));
        }
    }
}