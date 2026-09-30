<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Illuminate\Support\Facades\DB;
use Automas\Hrm\Models\Attendance;
use Automas\Hrm\Models\Branch;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\Department;
use Automas\Hrm\Models\Holiday;
use Automas\Hrm\Models\LeaveApplication;

class AttendanceSummaryGenerator
{
    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);
        $holidays = $this->getHolidays($filters);
        return [
            'data' => $data,
            'holidays' => $holidays,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $attendanceQuery = Attendance::query()
            ->join('users', 'attendances.employee_id', '=', 'users.id')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('attendances.created_by', creatorId())
            ->select(
                'attendances.date',
                'users.name as employee_name',
                'users.id as employee_user_id',
                'users.avatar as employee_avatar',
                'employees.employee_id as emp_code',
                'employees.department_id',
                DB::raw('COALESCE(departments.department_name, "-") as department_name'),
                DB::raw('COALESCE(designations.designation_name, "-") as designation_name'),
                DB::raw('TIME_FORMAT(attendances.clock_in, "%H:%i:%s") as clock_in'),
                DB::raw('TIME_FORMAT(attendances.clock_out, "%H:%i:%s") as clock_out'),
                'attendances.total_hour',
                'attendances.break_hour',
                'attendances.overtime_hours',
                'attendances.overtime_amount',
                'attendances.status',
                DB::raw("CASE
                    WHEN attendances.clock_in IS NULL THEN 'Absent'
                    ELSE 'On Time'
                END as punctuality")
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $attendanceQuery->whereBetween('attendances.date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['employee_ids'])) {
            $attendanceQuery->whereIn('attendances.employee_id', $filters['employee_ids']);
        }

        if (!empty($filters['branch_ids'])) {
            $attendanceQuery->whereIn('employees.branch_id', $filters['branch_ids']);
        }

        if (!empty($filters['department_ids'])) {
            $attendanceQuery->whereIn('employees.department_id', $filters['department_ids']);
        }

        if (!empty($filters['status'])) {
            $statuses = array_map('strtolower', $filters['status']);
            $attendanceQuery->whereIn('attendances.status', $statuses);
        }

        $attendanceRecords = $attendanceQuery->orderBy('attendances.date', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();

        $statusFilter = !empty($filters['status']) ? array_map('strtolower', $filters['status']) : [];
        $needsLeave = empty($statusFilter) || in_array('on leave', $statusFilter);
        $leaveRecords = $needsLeave ? $this->getLeaveRecords($filters, $attendanceRecords) : collect();

        $allRecords = $attendanceRecords->concat($leaveRecords)
            ->sortByDesc('date')
            ->sortBy('employee_name')
            ->values();

        if (!empty($statusFilter)) {
            $allRecords = $allRecords->filter(function ($record) use ($statusFilter) {
                return in_array(strtolower($record->status), $statusFilter);
            })->values();
        }

        return $allRecords;
    }

    public function getSummary($data)
    {
        $totalEmployees = Employee::where('created_by', creatorId())->count();
        return [
            'total_employees' => $totalEmployees,
            'total_records' => $data->count(),
            'total_present' => $data->where('status', 'present')->count(),
            'total_half_day' => $data->where('status', 'half day')->count(),
            'total_absent' => $data->where('status', 'absent')->count(),
            'total_on_leave' => $data->where('status', 'on leave')->count(),
            'total_working_hours' => round($data->sum('total_hour'), 2),
            'total_overtime_hours' => round($data->sum('overtime_hours'), 2),
            'total_overtime_amount' => round($data->sum('overtime_amount'), 2),
            'attendance_rate' => $data->count() > 0
                ? round(($data->where('status', 'present')->count() / $data->count()) * 100, 2)
                : 0,
            'late_arrivals' => $data->where('punctuality', 'Late')->count(),
            'early_departures' => $data->where('punctuality', 'Early')->count(),
        ];
    }

    public function getFilterOptions()
    {
        $branches = Branch::where('created_by', creatorId())
            ->select('id', 'branch_name')
            ->orderBy('branch_name')
            ->get()
            ->map(fn($b) => ['value' => $b->id, 'label' => $b->branch_name]);

        $employees = Employee::where('employees.created_by', creatorId())
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'employees.employee_id')
            ->orderBy('users.name')
            ->get()
            ->map(fn($emp) => ['value' => $emp->id, 'label' => $emp->name . ' (' . $emp->employee_id . ')']);

        $departments = Department::where('departments.created_by', creatorId())
            ->select('id', 'department_name', 'branch_id')
            ->orderBy('department_name')
            ->get()
            ->map(fn($dept) => ['value' => $dept->id, 'label' => $dept->department_name, 'branch_id' => $dept->branch_id]);

        return [
            'branches' => $branches,
            'employees' => $employees,
            'departments' => $departments,
            'statuses' => [
                ['value' => 'present', 'label' => 'Present'],
                ['value' => 'half day', 'label' => 'Half Day'],
                ['value' => 'absent', 'label' => 'Absent'],
                ['value' => 'on leave', 'label' => 'On Leave'],
            ],
        ];
    }

    private function getHolidays(array $filters)
    {
        $query = Holiday::where('created_by', creatorId())
            ->select('id', 'name', 'start_date', 'end_date');

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhereBetween('end_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhere(function ($q2) use ($filters) {
                        $q2->where('start_date', '<=', $filters['start_date'])
                            ->where('end_date', '>=', $filters['end_date']);
                    });
            });
        }

        return $query->orderBy('start_date')->get();
    }

    private function getLeaveRecords(array $filters, $attendanceRecords)
    {
        $leaveRecords = collect();

        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            return $leaveRecords;
        }

        $leaves = LeaveApplication::query()
            ->join('users', 'leave_applications.employee_id', '=', 'users.id')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('leave_applications.created_by', creatorId())
            ->where('leave_applications.status', 'approved')
            ->when(!empty($filters['employee_ids']), fn($q) => $q->whereIn('leave_applications.employee_id', $filters['employee_ids']))
            ->when(!empty($filters['branch_ids']), fn($q) => $q->whereIn('employees.branch_id', $filters['branch_ids']))
            ->when(!empty($filters['department_ids']), fn($q) => $q->whereIn('employees.department_id', $filters['department_ids']))
            ->where(function ($q) use ($filters) {
                $q->whereBetween('leave_applications.start_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhereBetween('leave_applications.end_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhere(function ($q2) use ($filters) {
                        $q2->where('leave_applications.start_date', '<=', $filters['start_date'])
                            ->where('leave_applications.end_date', '>=', $filters['end_date']);
                    });
            })
            ->select(
                'leave_applications.*',
                'users.name as employee_name',
                'users.id as employee_user_id',
                'users.avatar as employee_avatar',
                'employees.employee_id as emp_code',
                'employees.department_id',
                DB::raw('COALESCE(departments.department_name, "-") as department_name'),
                DB::raw('COALESCE(designations.designation_name, "-") as designation_name')
            )
            ->get();

        foreach ($leaves as $leave) {
            $startDate = \Carbon\Carbon::parse($leave->start_date);
            $endDate = \Carbon\Carbon::parse($leave->end_date);
            $filterStart = \Carbon\Carbon::parse($filters['start_date']);
            $filterEnd = \Carbon\Carbon::parse($filters['end_date']);

            $currentDate = $startDate->max($filterStart);
            $lastDate = $endDate->min($filterEnd);

            while ($currentDate->lte($lastDate)) {
                $hasAttendance = $attendanceRecords->first(function ($record) use ($leave, $currentDate) {
                    return $record->employee_user_id == $leave->employee_user_id &&
                        $record->date == $currentDate->format('Y-m-d');
                });

                if (!$hasAttendance) {
                    $leaveRecords->push((object) [
                        'date' => $currentDate->format('Y-m-d'),
                        'employee_name' => $leave->employee_name,
                        'employee_user_id' => $leave->employee_user_id,
                        'emp_code' => $leave->emp_code,
                        'employee_avatar' => $leave->employee_avatar ?? null,
                        'department_name' => $leave->department_name,
                        'designation_name' => $leave->designation_name,
                        'clock_in' => null,
                        'clock_out' => null,
                        'total_hour' => 0,
                        'break_hour' => 0,
                        'overtime_hours' => 0,
                        'overtime_amount' => 0,
                        'status' => 'on leave',
                        'punctuality' => 'On Leave',
                    ]);
                }

                $currentDate->addDay();
            }
        }

        return $leaveRecords;
    }
}
