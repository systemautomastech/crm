<?php

namespace Automas\Hrm\Http\Controllers;

use Automas\Hrm\Http\Requests\StoreDepartmentRequest;
use Automas\Hrm\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Models\Branch;
use Automas\Hrm\Models\Department;
use Automas\Hrm\Events\CreateDepartment;
use Automas\Hrm\Events\DestroyDepartment;
use Automas\Hrm\Events\UpdateDepartment;

class DepartmentController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-departments')) {
            $departments = Department::with('branch')->select('id', 'department_name', 'branch_id', 'emp_id_prefix', 'created_at')
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-departments')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-departments')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('Hrm/SystemSetup/Departments/Index', [
                'departments' => $departments,
                'branches' => Branch::where('created_by', creatorId())->select('id', 'branch_name')->get(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreDepartmentRequest $request)
    {
        if (Auth::user()->can('create-departments')) {
            $validated = $request->validated();

            $department = new Department();
            $department->department_name = $validated['department_name'];
            $department->branch_id = $validated['branch_id'];
            $department->emp_id_prefix = $validated['employee_prefix'];

            $department->creator_id = Auth::id();
            $department->created_by = creatorId();
            $department->save();

            CreateDepartment::dispatch($request, $department);

            return redirect()->route('hrm.departments.index')->with('success', __('The department has been created successfully.'));
        } else {
            return redirect()->route('hrm.departments.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        if (Auth::user()->can('edit-departments')) {
            $validated = $request->validated();

            $department->department_name = $validated['department_name'];
            $department->branch_id = $validated['branch_id'];
            $department->emp_id_prefix = $validated['employee_prefix'];

            $department->save();

            UpdateDepartment::dispatch($request, $department);

            return back()->with('success', __('The department details are updated successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(Department $department)
    {
        if (Auth::user()->can('delete-departments')) {
            DestroyDepartment::dispatch($department);
            $department->delete();

            return back()->with('success', __('The department has been deleted.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
}
