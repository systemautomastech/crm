<?php

namespace Automas\SalesOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\SalesOrder\Models\SalesOrderSetting;

class SalesOrderSettingController extends Controller
{
    public function show()
    {
        if (!Auth::user()->can('manage-sales-order-settings')) {
            return back()->with('error', __('Permission denied'));
        }

        $settings = SalesOrderSetting::getSettings();

        return Inertia::render('SalesOrder/SystemSetup/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        if (!Auth::user()->can('manage-sales-order-settings')) {
            return back()->with('error', __('Permission denied'));
        }

        // Accept either a nested `settings` key (sent by the frontend) or flat
        $input = $request->has('settings')
            ? $request->input('settings')
            : $request->all();

        $validated = validator($input, [
            'so_prefix'          => 'nullable|string|max:20',
            'so_starting_number' => 'nullable|integer|min:1',
            'dc_prefix'          => 'nullable|string|max:20',
            'dc_starting_number' => 'nullable|integer|min:1',
            'logo_image'         => 'nullable|string|max:500',
            'show_logo'          => 'nullable|string|in:on,off',
            'bg_letterhead'      => 'nullable|string|max:500',
            'enable_letterhead'  => 'nullable|string|in:on,off',
            'default_terms'      => 'nullable|string',
            'default_notes'      => 'nullable|string',
            'footer_note'        => 'nullable|string|max:500',
            'template_color'     => 'nullable|string|max:20',
        ])->validate();

        SalesOrderSetting::setSettings(array_filter($validated, fn($v) => $v !== null));

        return back()->with('success', __('Sales Order settings updated successfully.'));
    }
}
