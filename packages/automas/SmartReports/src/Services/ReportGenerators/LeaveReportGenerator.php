<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Illuminate\Support\Facades\DB;
use Automas\Hrm\Models\LeaveApplication;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\Department;
use Automas\Hrm\Models\Branch;

class LeaveReportGenerator
{
    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);
        return [
            'data'    => $data,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $query = LeaveApplication::query()
            ->join('users', 'leave_applications.employee_id', '=', 'users.id')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
            ->where('leave_applications.created_by', creatorId())
            ->select(
                'leave_applications.*',
                'users.name as employee_name',
                'users.id as employee_user_id',
                'users.avatar as employee_avatar',
                'employees.employee_id',
                DB::raw('COALESCE(departments.department_name, "-") as department_name'),
                DB::raw('COALESCE(leave_types.name, "-") as leave_type_name')
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('leave_applications.start_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['employee_ids'])) {
            $query->whereIn('leave_applications.employee_id', $filters['employee_ids']);
        }

        if (!empty($filters['department_ids'])) {
            $query->whereIn('employees.department_id', $filters['department_ids']);
        }

        if (!empty($filters['branch_ids'])) {
            $query->whereIn('employees.branch_id', $filters['branch_ids']);
        }

        if (!empty($filters['status'])) {
            $query->whereIn('leave_applications.status', $filters['status']);
        }

        if (!empty($filters['leave_type_ids'])) {
            $query->whereIn('leave_applications.leave_type_id', $filters['leave_type_ids']);
        }

        return $query->orderBy('leave_applications.start_date', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();
    }

    public function getSummary($data)
    {
        return [
            'total_records'  => $data->count(),
            'total_pending'  => $data->where('status', 'pending')->count(),
            'total_approved' => $data->where('status', 'approved')->count(),
            'total_rejected' => $data->where('status', 'rejected')->count(),
            'total_days'     => round($data->sum('total_days'), 2),
            'approved_days'  => round($data->where('status', 'approved')->sum('total_days'), 2),
        ];
    }

    public function getFilterOptions()
    {
        $employees = Employee::where('employees.created_by', creatorId())
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'employees.employee_id')
            ->orderBy('users.name')
            ->get()
            ->map(fn($emp) => ['value' => $emp->id, 'label' => $emp->name . ' (' . $emp->employee_id . ')']);

        $branches = Branch::where('created_by', creatorId())
            ->select('id', 'branch_name')
            ->orderBy('branch_name')
            ->get()
            ->map(fn($b) => ['value' => $b->id, 'label' => $b->branch_name]);

        $departments = Department::where('departments.created_by', creatorId())
            ->select('id', 'department_name', 'branch_id')
            ->orderBy('department_name')
            ->get()
            ->map(fn($dept) => ['value' => $dept->id, 'label' => $dept->department_name, 'branch_id' => $dept->branch_id]);

        $leaveTypes = \Automas\Hrm\Models\LeaveType::where('created_by', creatorId())
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($type) => ['value' => $type->id, 'label' => $type->name]);

        return [
            'employees'   => $employees,
            'branches'    => $branches,
            'departments' => $departments,
            'leave_types' => $leaveTypes,
            'statuses'    => [
                ['value' => 'pending',  'label' => 'Pending'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'rejected', 'label' => 'Rejected'],
            ],
        ];
    }
}
