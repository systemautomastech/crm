<?php

namespace Automas\Hrm\Http\Controllers;

use App\Models\User;
use Automas\Hrm\Models\Resignation;
use Automas\Hrm\Http\Requests\StoreResignationRequest;
use Automas\Hrm\Http\Requests\UpdateResignationRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Automas\Hrm\Events\UpdateResignationStatus;
use Automas\Hrm\Events\CreateResignation;
use Automas\Hrm\Events\UpdateResignation;
use Automas\Hrm\Events\DestroyResignation;
use Automas\Hrm\Models\Employee;
use App\Models\EmailTemplate;

class ResignationController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-resignations')) {
            $resignations = Resignation::with([
                'employee:id,name',
                'approvedBy:id,name'
            ])->where(function ($q) {
                if (Auth::user()->can('manage-any-resignations')) {
                    $q->where('resignations.created_by', creatorId());
                } elseif (Auth::user()->can('manage-own-resignations')) {
                    $q->where('resignations.creator_id', Auth::id())->orWhere('resignations.employee_id', Auth::id());
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
                ->when(request('name'), function ($q) {
                    $q->whereHas('employee', function ($query) {
                        $query->where('name', 'like', '%' . request('name') . '%');
                    });
                })
                ->when(request('employee_id'), fn($q) => $q->where('resignations.employee_id', request('employee_id')))
                ->when(request('status') && request('status') !== '', fn($q) => $q->where('resignations.status', request('status')))

                ->when(request('sort'), function($q) {
                    if (request('sort') === 'employee_id') {
                        $q->join('users', 'resignations.employee_id', '=', 'users.id')
                          ->orderBy('users.name', request('direction', 'asc'))
                          ->select('resignations.*');
                    } else {
                        $q->orderBy(request('sort'), request('direction', 'asc'));
                    }
                }, fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Hrm/Resignations/Index', [
                'resignations' => $resignations,
                'employees' => $this->getFilteredEmployees(),
                'users' => User::where('created_by', creatorId())->where('is_disable', 0)->select('id', 'name')->get(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreResignationRequest $request)
    {
        if (Auth::user()->can('create-resignations')) {
            $validated = $request->validated();
            
            // Check if employee already has an approved resignation
            $existingResignation = Resignation::where('employee_id', $validated['employee_id'])
                ->where('status', 'accepted')
                ->first();
            if ($existingResignation) {
                return redirect()->route('hrm.resignations.index')
                    ->with('error', __('This employee already has an approved resignation.'));
            }
            
            $resignation = new Resignation();
            $resignation->employee_id = $validated['employee_id'];
            $resignation->last_working_date = $validated['last_working_date'];
            $resignation->reason = $validated['reason'];
            $resignation->description = $validated['description'];
            $resignation->document = basename($validated['document']);

            $resignation->creator_id = Auth::id();
            $resignation->created_by = creatorId();
            $resignation->save();

            CreateResignation::dispatch($request, $resignation);

            return redirect()->route('hrm.resignations.index')->with('success', __('The resignation has been created successfully.'));
        } else {
            return redirect()->route('hrm.resignations.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateResignationRequest $request, Resignation $resignation)
    {
        if (Auth::user()->can('edit-resignations')) {
            $validated = $request->validated();
            
            // Check if employee already has an approved resignation (excluding current one)
            $existingResignation = Resignation::where('employee_id', $validated['employee_id'])
                ->where('status', 'accepted')
                ->where('id', '!=', $resignation->id)
                ->first();
            
            if ($existingResignation) {
                return redirect()->back()
                    ->with('error', __('This employee already has an approved resignation.'));
            }

            $resignation->employee_id = $validated['employee_id'];
            $resignation->last_working_date = $validated['last_working_date'];
            $resignation->reason = $validated['reason'];
            $resignation->description = $validated['description'];
            $resignation->document = basename($validated['document']);

            $resignation->save();

            UpdateResignation::dispatch($request, $resignation);

            return redirect()->back()->with('success', __('The resignation details are updated successfully.'));
        } else {
            return redirect()->route('hrm.resignations.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(Resignation $resignation)
    {
        if (Auth::user()->can('delete-resignations')) {
            DestroyResignation::dispatch($resignation);
            
            if ($resignation->status === 'accepted' && $resignation->employee_id) {
                $user = User::find($resignation->employee_id);
                if ($user) {
                    $user->is_disable = 0;
                    $user->is_enable_login = 1;
                    $user->save();
                }
            }

            $resignation->delete();

            return redirect()->back()->with('success', __('The resignation has been deleted.'));
        } else {
            return redirect()->route('hrm.resignations.index')->with('error', __('Permission denied'));
        }
    }

    public function updateStatus(Request $request, Resignation $resignation, $status)
    {
        if (Auth::user()->can('manage-resignation-status')) {
            $oldStatus = $resignation->status;
            $resignation->status = $status;
            $resignation->approved_by = Auth::id();
            $resignation->save();

            if ($resignation->employee_id) {
                $user = User::find($resignation->employee_id);
                if ($user) {
                    if ($status === 'accepted') {
                        $user->is_disable = 1;
                        $user->is_enable_login = 0;
                        $user->save();
                    } elseif ($oldStatus === 'accepted' && $status !== 'accepted') {
                        $user->is_disable = 0;
                        $user->is_enable_login = 1;
                        $user->save();
                    }
                }
            }

            UpdateResignationStatus::dispatch($request, $resignation);
            if(company_setting('Resignations Status') == 'on') {
                $emailData = [
                    'employee_name'      => $resignation->employee->name ?? null,
                    'last_working_date'  => $resignation->last_working_date ? \Carbon\Carbon::parse($resignation->last_working_date)->format('Y-m-d') : null,
                    'reason'             => $resignation->reason ?? null,
                    'status'             => $resignation->status ?? null,
                ]; 
                $message = EmailTemplate::sendEmailTemplate('Resignations Status', [$resignation->employee->email ?? null], $emailData);
                if($message['is_success'] == false && !empty($message['error'])) {
                    return back()
                        ->with('success', __('The resignation status has been updated successfully.'))
                        ->with('error', $message['error']);
                }
            }

            return redirect()->back()->with('success', __('The resignation status has been updated successfully.'));
        } else {
            return redirect()->route('hrm.resignations.index')->with('error', __('Permission denied'));
        }
    }

    private function getFilteredEmployees()
    {
        $employeeQuery = Employee::where('created_by', creatorId());

        if (Auth::user()->can('manage-own-resignations') && !Auth::user()->can('manage-any-resignations')) {
            $employeeQuery->where(function ($q) {
                $q->where('creator_id', Auth::id())->orWhere('user_id', Auth::id());
            });
        }

        return User::emp()->where('created_by', creatorId())
            ->whereIn('id', $employeeQuery->pluck('user_id'))
            ->select('id', 'name')->get();
    }
}
