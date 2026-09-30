<?php

namespace Automas\Hrm\Http\Controllers;

use App\Models\User;
use Automas\Hrm\Models\Award;
use Automas\Hrm\Http\Requests\StoreAwardRequest;
use Automas\Hrm\Http\Requests\UpdateAwardRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Models\AwardType;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Events\CreateAward;
use Automas\Hrm\Events\DestroyAward;
use Automas\Hrm\Events\UpdateAward;
use App\Models\EmailTemplate;

class AwardController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-awards')) {
            $awards = Award::with(['awardType:id,name', 'employee:id,name'])->where(function ($q) {
                if (Auth::user()->can('manage-any-awards')) {
                    $q->where('created_by', operator: creatorId());
                } elseif (Auth::user()->can('manage-own-awards')) {
                    $q->where('creator_id', Auth::id())->orWhere('employee_id', Auth::id());
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
                ->when(request('name'), function ($q) {
                    $q->whereHas('employee', function ($query) {
                        $query->where('name', 'like', '%' . request('name') . '%');
                    });
                })
                ->when(request('employee_id'), fn($q) => $q->where('employee_id', request('employee_id')))
                ->when(request('award_type_id'), fn($q) => $q->where('award_type_id', request('award_type_id')))

                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Hrm/Awards/Index', [
                'awards' => $awards,
                'employees' => $this->getFilteredEmployees(),
                'awardTypes' => AwardType::where('created_by', creatorId())->select('id', 'name')->get(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreAwardRequest $request)
    {
        if (Auth::user()->can('create-awards')) {
            $validated = $request->validated();
            $award = new Award();
            $award->employee_id = $validated['employee_id'];
            $award->award_type_id = $validated['award_type_id'];
            $award->award_date = $validated['award_date'];
            $award->description = $validated['description'];
            $award->certificate = basename($validated['certificate']);

            $award->creator_id = Auth::id();
            $award->created_by = creatorId();
            $award->save();

            CreateAward::dispatch($request, $award);

            // Send sales invoice mail
            if(company_setting('Create Award') == 'on') {
                $emailData = [
                    'employee_name' => $award->employee->name ?? null,
                    'award_type' => $award->awardType->name ?? null,
                    'award_date' => $validated['award_date'] ?? null,
                ];
                 
                $message = EmailTemplate::sendEmailTemplate('Create Award', [$award->employee->email], $emailData);
                if($message['is_success'] == false && !empty($message['error'])) {
                    return back()
                        ->with('success', __('The award has been created successfully.'))
                        ->with('error', $message['error']);
                }
            }

            return redirect()->route('hrm.awards.index')->with('success', __('The award has been created successfully.'));
        } else {
            return redirect()->route('hrm.awards.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateAwardRequest $request, Award $award)
    {
        if (Auth::user()->can('edit-awards')) {
            $validated = $request->validated();



            $award->employee_id = $validated['employee_id'];
            $award->award_type_id = $validated['award_type_id'];
            $award->award_date = $validated['award_date'];
            $award->description = $validated['description'];
            $award->certificate = basename($validated['certificate']);

            $award->save();

            UpdateAward::dispatch($request, $award);

            return redirect()->back()->with('success', __('The award details are updated successfully.'));
        } else {
            return redirect()->route('hrm.awards.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(Award $award)
    {
        if (Auth::user()->can('delete-awards')) {
            DestroyAward::dispatch($award);
            $award->delete();

            return redirect()->back()->with('success', __('The award has been deleted.'));
        } else {
            return redirect()->route('hrm.awards.index')->with('error', __('Permission denied'));
        }
    }

    private function getFilteredEmployees()
    {
        $employeeQuery = Employee::where('created_by', creatorId());

        if (Auth::user()->can('manage-own-awards') && !Auth::user()->can('manage-any-awards')) {
            $employeeQuery->where(function ($q) {
                $q->where('creator_id', Auth::id())->orWhere('user_id', Auth::id());
            });
        }

        return User::emp()->where('created_by', creatorId())
            ->whereIn('id', $employeeQuery->pluck('user_id'))
            ->select('id', 'name')->get();
    }
}
