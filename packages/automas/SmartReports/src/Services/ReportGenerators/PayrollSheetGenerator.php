<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Illuminate\Support\Facades\DB;
use Automas\Hrm\Models\Payroll;
use Automas\Hrm\Models\PayrollEntry;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\Department;

class PayrollSheetGenerator
{
    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);
        return [
            'data' => $data,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $query = PayrollEntry::query()
            ->join('users', 'payroll_entries.employee_id', '=', 'users.id')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('designations', 'employees.designation_id', '=', 'designations.id')
            ->leftJoin('payrolls', 'payroll_entries.payroll_id', '=', 'payrolls.id')
            ->where('payroll_entries.created_by', creatorId())
            ->select(
                'payroll_entries.*',
                'users.name as employee_name',
                'users.id as employee_user_id',
                'employees.employee_id',
                DB::raw('COALESCE(departments.department_name, "-") as department_name'),
                DB::raw('COALESCE(designations.designation_name, "-") as designation_name'),
                DB::raw('(payroll_entries.total_manual_overtimes + payroll_entries.attendance_overtime_amount) as total_overtime'),
                'payrolls.title as payroll_title',
                'payrolls.pay_period_start',
                'payrolls.pay_period_end',
                'payrolls.pay_date'
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('payrolls.pay_period_start', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['employee_ids'])) {
            $query->whereIn('payroll_entries.employee_id', $filters['employee_ids']);
        }

        if (!empty($filters['department_ids'])) {
            $query->whereIn('employees.department_id', $filters['department_ids']);
        }

        if (!empty($filters['payroll_id'])) {
            $query->where('payroll_entries.payroll_id', $filters['payroll_id']);
        }

        return $query->orderBy('payrolls.pay_date', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();
    }

    public function getSummary($data)
    {
        return [
            'total_records' => $data->count(),
            'total_gross_pay' => round($data->sum('gross_pay'), 2),
            'total_net_pay' => round($data->sum('net_pay'), 2),
            'total_allowances' => round($data->sum('total_allowances'), 2),
            'total_deductions' => round($data->sum('total_deductions'), 2),
            'total_loans' => round($data->sum('total_loans'), 2),
            'total_overtime' => round($data->sum('total_manual_overtimes') + $data->sum('attendance_overtime_amount'), 2),
            'average_net_pay' => $data->count() > 0 ? round($data->sum('net_pay') / $data->count(), 2) : 0,
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

        $departments = Department::where('departments.created_by', creatorId())
            ->select('id', 'department_name')
            ->orderBy('department_name')
            ->get()
            ->map(fn($dept) => ['value' => $dept->id, 'label' => $dept->department_name]);

        $payrolls = Payroll::where('created_by', creatorId())
            ->select('id', 'title', 'pay_period_start', 'pay_period_end')
            ->orderBy('pay_date', 'desc')
            ->get()
            ->map(fn($p) => [
                'value' => $p->id,
                'label' => $p->title . ' (' . $p->pay_period_start->format('M d') . ' - ' . $p->pay_period_end->format('M d, Y') . ')',
            ]);

        return [
            'employees' => $employees,
            'departments' => $departments,
            'payrolls' => $payrolls,
        ];
    }
}
