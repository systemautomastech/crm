<?php

namespace Automas\Hrm\Http\Controllers;

use Automas\Hrm\Models\Holiday;
use Automas\Hrm\Models\HolidayType;
use Automas\Hrm\Http\Requests\StoreHolidayRequest;
use Automas\Hrm\Http\Requests\UpdateHolidayRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Events\CreateHoliday;
use Automas\Hrm\Events\DestroyHoliday;
use Automas\Hrm\Events\UpdateHoliday;


class HolidayController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-holidays')){
            $holidays = Holiday::with('holidayType')

                ->where(function($q) {
                    if(Auth::user()->can('manage-any-holidays')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-holidays')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('name'), function($q) {
                    $q->where(function($query) {
                        $query->where('name', 'like', '%' . request('name') . '%')
                              ->orWhereHas('holidayType', function($typeQuery) {
                                  $typeQuery->where('holiday_type', 'like', '%' . request('name') . '%');
                              });
                    });
                })
                ->when(request('holiday_type_id') && request('holiday_type_id') !== 'all', fn($q) => $q->where('holiday_type_id', request('holiday_type_id')))
                ->when(request('start_date'), fn($q) => $q->whereDate('start_date', '>=', request('start_date')))
                ->when(request('end_date'), fn($q) => $q->whereDate('end_date', '<=', request('end_date')))
                ->when(request('is_paid') !== null && request('is_paid') !== '' && request('is_paid') !== 'all', fn($q) => $q->where('is_paid', (bool) request('is_paid')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Hrm/Holidays/Index', [
                'holidays' => $holidays,
                'holidayTypes' => HolidayType::where('created_by', creatorId())->select('id', 'holiday_type')->get(),
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreHolidayRequest $request)
    {
        if(Auth::user()->can('create-holidays')){
            $validated = $request->validated();

            $validated['is_paid'] = $request->boolean('is_paid', false);

            $holiday = new Holiday();
            $holiday->name = $validated['name'];
            $holiday->start_date = $validated['start_date'];
            $holiday->end_date = $validated['end_date'];
            $holiday->holiday_type_id = $validated['holiday_type_id'];
            $holiday->description = $validated['description'];
            $holiday->is_paid = $validated['is_paid']; 

            $holiday->creator_id = Auth::id();
            $holiday->created_by = creatorId();
            $holiday->save();

            CreateHoliday::dispatch($request, $holiday);

            return redirect()->route('hrm.holidays.index')->with('success', __('The holiday has been created successfully.'));
        }
        else{
            return redirect()->route('hrm.holidays.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    {
        if(Auth::user()->can('edit-holidays')){
            $validated = $request->validated();

            $validated['is_paid'] = $request->boolean('is_paid', false); 

            $holiday->name = $validated['name'];
            $holiday->start_date = $validated['start_date'];
            $holiday->end_date = $validated['end_date'];
            $holiday->holiday_type_id = $validated['holiday_type_id'];
            $holiday->description = $validated['description'];
            $holiday->is_paid = $validated['is_paid']; 

            $holiday->save();

            UpdateHoliday::dispatch($request, $holiday);

            return redirect()->back()->with('success', __('The holiday details are updated successfully.'));
        }
        else{
            return redirect()->route('hrm.holidays.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(Holiday $holiday)
    {
        if(Auth::user()->can('delete-holidays')){
            DestroyHoliday::dispatch($holiday);
            $holiday->delete();

            return redirect()->back()->with('success', __('The holiday has been deleted.'));
        }
        else{
            return redirect()->route('hrm.holidays.index')->with('error', __('Permission denied'));
        }
    }




}