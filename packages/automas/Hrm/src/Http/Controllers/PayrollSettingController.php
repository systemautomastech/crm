<?php

namespace Automas\Hrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PayrollSettingController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-payslip') || Auth::user()->can('manage-hrm') || Auth::user()->can('manage-settings')) {
            $globalSettings = getCompanyAllSetting();
            return Inertia::render('Hrm/SystemSetup/PayslipSettings/Index', [
                'settings' => [
                    'payslip_logo' => $globalSettings['payslip_logo'] ?? '',
                    'payslip_show_logo' => $globalSettings['payslip_show_logo'] ?? 'on',
                    'payslip_bg_letterhead' => $globalSettings['payslip_bg_letterhead'] ?? '',
                    'payslip_enable_letterhead' => $globalSettings['payslip_enable_letterhead'] ?? 'off',
                    'payslip_hr_signature' => $globalSettings['payslip_hr_signature'] ?? '',
                    'payslip_hr_name' => $globalSettings['payslip_hr_name'] ?? '',
                    'payslip_hr_title' => $globalSettings['payslip_hr_title'] ?? 'HR Manager / Authorized Signatory',
                    'payslip_show_signatures' => $globalSettings['payslip_show_signatures'] ?? 'on',
                    'payslip_note' => $globalSettings['payslip_note'] ?? '',
                ],
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function update(Request $request)
    {
        if (Auth::user()->can('manage-payslip') || Auth::user()->can('manage-hrm') || Auth::user()->can('manage-settings')) {
            $settings = $request->input('settings', []);
            foreach ($settings as $key => $value) {
                setSetting($key, $value);
            }
            return redirect()->back()->with('success', __('Payroll & Payslip setup updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }
}
