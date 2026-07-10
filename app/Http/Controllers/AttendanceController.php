<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\AttendanceSummary;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // ============ MAIN ATTENDANCE PAGE ============
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $fortnight = $request->fortnight ?? $this->getCurrentFortnight();
        $currentFortnight = $this->getCurrentFortnight();
        $selectedEmployeeId = $request->employee_id;

        // Check if selected employee belongs to this company
        if ($selectedEmployeeId) {
            $employee = Employee::where('id', $selectedEmployeeId)
                ->where('company_id', $companyId)
                ->first();
            
            if (!$employee) {
                $selectedEmployeeId = null;
                return redirect()->route('attendance.index', [
                    'fortnight' => $fortnight
                ]);
            }
        }

        // Get all fortnights for dropdown
        $fortnights = [];
        $fortnightPeriods = [];
        for ($i = 1; $i <= 26; $i++) {
            $fn = date('y') . str_pad($i, 2, '0', STR_PAD_LEFT);
            $fortnights[] = $fn;
            $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
        }

        $period = $this->getFortnightPeriod($fortnight);

        // Get all employees for dropdown
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get();

        // Get selected employee data
        $selectedEmployee = null;
        $selectedDayLogs = collect();
        $timesheetStatus = 'Draft';

        if ($selectedEmployeeId) {
            $selectedEmployee = Employee::find($selectedEmployeeId);
            
            if ($selectedEmployee) {
                // Get logs for selected employee and fortnight
                $selectedDayLogs = AttendanceLog::where('employee_id', $selectedEmployeeId)
                    ->where('fortnight_number', $fortnight)
                    ->get()
                    ->keyBy(function($item) {
                        return $item->date->format('Y-m-d');
                    });

                // Get status from first log
                $firstLog = $selectedDayLogs->first();
                $timesheetStatus = $firstLog ? $firstLog->timesheet_status : 'Draft';
            }
        }

        // GET HOLIDAYS FOR THE COMPANY
        $publicHolidays = $this->getPublicHolidays($companyId);
        $holidayDates = [];
        foreach ($publicHolidays as $date) {
            $holidayDates[$date] = true;
        }

        return view('attendance.index', compact(
            'employees', 
            'fortnight', 
            'period', 
            'fortnights', 
            'fortnightPeriods',
            'selectedEmployeeId', 
            'selectedEmployee', 
            'selectedDayLogs',
            'currentFortnight', 
            'timesheetStatus',
            'holidayDates'  //  ADD THIS
        ));
    }

    // ============ BULK UPDATE ============
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'fortnight' => 'required|string',
            'attendance' => 'required|array',
        ]);

        $employeeId = $request->employee_id;
        $fortnight = $request->fortnight;
        $action = $request->input('action', 'save');
        $attendanceData = $request->attendance;
        $companyId = auth()->user()->company_id;
        $publicHolidays = $this->getPublicHolidays($companyId);

        // Check current status
        $existingLog = AttendanceLog::where('employee_id', $employeeId)
            ->where('fortnight_number', $fortnight)
            ->first();

        $currentStatus = $existingLog ? $existingLog->timesheet_status : 'Draft';

        // If Locked, block all actions
        if ($currentStatus === 'Locked') {
            return redirect()->route('attendance.index', [
                'fortnight' => $fortnight,
                'employee_id' => $employeeId
            ])->with('error', '❌ This timesheet is LOCKED. No changes allowed.');
        }

        // If Final, only allow Save and Lock
        if ($currentStatus === 'Final' && !in_array($action, ['save', 'lock'])) {
            return redirect()->route('attendance.index', [
                'fortnight' => $fortnight,
                'employee_id' => $employeeId
            ])->with('error', '⚠️ This timesheet is FINALIZED. Only Save and Lock are allowed.');
        }

        foreach ($attendanceData as $dateKey => $data) {
            $hours = $data['hours'] ?? 0;
            $type = $data['type'] ?? 'Work';
            $notes = $data['notes'] ?? '';

            $nonPayTypes = ['Annual Leave', 'Leave Without Pay', 'Absent'];
            if (in_array($type, $nonPayTypes)) {
                $hours = 0;
            }

            $isSunday = Carbon::parse($dateKey)->isSunday();
            $isHoliday = in_array($dateKey, $publicHolidays);

            $updateData = [
                'hours_worked' => $hours,
                'attendance_type' => $type,
                'notes' => $notes,
                'is_sunday' => $isSunday,
                'is_holiday' => $isHoliday,
                'fortnight_number' => $fortnight,
            ];

            //  Only change status if NOT Final
            if ($currentStatus !== 'Final') {
                if ($action === 'finalize') {
                    $updateData['timesheet_status'] = 'Final';
                    $updateData['finalized_at'] = now();
                    $updateData['finalized_by'] = auth()->id();
                }

                if ($action === 'lock') {
                    $updateData['timesheet_status'] = 'Locked';
                    $updateData['locked_at'] = now();
                    $updateData['locked_by'] = auth()->id();
                }
            }

            //  If Save - keep existing status
            if ($action === 'save') {
                // Don't change status
            }

            AttendanceLog::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $dateKey,
                ],
                $updateData
            );
        }

        // Update the summary after saving logs
        $this->updateSummary($employeeId, $fortnight);

        $messages = [
            'save' => ' Timesheet saved successfully!',
            'finalize' => 'Timesheet FINALIZED! You can still edit hours but status cannot be changed.',
            'lock' => ' Timesheet LOCKED! No further edits allowed.',
        ];

        return redirect()->route('attendance.index', [
            'fortnight' => $fortnight,
            'employee_id' => $employeeId
        ])->with('success', $messages[$action] ?? 'Timesheet saved successfully!');
    }

    // ============ SAVE ATTENDANCE LOG HELPER ============
    private function saveAttendanceLog($employeeId, $dateKey, $dayData, $fortnight, $publicHolidays)
    {
        $timeIn = $dayData['time_in'] ?? null;
        $timeOut = $dayData['time_out'] ?? null;
        $hasBreak = $dayData['has_break'] ?? false;

        $isSunday = Carbon::parse($dateKey)->isSunday();
        $isHoliday = in_array($dateKey, $publicHolidays);

        $hoursWorked = 0;
        if ($timeIn && $timeOut) {
            try {
                $start = Carbon::parse($timeIn);
                $end = Carbon::parse($timeOut);
                if ($end->greaterThan($start)) {
                    $hoursWorked = $start->diffInHours($end);
                } else {
                    $end->addDay();
                    $hoursWorked = $end->diffInHours($start);
                }
                if ($hasBreak && $hoursWorked > 0) {
                    $hoursWorked = max(0, $hoursWorked - 1);
                }
            } catch (\Exception $e) {
                $hoursWorked = 0;
            }
        }

        AttendanceLog::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $dateKey,
            ],
            [
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'hours_worked' => $hoursWorked,
                'has_break' => $hasBreak,
                'is_sunday' => $isSunday,
                'is_holiday' => $isHoliday,
                'fortnight_number' => $fortnight,
                'created_by' => auth()->id(),
            ]
        );
    }

    // ============ STORE ATTENDANCE ============
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'has_break' => 'nullable|boolean',
        ]);

        $fortnight = $this->getFortnightNumber($request->date);
        $isSunday = Carbon::parse($request->date)->isSunday();
        $publicHolidays = $this->getPublicHolidays();
        $isHoliday = in_array($request->date, $publicHolidays);

        $hoursWorked = 0;
        if ($request->time_in && $request->time_out) {
            $start = Carbon::parse($request->time_in);
            $end = Carbon::parse($request->time_out);
            $hoursWorked = $end->diffInHours($start);
            if ($request->has_break && $hoursWorked > 0) {
                $hoursWorked = max(0, $hoursWorked - 1);
            }
        }

        AttendanceLog::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'date' => $request->date,
            ],
            [
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'hours_worked' => $hoursWorked,
                'has_break' => $request->has_break ?? false,
                'is_sunday' => $isSunday,
                'is_holiday' => $isHoliday,
                'fortnight_number' => $fortnight,
                'created_by' => auth()->id(),
            ]
        );

        $this->updateSummary($request->employee_id, $fortnight);

        return redirect()->route('attendance.index', [
            'fortnight' => $fortnight,
            'date' => $request->date
        ])->with('success', 'Attendance saved successfully.');
    }

    // ============ DELETE ATTENDANCE ============
    public function destroy(AttendanceLog $log)
    {
        $date = $log->date->format('Y-m-d');
        $fortnight = $log->fortnight_number;
        $employeeId = $log->employee_id;
        
        $log->delete();
        $this->updateSummary($employeeId, $fortnight);

        return redirect()->route('attendance.index', [
            'fortnight' => $fortnight,
            'date' => $date
        ])->with('success', 'Attendance deleted successfully.');
    }

    // ============ UPDATE SUMMARY ============
    private function updateSummary($employeeId, $fortnight)
    {
        $logs = AttendanceLog::where('employee_id', $employeeId)
            ->where('fortnight_number', $fortnight)
            ->get();

        $summary = AttendanceSummary::firstOrNew([
            'employee_id' => $employeeId,
            'fortnight_number' => $fortnight,
        ]);

        $dates = $logs->pluck('date')->sort();
        if ($dates->count() > 0) {
            $summary->period_start = $dates->first();
            $summary->period_end = $dates->last();
        }

        $regularHours = 0;
        $sundayHours = 0;
        $holidayHours = 0;
        $totalHours = 0;
        $nonWorkTypes = ['Annual Leave', 'Leave Without Pay', 'Absent'];

        foreach ($logs as $log) {
            $hours = $log->hours_worked;
            $type = $log->attendance_type ?? 'Work';

            if (in_array($type, $nonWorkTypes)) {
                continue;
            }

            $totalHours += $hours;

            if ($log->is_sunday) {
                $sundayHours += $hours;
            } elseif ($log->is_holiday) {
                $holidayHours += $hours;
            } else {
                $regularHours += $hours;
            }
        }

        $employee = Employee::find($employeeId);
        if ($employee && $employee->company) {
            $regularLimit = $employee->company->regular_hours ?? 84;
        } else {
            $regularLimit = 84;
        }

        $overtimeHours = 0;
        if ($regularHours > $regularLimit) {
            $overtimeHours = $regularHours - $regularLimit;
            $regularHours = $regularLimit;
        }

        $summary->regular_hours = $regularHours;
        $summary->overtime_hours = $overtimeHours;
        $summary->sunday_hours = $sundayHours;
        $summary->holiday_hours = $holidayHours;
        $summary->total_hours = $totalHours;
        $summary->total_days = $logs->count();
        $summary->present_days = $logs->where('hours_worked', '>', 0)->count();
        $summary->absent_days = $logs->where('hours_worked', 0)->count();
        $summary->save();

        return $summary;
    }

    // ============ HELPER METHODS ============

    public function getCurrentFortnight()
    {
        $year = date('y');
        $start = Carbon::createFromDate(date('Y') - 1, 12, 25);
        $daysSinceStart = $start->diffInDays(now()) + 1;
        $fortnight = ceil($daysSinceStart / 14);
        return $year . str_pad($fortnight, 2, '0', STR_PAD_LEFT);
    }

    private function getFortnightNumber($date)
    {
        $date = Carbon::parse($date);
        $year = $date->format('y');
        $dayOfYear = $date->dayOfYear;
        $fortnight = ceil($dayOfYear / 14);
        return $year . str_pad($fortnight, 2, '0', STR_PAD_LEFT);
    }

    public function getFortnightPeriod($fortnight)
    {
        $year = (int)substr($fortnight, 0, 2);
        $week = (int)substr($fortnight, 2);
        $fullYear = 2000 + $year;
        $start = Carbon::createFromDate($fullYear - 1, 12, 25)->addDays(($week - 1) * 14);
        $end = $start->copy()->addDays(13);
        return ['start' => $start, 'end' => $end];
    }

    public function show(Request $request, Employee $employee)
    {
        $date = $request->date ?? now()->toDateString();
        $fortnight = $this->getFortnightNumber($date);
        
        $logs = $employee->attendanceLogs()
            ->where('fortnight_number', $fortnight)
            ->orderBy('date', 'asc')
            ->get();

        $summary = $employee->getAttendanceSummary($fortnight);
        $log = $employee->attendanceLogs()->where('date', $date)->first();

        return view('attendance.show', compact('employee', 'logs', 'summary', 'fortnight', 'date', 'log'));
    }

    // ============ PUBLIC HOLIDAYS ============
    private function getPublicHolidays($companyId = null)
    {
        if (!$companyId) {
            $companyId = auth()->user()->company_id;
        }
        
        return Holiday::where('company_id', $companyId)
            ->where('is_active', true)
            ->pluck('date')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
    }
}