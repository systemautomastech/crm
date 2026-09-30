<?php

namespace Automas\Hrm\Http\Controllers;

use App\Models\User;
use Automas\Hrm\Models\Attendance;
use Automas\Hrm\Http\Requests\StoreAttendanceRequest;
use Automas\Hrm\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\Shift;
use Automas\Hrm\Events\CreateAttendance;
use Automas\Hrm\Events\UpdateAttendance;
use Automas\Hrm\Events\DestroyAttendance;
use Automas\Hrm\Models\LeaveApplication;
use Automas\Hrm\Models\Holiday;
use Automas\Hrm\Models\IpRestrict;

class AttendanceController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-attendances')) {
            // Default to calendar view if not specified
            $view = request('view', 'calendar');
            
            // For calendar view, get all attendances of current month
            if ($view === 'calendar') {
                $currentMonth = request('month') ? \Carbon\Carbon::parse(request('month')) : now();
                $startOfMonth = $currentMonth->copy()->startOfMonth()->toDateString();
                $endOfMonth = $currentMonth->copy()->endOfMonth()->toDateString();

                $attendances = Attendance::query()
                    ->with(['user', 'shift'])
                    ->where(function ($q) {
                        if (Auth::user()->can('manage-any-attendances')) {
                            $q->where('created_by', creatorId());
                        } elseif (Auth::user()->can('manage-own-attendances')) {
                            $q->where('creator_id', Auth::id())->orWhere('employee_id', Auth::id());
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    })
                    ->whereRaw('DATE(date) BETWEEN ? AND ?', [$startOfMonth, $endOfMonth])
                    ->orderBy('date', 'asc')
                    ->get()
                    ->map(function($attendance) {
                        $isLate = false;
                        $isEarly = false;
                        $isPending = false;
                        
                        if ($attendance->shift_id) {
                            $shift = Shift::find($attendance->shift_id);
                            if ($shift && $attendance->clock_in) {
                                $clockInTime = \Carbon\Carbon::parse($attendance->clock_in);
                                $attendanceDate = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                                $shiftStartTime = \Carbon\Carbon::parse($attendanceDate . ' ' . $shift->start_time);
                                
                                // Check if late (more than 15 minutes after shift start)
                                if ($clockInTime->diffInMinutes($shiftStartTime, false) > 15) {
                                    $isLate = true;
                                }
                            }
                            
                            if ($shift && $attendance->clock_out) {
                                $clockOutTime = \Carbon\Carbon::parse($attendance->clock_out);
                                $attendanceDate = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                                $shiftEndTime = \Carbon\Carbon::parse($attendanceDate . ' ' . $shift->end_time);
                                
                                // Handle night shifts
                                if ($shift->is_night_shift && $shift->end_time < $shift->start_time) {
                                    $shiftEndTime->addDay();
                                }
                                
                                // Check if early (more than 15 minutes before shift end)
                                if ($shiftEndTime->diffInMinutes($clockOutTime, false) > 15) {
                                    $isEarly = true;
                                }
                            }
                        }
                        
                        // Check if pending (clock in but no clock out)
                        if ($attendance->clock_in && !$attendance->clock_out) {
                            $isPending = true;
                        }
                        
                        return [
                            'id' => $attendance->id,
                            'employee_id' => $attendance->employee_id,
                            'shift_id' => $attendance->shift_id,
                            'date' => $attendance->date ? $attendance->date->format('Y-m-d') : null,
                            'clock_in' => $attendance->clock_in,
                            'clock_out' => $attendance->clock_out,
                            'total_hour' => $attendance->total_hour,
                            'break_hour' => $attendance->break_hour,
                            'overtime_hours' => $attendance->overtime_hours,
                            'overtime_amount' => $attendance->overtime_amount,
                            'status' => $attendance->status,
                            'notes' => $attendance->notes,
                            'is_late' => $isLate,
                            'is_early' => $isEarly,
                            'is_pending' => $isPending,
                            'user' => $attendance->user,
                            'shift' => $attendance->shift,
                        ];
                    });

                // Fetch leaves and holidays for the month
                $leaves = LeaveApplication::where('created_by', creatorId())
                    ->where('status', 'approved')
                    ->where(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                          ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                          ->orWhere(function($q2) use ($startOfMonth, $endOfMonth) {
                              $q2->where('start_date', '<=', $startOfMonth)
                                 ->where('end_date', '>=', $endOfMonth);
                          });
                    })
                    ->with(['employee', 'leave_type'])
                    ->get()
                    ->map(function($leave) {
                        return [
                            'id' => $leave->id,
                            'employee_id' => $leave->employee_id,
                            'start_date' => $leave->start_date->format('Y-m-d'),
                            'end_date' => $leave->end_date->format('Y-m-d'),
                            'total_days' => $leave->total_days,
                            'reason' => $leave->reason,
                            'status' => $leave->status,
                            'leave_type' => $leave->leave_type,
                        ];
                    });

                $holidays = Holiday::where('created_by', creatorId())
                    ->where(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                          ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                          ->orWhere(function($q2) use ($startOfMonth, $endOfMonth) {
                              $q2->where('start_date', '<=', $startOfMonth)
                                 ->where('end_date', '>=', $endOfMonth);
                          });
                    })
                    ->get()
                    ->map(function($holiday) {
                        return [
                            'id' => $holiday->id,
                            'name' => $holiday->name,
                            'start_date' => $holiday->start_date->format('Y-m-d'),
                            'end_date' => $holiday->end_date->format('Y-m-d'),
                            'description' => $holiday->description,
                            'is_paid' => $holiday->is_paid,
                        ];
                    });

                return Inertia::render('Hrm/Attendances/Index', [
                    'attendances' => ['data' => $attendances],
                    'leaves' => $leaves,
                    'holidays' => $holidays,
                    'employees' => $this->getFilteredEmployees(),
                    'workingDays' => json_decode(getCompanyAllSetting(creatorId())['working_days'] ?? '[]', true),
                ]);
            }

            // For list view, use pagination
            $attendances = Attendance::query()
                ->with(['user', 'shift'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-attendances')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-attendances')) {
                        $q->where('creator_id', Auth::id())->orWhere('employee_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->paginate(request('per_page', 10))
                ->withQueryString();


            return Inertia::render('Hrm/Attendances/Index', [
                'attendances' => $attendances,
                'employees' => $this->getFilteredEmployees(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }



    public function store(StoreAttendanceRequest $request)
    {
        if (Auth::user()->can('create-attendances')) {
            $validated = $request->validated();

            // Check if attendance already exists for this employee and date
            $exists = Attendance::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->where('created_by', creatorId())
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', __('Attendance record already exists for this employee and date.'));
            }

            // Validate working day, leave, and holiday
            $date = \Carbon\Carbon::parse($validated['date']);

            $workingDays = getCompanyAllSetting(creatorId())['working_days'] ?? '';
            $workingDaysArray = json_decode($workingDays, true) ?? [];
            $isWorkingDay = in_array($date->dayOfWeek, $workingDaysArray);
            
            $isOnLeave = LeaveApplication::where('created_by', creatorId())
                ->where('employee_id', $validated['employee_id'])
                ->where('status', 'approved')
                ->where('start_date', '<=', $date->toDateString())
                ->where('end_date', '>=', $date->toDateString())
                ->exists();
                
            $isHoliday = Holiday::where('created_by', creatorId())
                ->where('start_date', '<=', $date->toDateString())
                ->where('end_date', '>=', $date->toDateString())
                ->exists();

            if (!$isWorkingDay) {
                return redirect()->back()->with('error', __('Attendance cannot be created for non-working days.'));
            }
            if ($isOnLeave) {
                return redirect()->back()->with('error', __('Employee is on leave for this date.'));
            }
            if ($isHoliday) {
                return redirect()->back()->with('error', __('Attendance cannot be created on holidays.'));
            }

            $employee = Employee::with('shift')->where('user_id', $validated['employee_id'])->where('created_by', creatorId())->first();
            $shift = $employee ? $employee->shift : null;

            // Calculate attendance data first
            $calculatedData = $this->calculateAttendanceData(
                $validated['clock_in'],
                $validated['clock_out'],
                $validated['break_hour'] ?? 0,
                $shift,
                $employee
            );


            $attendance = new Attendance();
            $attendance->employee_id = $validated['employee_id'];
            $attendance->shift_id = $shift;
            $attendance->date = $validated['date'];
            $attendance->clock_in = $validated['clock_in'];
            $attendance->clock_out = $validated['clock_out'];
            $attendance->total_hour = $calculatedData['total_hour']['total_working_hours'];
            $attendance->break_hour = $calculatedData['total_hour']['total_break_hours'];
            $attendance->overtime_hours = $calculatedData['overtime_hours'];
            $attendance->overtime_amount = $calculatedData['overtime_amount'];
            $attendance->status = $calculatedData['status'];
            $attendance->notes = $validated['notes'];
            $attendance->creator_id = Auth::id();
            $attendance->created_by = creatorId();

            $attendance->save();

            CreateAttendance::dispatch($request, $attendance);

            return redirect()->route('hrm.attendances.index')->with('success', __('The attendance has been created successfully.'));
        } else {
            return redirect()->route('hrm.attendances.index')->with('error', __('Permission denied'));
        }
    }



    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        if (Auth::user()->can('edit-attendances')) {
            $validated = $request->validated();


            // Check if employee or date changed and if duplicate exists
            if ($attendance->employee_id != $validated['employee_id'] || $attendance->date != $validated['date']) {

                $exists = Attendance::where('employee_id', $validated['employee_id'])
                    ->where('date', $validated['date'])
                    ->where('id', '!=', $attendance->id)
                    ->where('created_by', creatorId())
                    ->exists();

                if ($exists) {
                    return redirect()->back()->with('error', __('Attendance record already exists for this employee and date.'));
                }
            }
            // Validate working day, leave, and holiday
            $date = \Carbon\Carbon::parse($validated['date']);

            $workingDays = getCompanyAllSetting(creatorId())['working_days'] ?? '';
            $workingDaysArray = json_decode($workingDays, true) ?? [];
            $isWorkingDay = in_array($date->dayOfWeek, $workingDaysArray);
            
            $isOnLeave = LeaveApplication::where('created_by', creatorId())
                ->where('employee_id', $validated['employee_id'])
                ->where('status', 'approved')
                ->where('start_date', '<=', $date->toDateString())
                ->where('end_date', '>=', $date->toDateString())
                ->exists();
                
            $isHoliday = Holiday::where('created_by', creatorId())
                ->where('start_date', '<=', $date->toDateString())
                ->where('end_date', '>=', $date->toDateString())
                ->exists();

            if (!$isWorkingDay) {
                return redirect()->back()->with('error', __('Attendance cannot be created for non-working days.'));
            }
            if ($isOnLeave) {
                return redirect()->back()->with('error', __('Employee is on leave for this date.'));
            }
            if ($isHoliday) {
                return redirect()->back()->with('error', __('Attendance cannot be created on holidays.'));
            }

            $employee = Employee::with('shift')->where('user_id', $validated['employee_id'])->where('created_by', creatorId())->first();
            $shift = $employee ? $employee->shift : null;

            // Calculate attendance data first
            $calculatedData = $this->calculateAttendanceData(
                $validated['clock_in'],
                $validated['clock_out'],
                $validated['break_hour'] ?? 0,
                $shift,
                $employee
            );

            $attendance->update([
                'employee_id' => $validated['employee_id'],
                'shift_id' => $shift,
                'date' => $validated['date'],
                'clock_in' => $validated['clock_in'],
                'clock_out' => $validated['clock_out'],
                'total_hour' => $calculatedData['total_hour']['total_working_hours'],
                'break_hour' => $calculatedData['total_hour']['total_break_hours'],
                'overtime_hours' => $calculatedData['overtime_hours'],
                'overtime_amount' => $calculatedData['overtime_amount'],
                'status' => $calculatedData['status'],
                'notes' => $validated['notes'],
            ]);

            UpdateAttendance::dispatch($request, $attendance);

            return redirect()->back()->with('success', __('The attendance details are updated successfully.'));
        } else {
            return redirect()->route('hrm.attendances.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(Attendance $attendance)
    {
        if (Auth::user()->can('delete-attendances')) {
            DestroyAttendance::dispatch($attendance);
            $attendance->delete();

            return redirect()->back()->with('success', __('The attendance has been deleted.'));
        } else {
            return redirect()->route('hrm.attendances.index')->with('error', __('Permission denied'));
        }
    }


    // Attedance Calucaltion Functions
    private function calculateTotalHours($clockIn, $clockOut, $shift)
    {
        if (!$clockIn || !$clockOut) {
            return 0;
        }

        $clockInTime = \Carbon\Carbon::parse($clockIn);
        $clockOutTime = \Carbon\Carbon::parse($clockOut);

        // Handle next day clock out (night shifts)
        if ($clockOutTime->lt($clockInTime)) {
            $clockOutTime->addDay();
        }

        $totalMinutes = abs($clockOutTime->diffInMinutes($clockInTime));
        $breakMinutes = 0;

        if ($shift && $shift->break_start_time && $shift->break_end_time) {
            $breakStart = \Carbon\Carbon::parse($shift->break_start_time);
            $breakEnd = \Carbon\Carbon::parse($shift->break_end_time);

            // Handle next day break times for night shifts
            if ($breakEnd->lt($breakStart)) {
                $breakEnd->addDay();
            }

            //  Only deduct break if employee worked through the break period
            if ($clockInTime->lte($breakStart) && $clockOutTime->gte($breakEnd)) {
                $breakMinutes = $this->breakDuration(shift: $shift);
            } elseif ($clockInTime->lte($breakStart) && $clockOutTime->gt($breakStart) && $clockOutTime->lte($breakEnd)) {
                // Left during break - deduct time spent on break
                $breakMinutes = abs($clockOutTime->diffInMinutes($breakStart));
            } elseif ($clockInTime->gt($breakStart) && $clockInTime->lt($breakEnd) && $clockOutTime->gte($breakEnd)) {
                // Came during break - deduct partial break (missed part of break)
                $breakMinutes = abs($breakEnd->diffInMinutes($clockInTime));
            } elseif ($clockInTime->gt($breakStart) && $clockOutTime->lt($breakEnd)) {
                // Came and left during break - no break deduction
                $breakMinutes = 0;
            }
        }

        $workingMinutes = max(0, $totalMinutes - $breakMinutes);
        $calculatedHours =   round($workingMinutes / 60, 2);
        $totalBreakHour =   round($breakMinutes / 60, 2);
        $totalHours = [
            'total_working_hours' => $calculatedHours ?? 0,
            'total_break_hours' => $totalBreakHour ?? 0,
        ];
        return $totalHours;
    }

    private function breakDuration($shift)
    {
        $breakStart = \Carbon\Carbon::parse($shift->break_start_time);
        $breakEnd = \Carbon\Carbon::parse($shift->break_end_time);
        if ($breakEnd->lt($breakStart)) {
            $breakEnd->addDay();
        }
        $breakDuration = abs($breakEnd->diffInMinutes($breakStart));

        return $breakDuration;
    }

    private function getWorkingHour($shift)
    {
        $start = \Carbon\Carbon::parse($shift->start_time);
        $end = \Carbon\Carbon::parse($shift->end_time);

        // Handle night shifts
        if ($shift->is_night_shift && $end->lt($start)) {
            $end->addDay();
        }
        $breakDuration = $this->breakDuration($shift);

        $totalMinutes = abs($end->diffInMinutes($start)) - $breakDuration;
        return round(max(0, $totalMinutes) / 60, 2);
    }

    private function calculateAttendanceData($clockIn, $clockOut, $breakHour, $shift, $employee)
    {
        $shift = Shift::where('id', $shift)->where('created_by', creatorId())->first();
        // Step 1: Calculate total working hours
        $totalHourData = $this->calculateTotalHours($clockIn, $clockOut, $shift);
        $totalHour = $totalHourData['total_working_hours'];


        // Step 2: Calculate overtime
        $standardHours = ($shift && $this->getWorkingHour($shift) > 0) ? $this->getWorkingHour($shift) : 8;
        $overtimeHours = max(0, round($totalHour - $standardHours, 2));

        // Step 3: Calculate overtime amount
        $overtimeAmount = 0;
        if ($overtimeHours > 0 && $employee && $employee->rate_per_hour) {
            $overtimeAmount = round($overtimeHours * ($employee->rate_per_hour), 2);
        }

        // Step 4: Determine status
        $status = 'absent';
        if ($totalHour > 0) {
            $halfDayThreshold = $standardHours / 2;
            if ($totalHour >= $standardHours) {
                $status = 'present';
            } elseif ($totalHour >= $halfDayThreshold) {
                $status = 'half day';
            } else {
                $status = 'absent';
            }
        }

        return [
            'total_hour' => $totalHourData,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'status' => $status,
        ];
    }


    public function clockIn()
    {
        if (Auth::user()->can('clock-in')) {
            $employeeId = Auth::id();
            
            // Check if user exists in employee table
            $employee = Employee::where('user_id', $employeeId)->where('created_by', creatorId())->first();
            if (!$employee) {
                return redirect()->back()->with('error', __('Please convert staff to employee first.'));
            }
            
            // Check IP restriction
            $setting = getCompanyAllSetting();
            if (isset($setting['ip_restrict']) && $setting['ip_restrict'] === 'on') {
                $userIp = request()->ip();
                $allowedIp = IpRestrict::where('ip', $userIp)
                    ->where('created_by', creatorId())
                    ->exists();
                
                if (!$allowedIp) {
                    return redirect()->back()->with('error', __('This IP is not allowed to clock in & clock out.'));
                }
            }

            $today = now()->toDateString();
            $employeeId = Auth::id();

            // First check for any pending clock out and complete it
            $pendingClockOuts = Attendance::where('employee_id', $employeeId)
                ->whereNull('clock_out')
                ->where('created_by', creatorId())
                ->get();

            if ($pendingClockOuts) {
                foreach ($pendingClockOuts as $pendingClockOut) {
                    $employee = Employee::where('user_id', $employeeId)->where('created_by', creatorId())->first();
                    $shift = $employee ? Shift::find($employee->shift) : null;

                    if ($shift) {
                        $clockInDate = \Carbon\Carbon::parse($pendingClockOut->clock_in)->format('Y-m-d');
                        $shiftEndDateTime = \Carbon\Carbon::parse($clockInDate . ' ' . $shift->end_time);

                        // For night shifts, shift end is next day
                        if ($shift->end_time < $shift->start_time) {
                            $shiftEndDateTime->addDay();
                        }

                        // Auto complete previous attendance with shift end time
                        $calculatedData = $this->calculateAttendanceData(
                            $pendingClockOut->clock_in,
                            $shiftEndDateTime,
                            0,
                            $shift->id,
                            $employee
                        );


                        $pendingClockOut->update([
                            'clock_out' => $shiftEndDateTime,
                            'total_hour' => $calculatedData['total_hour']['total_working_hours'],
                            'break_hour' => $calculatedData['total_hour']['total_break_hours'],
                            'overtime_hours' => $calculatedData['overtime_hours'],
                            'overtime_amount' => $calculatedData['overtime_amount'],
                            'status' => $calculatedData['status'],
                        ]);
                    }
                }
            }

            // Check if already clocked in today
            $existingAttendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $today)
                ->where('created_by', creatorId())
                ->first();


            if ($existingAttendance && $existingAttendance->clock_in) {
                return redirect()->back()->with('error', __('You have already clocked in today.'));
            }



            // $clockInTime = now()->format('H:i:s');
            $clockInTime = now();

            if ($existingAttendance) {
                $existingAttendance->update(['clock_in' => $clockInTime]);
            } else {
                $employee = Employee::where('user_id', $employeeId)->where('created_by', creatorId())->first();
                $shift = $employee ? $employee->shift : null;

                Attendance::create([
                    'employee_id' => $employeeId,
                    'shift_id' => $shift,
                    'date' => $today,
                    'clock_in' => $clockInTime,
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId(),
                ]);
            }

            return redirect()->back()->with('success', __('Clocked in successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied'));
    }

    public function clockOut()
    {
        if (Auth::user()->can('clock-out')) {
            // Check IP restriction
            $setting = getCompanyAllSetting();
            if (isset($setting['ip_restrict']) && $setting['ip_restrict'] === 'on') {
                $userIp = request()->ip();
                $allowedIp = IpRestrict::where('ip', $userIp)
                    ->where('created_by', creatorId())
                    ->exists();
                
                if (!$allowedIp) {
                    return redirect()->back()->with('error', __('This IP is not allowed to clock in & clock out.'));
                }
            }

            $today = now()->toDateString();
            $employeeId = Auth::id();

            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $today)
                ->where('created_by', creatorId())
                ->first();

            // If no today's attendance, check for pending attendance from previous days
            if (!$attendance || !$attendance->clock_in) {
                $attendance = Attendance::where('employee_id', $employeeId)
                    ->whereNull('clock_out')
                    ->where('created_by', creatorId())
                    ->orderBy('clock_in', 'desc')
                    ->first();
            }

            if (!$attendance || !$attendance->clock_in) {
                return redirect()->back()->with('error', __('You must clock in first.'));
            }

            if ($attendance->clock_out) {
                return redirect()->back()->with('error', __('You have already clocked out today.'));
            }

            // $clockOutTime = now()->format('H:i:s');
            $clockOutTime = now();
            $employee = Employee::with('shift')->where('user_id', $employeeId)->where('created_by', creatorId())->first();
            $shift = $employee ? $employee->shift : null;

            // Calculate attendance data using existing logic
            $calculatedData = $this->calculateAttendanceData(
                $attendance->clock_in,
                $clockOutTime,
                0, // break_hour
                $shift,
                $employee
            );

            $attendance->update([
                'clock_out' => $clockOutTime,
                'total_hour' => $calculatedData['total_hour']['total_working_hours'],
                'break_hour' => $calculatedData['total_hour']['total_break_hours'],
                'overtime_hours' => $calculatedData['overtime_hours'],
                'overtime_amount' => $calculatedData['overtime_amount'],
                'status' => $calculatedData['status'],
            ]);

            return redirect()->back()->with('success', __('Clocked out successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied'));
    }

    public function getClockStatus()
    {
        $today = now()->toDateString();
        $employeeId = Auth::id();

        $attendance = Attendance::where('employee_id', $employeeId)
            ->where('date', $today)
            ->where('created_by', creatorId())
            ->first();

        return response()->json([
            'is_clocked_in' => $attendance && $attendance->clock_in && !$attendance->clock_out,
            'clock_in_time' => $attendance ? $attendance->clock_in : null,
            'clock_out_time' => $attendance ? $attendance->clock_out : null,
            'total_working_hours' => $attendance && $attendance->total_hour ? $attendance->total_hour . ' hours' : null,
        ]);
    }

    private function getFilteredEmployees()
    {
        $employeeQuery = Employee::where('created_by', creatorId());

        if (Auth::user()->can('manage-own-attendances') && !Auth::user()->can('manage-any-attendances')) {
            $employeeQuery->where(function ($q) {
                $q->where('creator_id', Auth::id())->orWhere('user_id', Auth::id());
            });
        }

        $userIds = $employeeQuery->pluck('user_id');

        $users = User::emp()->where('created_by', creatorId())
            ->whereIn('id', $userIds)
            ->select('id', 'name', 'avatar')
            ->get()
            ->keyBy('id');

        $employees = Employee::where('created_by', creatorId())
            ->whereIn('user_id', $userIds)
            ->with('designation')
            ->get()
            ->keyBy('user_id');

        return $users->map(function ($user) use ($employees) {
            $employee = $employees->get($user->id);
            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'designation' => $employee?->designation ? [
                    'id' => $employee->designation->id,
                    'designation_name' => $employee->designation->designation_name,
                ] : null,
            ];
        })->values();
    }
}
