<?php

namespace Automas\AIBusinessAdvisor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SuperAdminSettingsController extends Controller
{
    public function store(Request $request)
    {
        if (Auth::user()->can('manage-ai-business-advisor-settings')) {
            $request->validate([
                'ai_advisor_enabled' => 'required|in:on,off',
                'ai_advisor_retention_days' => 'required|integer|min:1|max:3650',
            ]);

            try {
                setSetting('ai_advisor_enabled', $request->ai_advisor_enabled === 'on' ? 'on' : 'off', null, false);
                setSetting('ai_advisor_retention_days', $request->ai_advisor_retention_days, null, false);

                return redirect()->back()->with('success', __('AI Advisor settings saved successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __('Failed to update settings: ') . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }
}
