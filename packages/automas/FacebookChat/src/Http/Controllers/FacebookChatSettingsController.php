<?php

namespace Automas\FacebookChat\Http\Controllers;

use App\Http\Controllers\Controller;
use Automas\FacebookChat\Http\Requests\UpdateFacebookChatSettingsRequest;
use Illuminate\Support\Facades\Auth;

class FacebookChatSettingsController extends Controller
{
    public function update(UpdateFacebookChatSettingsRequest $request)
    {
        if (Auth::user()->can('edit-facebook-chat-settings')) {
            $validated = $request->validated();

            $settings = $validated['settings'];
            try {
                foreach ($settings as $key => $value) {
                    setSetting($key, $value, creatorId(),false);
                }

                return redirect()->back()->with('success', __('Facebook Chat settings save successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __('Failed to update Facebook Chat settings: ') . $e->getMessage());
            }
        }else{
            return back()->with('error', __('Permission denied'));
        }
    }
}