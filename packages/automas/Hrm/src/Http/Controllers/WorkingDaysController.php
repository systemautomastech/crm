<?php

namespace Automas\Hrm\Http\Controllers;

use Automas\Hrm\Http\Requests\UpdateWorkingDaysRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkingDaysController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-working-days')) {
            $globalSettings = getCompanyAllSetting();
            $workingDaysIndices = $globalSettings['working_days'] ?? '';
            $workingDaysArray = json_decode($workingDaysIndices, true) ?? [];

            $workingDayNames = [];
            if (!empty($workingDaysArray)) {
                // Convert indices back to day names using date function
                $workingDayNames = array_map(function ($index) {
                    return strtolower(date('l', strtotime("Sunday +{$index} days")));
                }, $workingDaysArray);
            }

            return Inertia::render('Hrm/SystemSetup/WorkingDays/Index', [
                'workingDays' => $workingDayNames,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateWorkingDaysRequest $request)
    {
        if (Auth::user()->can('edit-working-days')) {
            $validated = $request->validated();

            // Convert day names to indices using date function
            $workingDayIndices = array_map(function ($day) {
                return date('w', strtotime($day));
            }, $validated['working_days']);

            setSetting('working_days', json_encode($workingDayIndices));

            return redirect()->back()->with('success', __('The working days details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }
}
