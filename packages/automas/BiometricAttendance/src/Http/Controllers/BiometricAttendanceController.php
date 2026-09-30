<?php

namespace Automas\BiometricAttendance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\BiometricAttendance\Models\BiometricSetting;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\Shift;
use Automas\Hrm\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class BiometricAttendanceController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('manage-biometric-attendance')) {
            $setting = BiometricSetting::where('created_by', creatorId())->first();
            if (!$setting) {
                return Inertia::render('BiometricAttendance/Index', [
                    'attendances' => new LengthAwarePaginator([], 0, 10),
                    'employees' => [],
                    'configurationMissing' => true,
                    'isZktecoSync' => false
                ]);
            }

            $api_urls = $setting->zkteco_api_url ?? '';
            $token = $setting->auth_token ?? '';
            $isZktecoSync = $setting->is_zkteco_sync ?? false;

            $configurationMissing = (empty($api_urls) || empty($token) || !$isZktecoSync);

            if (!empty($request->date_from) && !empty($request->date_to)) {
                $date_from = $request->date_from . ' 00:00:00';
                $date_to = $request->date_to . ' 23:59:59';
            } else {
                $date_from = date('Y-m-d', strtotime('-7 days')) . ' 00:00:00';
                $date_to = date('Y-m-d') . ' 23:59:59';
            }

            $attendances = [];
            $employees = Employee::with('user')->where('created_by', creatorId())->get();

            // Only fetch from API if we have a valid auth token
            if (!$configurationMissing && $setting->auth_token) {
                $attendances = $this->fetchAttendanceFromAPI($setting, $date_from, $date_to, 10000);
                if ($attendances === false) {
                    $attendances = [];
                }
            }   
            

            // Group by employee code and date
            $groupedAttendances = collect($attendances)
                ->groupBy(function ($item) {
                    return $item['emp_code'] . '_' . date('Y-m-d', strtotime($item['punch_time']));
                })
                ->map(function ($dayEntries) {
                    $sorted = $dayEntries->sortBy('punch_time');
                    $firstEntry = $sorted->first();
                    $lastEntry = $sorted->last();
                    $employee = Employee::with('user')->where('biometric_emp_id', $firstEntry['emp_code'])->where('created_by', creatorId())->first();

                    return [
                        'id' => $firstEntry['id'],
                        'employee_code' => $firstEntry['emp_code'],
                        'name' => $employee && $employee->user ? $employee->user->name : ($firstEntry['first_name'] ?? 'Unknown'),
                        'employee_id' => $employee ? $employee->employee_id : $firstEntry['emp_code'],
                        'date' => date('Y-m-d', strtotime($firstEntry['punch_time'])),
                        'clock_in' => date('H:i:s', strtotime($firstEntry['punch_time'])),
                        'clock_out' => $sorted->count() > 1 ? date('H:i:s', strtotime($lastEntry['punch_time'])) : null,
                        'total_entries' => $sorted->count(),
                        'biometric_id' => $firstEntry['id'],
                        'status' => 'present',
                    ];
                });
            $query = $groupedAttendances->values();

            
            // Handle search
            if ($request->has('search') && !empty($request->search)) {
                $query = $query->filter(function ($item) use ($request) {
                    return stripos($item['name'], $request->search) !== false ||
                        stripos($item['employee_code'], $request->search) !== false;
                });
            }

            // Handle employee filter
            if ($request->has('employee_id') && !empty($request->employee_id)) {
                $selectedEmployee = Employee::find($request->employee_id);
                if ($selectedEmployee) {
                    $query = $query->filter(function ($item) use ($selectedEmployee) {
                        return $item['name'] === $selectedEmployee->user->name;
                    });
                }
            }

            // Handle date range filter
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query = $query->filter(function ($item) use ($request) {
                    return $item['date'] >= $request->date_from;
                });
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query = $query->filter(function ($item) use ($request) {
                    return $item['date'] <= $request->date_to;
                });
            }

            // Handle sorting
            if ($request->has('sort') && !empty($request->sort)) {
                $direction = $request->direction ?? 'asc';
                $sortField = $request->sort;
                
                if ($sortField === 'name') {
                    $query = $direction === 'desc' ? $query->sortByDesc('name') : $query->sortBy('name');
                } elseif ($sortField === 'date') {
                    $query = $direction === 'desc' ? $query->sortByDesc('date') : $query->sortBy('date');
                } else {
                    $query = $direction === 'desc' ? $query->sortByDesc($sortField) : $query->sortBy($sortField);
                }
            } else {
                $query = $query->sortByDesc('date')->sortByDesc('id');
            }

            // Manual pagination for collection
            $perPage = $request->per_page ?? 10;
            $currentPage = $request->page ?? 1;
            $total = $query->count();
            $items = $query->forPage($currentPage, $perPage)->values();

            $attendances = new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );

            return Inertia::render('BiometricAttendance/Index', [
                'attendances' => $attendances,
                'employees' => $employees,
                'configurationMissing' => $configurationMissing,
                'isZktecoSync' => $isZktecoSync
            ]);
        } 
        else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(Request $request, $employeeCode, $date)
    {
        try {
            if (Auth::user()->can('view-biometric-attendance')) {
                // demo data code
                // if (config('app.run_demo_seeder')) {
                //     $allDemoData = $this->getDemoData();
                //     $attendances = collect($allDemoData)
                //         ->filter(function ($item) use ($employeeCode, $date) {
                //             return $item['emp_code'] === $employeeCode &&
                //                 date('Y-m-d', strtotime($item['punch_time'])) === $date;
                //         })
                //         ->values()
                //         ->toArray();
                // } else {
                    $setting = BiometricSetting::where('created_by', creatorId())->first();
                    if (!$setting?->auth_token || !$setting?->zkteco_api_url) {
                        return response()->json([
                            'success' => false,
                            'message' => __('ZKTeco api configuration missing')
                        ], 400);
                    }

                    $start_date = $date . ' 00:00:00';
                    $end_date = $date . ' 23:59:59';
                    
                    $attendances = $this->fetchAttendanceFromAPI($setting, $start_date, $end_date, 1000, $employeeCode);
                    
                    if ($attendances === false) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Failed to fetch data from ZKTeco API')
                        ], 500);
                    }
                // }


                // Process entries (already filtered by API)
                $dayEntries = collect($attendances)
                    ->sortBy('punch_time')
                    ->map(function ($item) {
                        return [
                            'id' => $item['id'],
                            'punch_time' => $item['punch_time'],
                            'time' => date('H:i:s', strtotime($item['punch_time'])),
                            'punch_state_display' => $item['punch_state_display'] ?? 'Unknown',
                            'verify_type_display' => $item['verify_type_display'] ?? 'Unknown',
                            'terminal_alias' => $item['terminal_alias'] ?? 'Unknown'
                        ];
                    })
                    ->values();

                if ($dayEntries->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('No entries found for this employee and date')
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'entries' => $dayEntries,
                        'employee_code' => $employeeCode,
                        'date' => $date
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied')
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to fetch attendance details')
            ], 500);
        }
    }

    public function sync(Request $request)
    {
        if (Auth::user()->can('sync-biometric-attendance')) {
            $setting = BiometricSetting::where('created_by', creatorId())->first();
            if (!$setting?->auth_token) {
                return back()->with('error', __('Please configure biometric settings first'));
            }

            $biometricEmpId = $request->biometric_emp_id;
            $biometricId = $request->biometric_id;
            $attedanceDate = $request->date;
            $clockInTime = $request->clock_in;
            $clockOutTime = $request->clock_out;

            $employee = Employee::with('user')
                ->where('created_by', creatorId())
                ->where('biometric_emp_id', $biometricEmpId)
                ->first();

            if ($employee) {

                if (is_null($clockOutTime)) {
                    return redirect()->back()->with('error', __("Still employee is not clock out. so you can't sync that attedance."));
                }

                // Check Attendance Already Sync or not 
                $attendance = Attendance::where('biometric_id', '=', $biometricId)
                    ->where('date', '=', $attedanceDate)
                    ->where('created_by', '=', creatorId())
                    ->first();

                if ($attendance) {
                    return redirect()->back()->with('error', __('Attendance is already sync.'));
                }

                // Check if record already exists
                $exists = Attendance::where('employee_id', '=', $employee->user_id)
                    ->where('date', '=', $attedanceDate)
                    ->where('created_by', '=', creatorId())
                    ->exists();

                if ($exists) {
                    return redirect()->back()->with('error', __('Attendance record already exists for this employee and date.'));
                } else {
                    $shift = Shift::where('id', '=', $employee->shift_id)
                        ->where('status', '=', 'active')
                        ->first();

                    if (!$shift) {
                        $shift = Shift::where('created_by', '=', creatorId())
                            ->where('status', '=', 'active')
                            ->first();
                    }

                    $clockInDateTime = $attedanceDate . ' ' . $clockInTime;
                    $clockOutDateTime = $clockOutTime ? $attedanceDate . ' ' . $clockOutTime : null;

                    // Calculate attendance data first
                    $calculatedData = $this->calculateAttendanceData($clockInDateTime,$clockOutDateTime,0,$shift,$employee);
                    
                    $attendance = new Attendance();
                    $attendance->employee_id = $employee->user_id;
                    $attendance->biometric_id = $biometricId;
                    $attendance->shift_id = $shift?->id;
                    $attendance->date = $attedanceDate;
                    $attendance->clock_in = $clockInDateTime;
                    $attendance->clock_out = $clockOutDateTime;
                    $attendance->total_hour = $calculatedData['total_hour']['total_working_hours'];
                    $attendance->break_hour = $calculatedData['total_hour']['total_break_hours'];
                    $attendance->overtime_hours = $calculatedData['overtime_hours'];
                    $attendance->overtime_amount = $calculatedData['overtime_amount'];
                    $attendance->status = $calculatedData['status'];
                    $attendance->creator_id = Auth::id();
                    $attendance->created_by = creatorId();
                    $attendance->save();

                    return redirect()->back()->with('success', __('Biometric data synced successfully.'));
                }
            } else {
                return redirect()->back()->with('error', __('Employee not found'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
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

    public function syncAllByDateRange(Request $request)
    {
        if (Auth::user()->can('sync-biometric-attendance')) {
            $setting = BiometricSetting::where('created_by', creatorId())->first();
            if (!$setting?->auth_token) {
                return back()->with('error', __('Please configure biometric settings first'));
            }

            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
            
            $attendances = $this->fetchAttendanceFromAPI($setting, $startDate, $endDate);
            
            if (empty($attendances)) {
                return back()->with('error', __('No attendance data found'));
            }

            $employees = Employee::with('user')
                ->where('created_by', creatorId())
                ->whereNotNull('biometric_emp_id')
                ->get()
                ->keyBy('biometric_emp_id');

            $groupedAttendances = collect($attendances)
                ->groupBy(function ($item) {
                    return $item['emp_code'] . '_' . date('Y-m-d', strtotime($item['punch_time']));
                })
                ->map(function ($dayEntries) {
                    $sorted = $dayEntries->sortBy('punch_time');
                    $firstEntry = $sorted->first();
                    $lastEntry = $sorted->last();

                    return [
                        'biometric_emp_id' => $firstEntry['emp_code'],
                        'biometric_id' => $firstEntry['id'],
                        'date' => date('Y-m-d', strtotime($firstEntry['punch_time'])),
                        'clock_in' => date('H:i:s', strtotime($firstEntry['punch_time'])),
                        'clock_out' => $sorted->count() > 1 ? date('H:i:s', strtotime($lastEntry['punch_time'])) : null,
                    ];
                })
                ->filter(function ($item) {
                    return !is_null($item['clock_out']);
                });

            $successCount = 0;
            $errorCount = 0;

            foreach ($groupedAttendances as $attendanceData) {
                try {
                    $employee = $employees->get($attendanceData['biometric_emp_id']);
                    
                    if (!$employee || Attendance::where('employee_id', $employee->user_id)
                        ->where('date', $attendanceData['date'])
                        ->where('created_by', creatorId())
                        ->exists()) {
                        $errorCount++;
                        continue;
                    }

                    $shift = Shift::where('id', $employee->shift_id)
                        ->where('status', 'active')
                        ->first() ?: Shift::where('created_by', creatorId())
                        ->where('status', 'active')
                        ->first();

                    $clockInDateTime = $attendanceData['date'] . ' ' . $attendanceData['clock_in'];
                    $clockOutDateTime = $attendanceData['date'] . ' ' . $attendanceData['clock_out'];

                    $calculatedData = $this->calculateAttendanceData($clockInDateTime, $clockOutDateTime, 0, $shift, $employee);
                    
                    $attendance = new Attendance();
                    $attendance->employee_id     = $employee->user_id;
                    $attendance->biometric_id    = $attendanceData['biometric_id'];
                    $attendance->shift_id        = $shift?->id;
                    $attendance->date            = $attendanceData['date'];
                    $attendance->clock_in        = $clockInDateTime;
                    $attendance->clock_out       = $clockOutDateTime;
                    $attendance->total_hour      = $calculatedData['total_hour']['total_working_hours'];
                    $attendance->break_hour      = $calculatedData['total_hour']['total_break_hours'];
                    $attendance->overtime_hours  = $calculatedData['overtime_hours'];
                    $attendance->overtime_amount = $calculatedData['overtime_amount'];
                    $attendance->status          = $calculatedData['status'];
                    $attendance->creator_id      = Auth::id();
                    $attendance->created_by      = creatorId();
                    $attendance->save();

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }

            return back()->with($successCount > 0 ? 'success' : 'error', 
                "Bulk sync: {$successCount} successful, {$errorCount} failed");
        }
        
        return back()->with('error', __('Permission Denied.'));
    }

    private function fetchAttendanceFromAPI($setting, $startDate, $endDate, $pageSize = 5000, $empCode = null)
    {
        // if (config('app.run_demo_seeder')) {
        //     return $this->getDemoData();
        // }

        $params = [
            'start_time' => $startDate,
            'end_time' => $endDate,
            'page_size' => $pageSize,
        ];
        
        if ($empCode) {
            $params['emp_code'] = $empCode;
        }

        $api_url = rtrim($setting->zkteco_api_url, '/');
        $url = $api_url . '/iclock/api/transactions/?' . http_build_query($params);

        $curl = curl_init();
        try {
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Token ' . $setting->auth_token
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            
            if ($curlError) {
                \Log::warning('ZKTeco API cURL Error', ['error' => $curlError, 'url' => $url]);
                return false;
            }
            
            if ($httpCode !== 200) {
                \Log::warning('ZKTeco API HTTP Error', ['http_code' => $httpCode, 'url' => $url]);
                return false;
            }
            
            if ($httpCode === 200 && $response) {
                $json_attendance = json_decode($response, true);
                return $json_attendance['data'] ?? [];
            }
            
            return false;
        } catch (\Throwable $th) {
            return false;
        } finally {
            curl_close($curl);
        }
    }

    public function getDemoData()
    {
        $cacheKey = 'biometric_demo_data_' . creatorId();
        
        return cache()->remember($cacheKey, 3600, function () {
            $employees = Employee::with(['user', 'department', 'designation'])
                ->where('created_by', creatorId())
                ->whereNotNull('biometric_emp_id')
                ->get();

            if ($employees->isEmpty()) {
                return [];
            }

            $data = [];
            $id = 2285;
            $empCode = 201;
        
            for ($day = 3; $day >= 0; $day--) {
                $date = date('Y-m-d', strtotime("-{$day} days"));
                $tempEmpCode = $empCode;
                
                foreach ($employees as $empIndex => $employee) {
                    $clockInHour = str_pad(8 + ($empIndex % 3), 2, '0', STR_PAD_LEFT);
                    $clockOutHour = str_pad(17 + ($empIndex % 3), 2, '0', STR_PAD_LEFT);
                    
                    // Clock In
                    $data[] = [
                        "id" => $id++,
                        "emp" => 10,
                        "emp_code" => $employee->employee_id ?? (string)$tempEmpCode,
                        "first_name" => $employee->user->name,
                        "last_name" => null,
                        "department" => $employee->department->department_name ?? 'Unknown Department',
                        "position" => $employee->designation->designation_name ?? 'Unknown Position',
                        "punch_time" => "{$date} {$clockInHour}:" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                        "punch_state" => "255",
                        "punch_state_display" => "Clock In",
                        "verify_type" => 1,
                        "verify_type_display" => "Fingerprint",
                        "work_code" => "",
                        "gps_location" => null,
                        "area_alias" => "Operation Office",
                        "terminal_sn" => "COAW221061101",
                        "temperature" => 0.0,
                        "is_mask" => "-",
                        "terminal_alias" => "F18/ID",
                        "upload_time" => "{$date} " . str_pad($clockInHour, 2, '0', STR_PAD_LEFT) . ":05:23"
                    ];
                    
                    // Break Out (50% chance)
                    if (rand(0, 1)) {
                        $data[] = [
                            "id" => $id++,
                            "emp" => 10,
                            "emp_code" => $employee->employee_id ?? (string)$tempEmpCode,
                            "first_name" => $employee->user->name,
                            "last_name" => null,
                            "department" => $employee->department->department_name ?? 'Unknown Department',
                            "position" => $employee->designation->designation_name ?? 'Unknown Position',
                            "punch_time" => "{$date} 12:" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ":00",
                            "punch_state" => "255",
                            "punch_state_display" => "Break Out",
                            "verify_type" => 1,
                            "verify_type_display" => "Fingerprint",
                            "work_code" => "",
                            "gps_location" => null,
                            "area_alias" => "Operation Office",
                            "terminal_sn" => "COAW221061101",
                            "temperature" => 0.0,
                            "is_mask" => "-",
                            "terminal_alias" => "F18/ID",
                            "upload_time" => "{$date} 12:05:00"
                        ];
                        
                        // Break In
                        $data[] = [
                            "id" => $id++,
                            "emp" => 10,
                            "emp_code" => $employee->employee_id ?? (string)$tempEmpCode,
                            "first_name" => $employee->user->name,
                            "last_name" => null,
                            "department" => $employee->department->department_name ?? 'Unknown Department',
                            "position" => $employee->designation->designation_name ?? 'Unknown Position',
                            "punch_time" => "{$date} 13:" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ":00",
                            "punch_state" => "255",
                            "punch_state_display" => "Break In",
                            "verify_type" => 1,
                            "verify_type_display" => "Fingerprint",
                            "work_code" => "",
                            "gps_location" => null,
                            "area_alias" => "Operation Office",
                            "terminal_sn" => "COAW221061101",
                            "temperature" => 0.0,
                            "is_mask" => "-",
                            "terminal_alias" => "F18/ID",
                            "upload_time" => "{$date} 13:05:00"
                        ];
                    }
                    
                    // Clock Out
                    $data[] = [
                        "id" => $id++,
                        "emp" => 10,
                        "emp_code" => $employee->employee_id ?? (string)$tempEmpCode,
                        "first_name" => $employee->user->name,
                        "last_name" => null,
                        "department" => $employee->department->department_name ?? 'Unknown Department',
                        "position" => $employee->designation->designation_name ?? 'Unknown Position',
                        "punch_time" => "{$date} {$clockOutHour}:" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ":00",
                        "punch_state" => "255",
                        "punch_state_display" => "Clock Out",
                        "verify_type" => 1,
                        "verify_type_display" => "Fingerprint",
                        "work_code" => "",
                        "gps_location" => null,
                        "area_alias" => "Operation Office",
                        "terminal_sn" => "COAW221061101",
                        "temperature" => 0.0,
                        "is_mask" => "-",
                        "terminal_alias" => "F18/ID",
                        "upload_time" => "{$date} " . str_pad($clockOutHour, 2, '0', STR_PAD_LEFT) . ":35:00"
                    ];
                    
                    $tempEmpCode++;
                }
            }

            return $data;
        });
    }
}
               