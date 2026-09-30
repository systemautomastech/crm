<?php

namespace Automas\Hrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\Attendance;
use Automas\Hrm\Models\LeaveApplication;
use Automas\Hrm\Models\Branch;
use Automas\Hrm\Models\Department;
use Automas\Hrm\Models\Promotion;
use Automas\Hrm\Models\Termination;
use Carbon\Carbon;
use Automas\Hrm\Models\Announcement;
use Automas\Hrm\Models\Award;
use Automas\Hrm\Models\Complaint;
use Automas\Hrm\Models\Event;
use Automas\Hrm\Models\Holiday;
use Automas\Hrm\Models\Shift;
use Automas\Hrm\Models\Warning;

class DashboardController extends Controller
{
    private function getDateRange(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $now = Carbon::now();

        switch ($period) {
            case 'prev_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $start = $now->copy()->subMonths(3)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'last_6_months':
                $start = $now->copy()->subMonths(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = Carbon::parse($request->get('start_date'))->startOfDay();
                    $end = Carbon::parse($request->get('end_date'))->endOfDay();
                } else {
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                }
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        return [$start, $end];
    }

    public function index(Request $request)
    {
        if (Auth::user()->can('manage-hrm-dashboard')) {
            $user = Auth::user();

            switch ($user->type) {
                case 'company':
                    return $this->companyDashboard($request);
                case 'hr':
                    return $this->companyDashboard($request);
                default:
                    return $this->employeeDashboard($request);
            }
        }
        return back()->with('error', __('Permission denied'));
    }


    private function companyDashboard(Request $request)
    {
        $creatorId = creatorId();
        $today = Carbon::today();
        [$start, $end] = $this->getDateRange($request);

        // Total Employees
        $totalEmployees = Employee::where('created_by', $creatorId)->count();

        // Present Today (employees with attendance today)
        $presentToday = Attendance::where('created_by', $creatorId)
            ->where('date', $today)
            ->whereNotNull('clock_in')
            ->distinct('employee_id')
            ->count();

        // Absent Today (employees with attendance status 'absent' today)
        $absentToday = Attendance::where('created_by', $creatorId)
            ->where('date', $today)
            ->where('status', 'absent')
            ->distinct('employee_id')
            ->count();

        // On Leave (approved leave applications for today)
        $onLeave = LeaveApplication::where('created_by', $creatorId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        // Yesterday's absent count for comparison
        $yesterday = Carbon::yesterday();

        $absentYesterday = Attendance::where('created_by', $creatorId)
            ->where('date', $yesterday)
            ->where('status', 'absent')
            ->distinct('employee_id')
            ->count();

        // Pending leave applications (period filtered)
        $pendingLeaves = LeaveApplication::where('created_by', $creatorId)
            ->where('status', 'pending')
            ->whereBetween('start_date', [$start, $end])
            ->count();

        // Total Branches
        $totalBranches = Branch::where('created_by', $creatorId)->count();

        // Total Departments
        $totalDepartments = Department::where('created_by', $creatorId)->count();

        // Total Promotions (period filtered)
        $totalPromotions = Promotion::where('created_by', $creatorId)
            ->whereBetween('effective_date', [$start, $end])
            ->count();

        // Terminations (period filtered with accepted status)
        $terminations = Termination::where('created_by', $creatorId)
            ->where('status', 'approved')
            ->whereBetween('termination_date', [$start, $end])
            ->count();

        // Department Distribution (employee count per department)
        $departmentDistribution = Department::where('created_by', $creatorId)
            ->with('branch')
            ->withCount(['employees' => function ($query) use ($creatorId) {
                $query->where('created_by', $creatorId);
            }])
            ->get()
            ->map(function ($dept) {
                return [
                    'name' => $dept->department_name . ' (' . ($dept->branch->branch_name ?? 'Unknown') . ')',
                    'value' => $dept->employees_count
                ];
            });


        // Employees on Leave Today
        $employeesOnLeaveToday = LeaveApplication::with('employee')->where('created_by', $creatorId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with(['employee', 'leave_type'])
            ->get()
            ->map(function ($leave) {
                return [
                    'name' => $leave->employee->name ?? 'Unknown',
                    'profile' => $leave->employee->avatar ?? '',
                    'leave_type' => $leave->leave_type->name ?? 'Unknown',
                    'days' => $leave->total_days
                ];
            });

        // Employees Without Attendance Today
        $attendedEmployeeIds = Attendance::where('created_by', $creatorId)
            ->where('date', $today)
            ->pluck('employee_id')
            ->toArray();


        $employeesWithoutAttendance = Employee::where('created_by', $creatorId)
            ->whereNotIn('user_id', $attendedEmployeeIds)
            ->with(['user', 'department'])
            ->get()
            ->map(function ($employee) {
                return [
                    'employee_id' => $employee->employee_id ?? 'Unknown',
                    'profile' =>  $employee->user->avatar ?? '',
                    'name' => $employee->user->name ?? 'Unknown',
                    'department' => $employee->department->department_name ?? 'Unknown'
                ];
            });

        // Events and Holidays for Calendar
        $events = Event::where('created_by', $creatorId)
            ->where('status', 'approved')
            ->with('eventType')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'startDate' => $event->start_date,
                    'endDate' => $event->end_date,
                    'time' => $event->start_time ?? '',
                    'description' => $event->description ?? '',
                    'type' => $event->eventType->event_type ?? 'event',
                    'color' => $event->color ?? '#3b82f6'
                ];
            });

        $holidays = Holiday::where('created_by', $creatorId)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => $holiday->id,
                    'title' => $holiday->name,
                    'startDate' => $holiday->start_date,
                    'endDate' => $holiday->end_date,
                    'time' => '',
                    'description' => $holiday->description ?? '',
                    'type' => 'holiday',
                    'color' => '#ef4444'
                ];
            });

        $calendarEvents = collect($events)->merge(collect($holidays));

        // Recent Leave Applications
        $recentLeaveApplications = LeaveApplication::where('created_by', $creatorId)
            ->with(['employee', 'leave_type'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'employee_name' => $leave->employee->name ?? 'Unknown',
                    'leave_type' => $leave->leave_type->name ?? 'Unknown',
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'total_days' => $leave->total_days,
                    'status' => $leave->status,
                    'created_at' => $leave->created_at
                ];
            });

        // Recent Announcements (active between today's date)
        $recentAnnouncements = Announcement::where('created_by', $creatorId)
            ->where('status', 'active')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->latest()
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'description' => $announcement->description ?? '',
                    'created_at' => $announcement->created_at
                ];
            });

        return Inertia::render('Hrm/Dashboard/company-dashboard', [
            'stats' => [
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'absent_today' => $absentToday,
                'absent_yesterday' => $absentYesterday,
                'on_leave' => $onLeave,
                'pending_leaves' => $pendingLeaves,
                'total_branches' => $totalBranches,
                'total_departments' => $totalDepartments,
                'total_promotions' => $totalPromotions,
                'terminations' => $terminations,
                'department_distribution' => $departmentDistribution,
                'calendar_events' => $calendarEvents,
                'recent_leave_applications' => $recentLeaveApplications,
                'recent_announcements' => $recentAnnouncements,
                'employees_on_leave_today' => $employeesOnLeaveToday,
                'employees_without_attendance' => $employeesWithoutAttendance,
            ],
            'message' => __('HRM Dashboard - Complete overview of your workforce.')
        ]);
    }

    private function employeeDashboard(Request $request)
    {
        $userId = Auth::id();
        $creatorId = creatorId();
        $today = Carbon::today();
        $currentYear = $today->year;
        $currentMonth = $today->month;

        // My Attendance (this month)
        $myAttendance = Attendance::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereNotNull('clock_in')
            ->count();

        // Total Approved Leave (this year)
        $totalApprovedLeaveYear = LeaveApplication::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        // Total Approved Leave (this month)
        $totalApprovedLeaveMonth = LeaveApplication::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->where('status', 'approved')
            ->whereMonth('start_date', $currentMonth)
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        // Pending Requests
        $pendingRequests = LeaveApplication::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->where('status', 'pending')
            ->whereMonth('start_date', $currentMonth)
            ->whereYear('start_date', $currentYear)
            ->count();

        // Total Absent Days (this month)
        $totalAbsentDays = Attendance::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->where('status', 'absent')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->count();

        // Total Awards (this month)
        $totalAwards = Award::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->whereMonth('award_date', $currentMonth)
            ->whereYear('award_date', $currentYear)
            ->count();

        // Total Warnings (this year)
        $totalWarnings = Warning::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->whereYear('warning_date', $currentYear)
            ->count();

        // Total Complaints (this year)
        $totalComplaints = Complaint::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->whereYear('complaint_date', $currentYear)
            ->count();

        // Events and Holidays for Calendar
        $events = Event::where('created_by', $creatorId)
            ->where('status', 'approved')
            ->with('eventType')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'startDate' => $event->start_date,
                    'endDate' => $event->end_date,
                    'time' => $event->start_time ?? '',
                    'description' => $event->description ?? '',
                    'type' => $event->eventType->event_type ?? 'event',
                    'color' => $event->color ?? '#3b82f6'
                ];
            });

        $holidays = Holiday::where('created_by', $creatorId)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => $holiday->id,
                    'title' => $holiday->name,
                    'startDate' => $holiday->start_date,
                    'endDate' => $holiday->end_date,
                    'time' => '',
                    'description' => $holiday->description ?? '',
                    'type' => 'holiday',
                    'color' => '#ef4444'
                ];
            });

        $calendarEvents = collect($events)->merge(collect($holidays));

        // Recent Announcements (active between today's date)
        $recentAnnouncements = Announcement::where('created_by', $creatorId)
            ->where('status', 'active')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->latest()
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'description' => $announcement->description ?? '',
                    'created_at' => $announcement->created_at
                ];
            });

        // Recent Leave Applications for Employee
        $recentLeaveApplications = LeaveApplication::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->with('leave_type')
            ->latest()
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'leave_type' => $leave->leave_type->name ?? 'Unknown',
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'total_days' => $leave->total_days,
                    'status' => $leave->status,
                    'created_at' => $leave->created_at
                ];
            });

        // Recent Awards for Employee
        $recentAwards = Award::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->with('awardType')
            ->latest()
            ->get()
            ->map(function ($award) {
                return [
                    'id' => $award->id,
                    'award_type' => $award->awardType->name ?? 'Award',
                    'award_date' => $award->award_date,
                    'created_at' => $award->created_at
                ];
            });

        // Recent Warnings for Employee
        $recentWarnings = Warning::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->with('warningType')
            ->latest()
            ->get()
            ->map(function ($warning) {
                return [
                    'id' => $warning->id,
                    'warning_type' => $warning->warningType->name ?? 'Warning',
                    'warning_date' => $warning->warning_date,
                    'created_at' => $warning->created_at
                ];
            });

        // Get employee shift information
        $employee = Employee::where('user_id', $userId)->where('created_by', $creatorId)->first();
        $shift = $employee ? Shift::find($employee->shift) : null;
        // Check for pending attendance (including night shifts)
        $pendingAttendance = Attendance::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->orderBy('clock_in', 'desc')
            ->first();
        // Today's Attendance for Clock In/Out
        $todayAttendance = Attendance::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->where('date', $today)
            ->first();

        // Check if clock in/out should be allowed
        $workingDays = getCompanyAllSetting($creatorId)['working_days'] ?? '';
        $workingDaysArray = json_decode($workingDays, true) ?? [];
        $todayDayIndex = $today->dayOfWeek;
        $isWorkingDay = in_array($todayDayIndex, $workingDaysArray);
        
        $isOnLeave = LeaveApplication::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->exists();
            
        $isHoliday = Holiday::where('created_by', $creatorId)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->exists();

        // Determine if user is currently clocked in (including night shifts)
        $isCurrentlyClockedIn = false;

        $activeAttendance = $todayAttendance;

        if ($pendingAttendance && $shift) {
            // Get shift duration in hours
            $shiftStart = Carbon::parse($shift->start_time);
            $shiftEnd = Carbon::parse($shift->end_time);
            
            // Handle night shift duration
            if ($shiftEnd->lt($shiftStart)) {
                $shiftEnd->addDay();
            }
            $shiftDurationHours = $shiftStart->diffInHours($shiftEnd);
            
            // Calculate shift end datetime from clock in time
            $clockInDateTime = Carbon::parse($pendingAttendance->clock_in);
            $shiftEndDateTime = $clockInDateTime->copy()->addHours($shiftDurationHours);
            // Check if current date is within shift duration dates
            $now = Carbon::now();
            $clockInDate = $clockInDateTime->format('Y-m-d');
            $shiftEndDate = $shiftEndDateTime->format('Y-m-d');
            $nowDate = $now->format('Y-m-d');
            // Allow clock out on clock in date or shift end date
            if ($nowDate >= $clockInDate && $nowDate <= $shiftEndDate) {
                $isCurrentlyClockedIn = true;
                $activeAttendance = $pendingAttendance;
            }
        }
        $attendanceData = [
            'is_clocked_in' => $isCurrentlyClockedIn,
            'clock_in_time' => $activeAttendance ? $activeAttendance->clock_in : null,
            'clock_out_time' => $activeAttendance ? $activeAttendance->clock_out : null,
            'total_working_hours' => $activeAttendance && $activeAttendance->total_hour ? $activeAttendance->total_hour . ' hours' : null,
            'can_clock' => $isWorkingDay && !$isOnLeave && !$isHoliday,
            'shift_start_time' => $shift ? $shift->start_time : null,
            'shift_end_time' => $shift ? $shift->end_time : null,
            'is_on_leave' => $isOnLeave,
            'is_holiday' => $isHoliday,
            'is_non_working_day' => !$isWorkingDay,
        ];

        // Recent Attendance Records
        $recentAttendance = Attendance::where('created_by', $creatorId)
            ->where('employee_id', $userId)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get()
            ->map(function ($attendance) {
                return [
                    'date' => $attendance->date,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'status' => $attendance->status,
                    'total_hour' => $attendance->total_hour,
                ];
            });

        return Inertia::render('Hrm/Dashboard/employee-dashboard', [
            'stats' => [
                'my_attendance' => $myAttendance,
                'total_approved_leave_year' => $totalApprovedLeaveYear,
                'total_approved_leave_month' => $totalApprovedLeaveMonth,
                'pending_requests' => $pendingRequests,
                'total_absent_days' => $totalAbsentDays,
                'total_awards' => $totalAwards,
                'total_warnings' => $totalWarnings,
                'total_complaints' => $totalComplaints,
                'calendar_events' => $calendarEvents,
                'recent_announcements' => $recentAnnouncements,
                'recent_leave_applications' => $recentLeaveApplications,
                'recent_awards' => $recentAwards,
                'recent_warnings' => $recentWarnings,
                'attendance_data' => $attendanceData,
                'recent_attendance' => $recentAttendance,
            ],
            'message' => __('Employee Dashboard - Your personal workspace.')
        ]);
    }
}
