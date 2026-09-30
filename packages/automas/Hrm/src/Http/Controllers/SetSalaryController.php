<?php

namespace Automas\Hrm\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\AllowanceType;
use Automas\Hrm\Models\Allowance;
use Automas\Hrm\Models\DeductionType;
use Automas\Hrm\Models\Deduction;
use Automas\Hrm\Models\LoanType;
use Automas\Hrm\Models\Loan;
use Automas\Hrm\Models\Overtime;
use Illuminate\Http\Request;

class SetSalaryController extends Controller
{
    private function checkEmployeeAccess(Employee $employee)
    {
        if(Auth::user()->can('manage-any-set-salary')) {
            return $employee->created_by == creatorId();
        } elseif(Auth::user()->can('manage-own-set-salary')) {
            return $employee->user_id == Auth::id();
        }
        return false;
    }
    public function index()
    {
        if (Auth::user()->can('manage-set-salary')) {
            $employees = Employee::query()
                ->with(['user:id,name,avatar', 'branch', 'department', 'designation'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-set-salary')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-set-salary')) {
                        $q->where('user_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('search'), function ($q) {
                    $q->whereHas('user', function ($query) {
                        $query->where('name', 'like', '%' . request('search') . '%');
                    })->orWhere('employee_id', 'like', '%' . request('search') . '%');
                })
                ->when(request('employee_id'), fn($q) => $q->where('id', request('employee_id')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest('id'))
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $allEmployees = Employee::query()
                ->with(['user:id,name'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-set-salary')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-set-salary')) {
                        $q->where('user_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest('id')
                ->get();

            return Inertia::render('Hrm/SetSalary/Index', [
                'employees' => $employees,
                'allEmployees' => $allEmployees,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(Employee $employee)
    {
        if (Auth::user()->can('view-set-salary')) {
            if(!$this->checkEmployeeAccess($employee)) {
                return redirect()->route('hrm.set-salary.index')->with('error', __('Permission denied'));
            }
            $employee->load(['user:id,name,avatar', 'branch', 'department', 'designation']);

            $allowanceTypes = AllowanceType::where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            $allowances = Allowance::with('allowanceType')->where(function ($q) use ($employee) {
                if (Auth::user()->can('manage-any-set-salary')) {
                    $q->where('created_by', creatorId())->where('employee_id', $employee->user_id);
                } elseif (Auth::user()->can('manage-own-set-salary')) {
                    $q->where('employee_id', Auth::id());
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
                ->orderby('id', 'desc')
                ->get();


            $deductionTypes = DeductionType::where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            $loanTypes = LoanType::where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            $loans = Loan::with('loanType')->where(function ($q) use ($employee) {
                if (Auth::user()->can('manage-any-set-salary')) {
                    $q->where('created_by', creatorId())->where('employee_id', $employee->user_id);
                } elseif (Auth::user()->can('manage-own-set-salary')) {
                    $q->where('employee_id', Auth::id());
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
                ->orderby('id', 'desc')
                ->get();

            $overtimes = Overtime::where(function ($q) use ($employee) {
                if (Auth::user()->can('manage-any-set-salary')) {
                    $q->where('created_by', creatorId())->where('employee_id', $employee->user_id);
                } elseif (Auth::user()->can('manage-own-set-salary')) {
                    $q->where('employee_id', Auth::id());
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
                ->orderby('id', 'desc')
                ->get();
            $deductions = Deduction::with('deductionType')->where(function ($q) use ($employee) {
                if (Auth::user()->can('manage-any-set-salary')) {
                    $q->where('created_by', creatorId())->where('employee_id', $employee->user_id);
                } elseif (Auth::user()->can('manage-own-set-salary')) {
                    $q->where('employee_id', Auth::id());
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
                ->orderby('id', 'desc')
                ->get();




            return Inertia::render('Hrm/SetSalary/Show', [
                'employee' => $employee,
                'allowanceTypes' => $allowanceTypes,
                'allowances' => $allowances,
                'deductionTypes' => $deductionTypes,
                'deductions' => $deductions,
                'loanTypes' => $loanTypes,
                'loans' => $loans,
                'overtimes' => $overtimes,
                'key' => time(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function update(Employee $employee)
    {
        if (Auth::user()->can('edit-set-salary')) {
            request()->validate([
                'basic_salary' => 'required|numeric|min:0',
            ]);

            $employee->update([
                'basic_salary' => request('basic_salary'),
            ]);

            return back()->with('success', __('The basic salary has been updated successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
}
