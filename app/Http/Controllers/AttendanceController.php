<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\AttendanceSummary;
use App\Models\Holiday;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\TimesheetExport;
use Maatwebsite\Excel\Facades\Excel;
class AttendanceController extends Controller
{
    // ============ MAIN ATTENDANCE PAGE ============
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $fortnight = $request->fortnight ?? $this->getCurrentFortnight();
        $currentFortnight = $this->getCurrentFortnight();
        $selectedEmployeeId = $request->employee_id;

        // Check if selected employee belongs to this company and allowed type
        if ($selectedEmployeeId) {
            $employee = Employee::where('id', $selectedEmployeeId)
                ->where('company_id', $companyId)
                ->active()
                ->whereIn('employee_type', $allowedTypes)
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

        // Get all employees for dropdown - filtered by user type and company
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('employee_number')
            ->orderBy('last_name')
            ->orderBy('first_name')
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
            'holidayDates'
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
        $companyId = auth()->user()->getCurrentCompanyId();
        $publicHolidays = $this->getPublicHolidays($companyId);

        // Check if employee is allowed
        $user = auth()->user();
        if ($action === 'finalize') {
            return redirect()->route('attendance.index', [
                'fortnight' => $fortnight,
                'employee_id' => $employeeId,
            ])->with('error', 'Finalizing timesheets is no longer available.');
        }

        $permission = $action === 'lock' ? 'lock-attendance' : 'save-attendance';
        if (!$this->hasAttendancePermission($user, $permission)) {
            return redirect()->route('attendance.index', [
                'fortnight' => $fortnight,
                'employee_id' => $employeeId,
            ])->with('error', 'You do not have permission to perform this attendance action.');
        }

        $employee = Employee::where('id', $employeeId)
            ->where('company_id', $companyId)
            ->active()
            ->first();

        if (!$employee || !$user->canViewEmployee($employee)) {
            return redirect()->route('attendance.index', [
                'fortnight' => $fortnight,
                'employee_id' => $employeeId
            ])->with('error', 'You are not authorized to manage this employee.');
        }

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

        if ($action === 'generate_expatriate_schedule') {
            if (!$employee->isExpatriate()) {
                return redirect()->route('attendance.index', [
                    'fortnight' => $fortnight,
                    'employee_id' => $employeeId,
                ])->with('error', 'Only expatriate employees can use the automatic schedule.');
            }

            $scheduleHours = (int) $request->input('expatriate_schedule_hours', 84);
            if (!in_array($scheduleHours, [84, 144], true)
                || $scheduleHours !== $employee->regular_hours_limit) {
                return redirect()->route('attendance.index', [
                    'fortnight' => $fortnight,
                    'employee_id' => $employeeId,
                ])->with('error', 'The selected expatriate schedule is not available for this employee.');
            }

            // Wrap generation and summary updating in a database transaction
            DB::transaction(function () use ($employee, $fortnight, $scheduleHours, $publicHolidays, $currentStatus) {
                $this->generateExpatriateSchedule($employee, $fortnight, $scheduleHours, $publicHolidays, $currentStatus);
                $this->updateSummary($employee->id, $fortnight);
            });

            return redirect()->route('attendance.index', [
                'fortnight' => $fortnight,
                'employee_id' => $employeeId,
            ])->with('success', "{$scheduleHours}-hour expatriate schedule generated successfully.");
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

            if ($action === 'lock') {
                $updateData['timesheet_status'] = 'Locked';
                $updateData['locked_at'] = now();
                $updateData['locked_by'] = auth()->id();
            }

            // If Save - keep existing status
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
            'lock' => ' Timesheet LOCKED! No further edits allowed.',
        ];

        return redirect()->route('attendance.index', [
            'fortnight' => $fortnight,
            'employee_id' => $employeeId
        ])->with('success', $messages[$action] ?? 'Timesheet saved successfully!');
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        $fortnight = $request->fortnight ?? $this->getCurrentFortnight();
        $period = $this->getFortnightPeriod($fortnight);
        $generated = $request->boolean('generated');

        $fortnights = [];
        $fortnightPeriods = [];
        for ($i = 1; $i <= 26; $i++) {
            $fn = date('y') . str_pad($i, 2, '0', STR_PAD_LEFT);
            $fortnights[] = $fn;
            $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
        }

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->whereHas('attendanceLogs', function ($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight)
                    ->where('hours_worked', '>', 0);
            })
            ->orderBy('employee_number')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $attendanceLogs = AttendanceLog::whereIn('employee_id', $employees->pluck('id'))
            ->where('fortnight_number', $fortnight)
            ->get()
            ->groupBy('employee_id')
            ->map(function ($logs) {
                return $logs->keyBy(function ($log) {
                    return $log->date->format('Y-m-d');
                });
            });

        $summaries = AttendanceSummary::whereIn('employee_id', $employees->pluck('id'))
            ->where('fortnight_number', $fortnight)
            ->get()
            ->keyBy('employee_id');

        $timesheetStatuses = $attendanceLogs->map(function ($logs) {
            return optional($logs->first())->timesheet_status ?? 'Draft';
        });

        $holidayDates = array_fill_keys($this->getPublicHolidays($companyId), true);

        return view('attendance.summary', compact(
            'employees',
            'attendanceLogs',
            'summaries',
            'timesheetStatuses',
            'fortnight',
            'period',
            'fortnights',
            'fortnightPeriods',
            'generated',
            'holidayDates'
        ));
    }

public function summaryBulkUpdate(Request $request)
    {
        $request->validate([
            'fortnight' => 'required|string',
            'attendance' => 'nullable|array',
            'summaries' => 'nullable|array',
            'summaries.*.regular_hours' => 'nullable|numeric|min:0',
            'summaries.*.overtime_hours' => 'nullable|numeric|min:0',
            'summaries.*.sunday_hours' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        $fortnight = $request->fortnight;
        $attendanceData = $request->attendance ?? [];
        $summaryData = $request->input('summaries', []);
        $publicHolidays = $this->getPublicHolidays($companyId);

        $action = $request->input('action');
        $requiredPermission = match ($action) {
            'unlock' => 'unlock-attendance',
            'lock_all' => 'lock-attendance',
            default => 'save-attendance',
        };
        if (!$this->hasAttendancePermission($user, $requiredPermission)) {
            return redirect()->back()->with('error', 'You do not have permission to perform this attendance action.');
        }

        if (in_array($action, ['unlock', 'reset'], true)) {
            $targetEmployeeId = (int) $request->input('target_employee_id');
            $returnToTimesheet = $request->input('redirect_to') === 'timesheet';
            $employee = Employee::where('id', $targetEmployeeId)
                ->where('company_id', $companyId)
                ->active()
                ->whereIn('employee_type', $allowedTypes)
                ->first();

            if (!$employee || !$user->canViewEmployee($employee)) {
                return $returnToTimesheet
                    ? redirect()->route('attendance.index', ['fortnight' => $fortnight])
                        ->with('error', 'You are not authorized to manage this employee.')
                    : redirect()->route('attendance.summary', ['fortnight' => $fortnight, 'generated' => 1])
                        ->with('error', 'You are not authorized to manage this employee.');
            }

            if ($action === 'unlock') {
                $updated = AttendanceLog::where('employee_id', $employee->id)
                    ->where('fortnight_number', $fortnight)
                    ->where('timesheet_status', 'Locked')
                    ->update([
                        'timesheet_status' => 'Draft',
                        'locked_at' => null,
                        'locked_by' => null,
                    ]);

                $message = $updated > 0
                    ? "Timesheet unlocked for {$employee->full_name}."
                    : "No locked timesheet was found for {$employee->full_name}.";
            } else {
                DB::transaction(function () use ($employee, $fortnight) {
                    AttendanceLog::where('employee_id', $employee->id)
                        ->where('fortnight_number', $fortnight)
                        ->delete();

                    AttendanceSummary::where('employee_id', $employee->id)
                        ->where('fortnight_number', $fortnight)
                        ->delete();
                });

                $message = "Attendance reset for {$employee->full_name}.";
            }

            return $returnToTimesheet
                ? redirect()->route('attendance.index', [
                    'fortnight' => $fortnight,
                    'employee_id' => $employee->id,
                ])->with('success', $message)
                : redirect()->route('attendance.summary', [
                    'fortnight' => $fortnight,
                    'generated' => 1,
                ])->with('success', $message);
        }

        $employeeIds = collect(array_merge(array_keys($attendanceData), array_keys($summaryData)))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $employees = Employee::where('company_id', $companyId)
            ->active()
            ->whereIn('employee_type', $allowedTypes)
            ->whereIn('id', $employeeIds)
            ->get()
            ->keyBy('id');

        $updatedEmployeeIds = collect();
        $skippedLocked = 0;

        foreach ($employees as $employee) {
            $dailyRows = $attendanceData[$employee->id] ?? [];

            if (!$employee || !$user->canViewEmployee($employee)) {
                continue;
            }

            // Check if timesheet is already locked
            $existingStatus = AttendanceLog::where('employee_id', $employee->id)
                ->where('fortnight_number', $fortnight)
                ->value('timesheet_status') ?? 'Draft';

            if ($existingStatus === 'Locked') {
                $skippedLocked++;
                continue;
            }

            foreach ($dailyRows as $dateKey => $data) {
                $type = $data['type'] ?? 'Work';
                $hoursInput = $data['hours'] ?? null;

                // Non-work types should always reset hours to 0
                if (in_array($type, ['Annual Leave', 'Leave Without Pay', 'Absent'], true)) {
                    $hours = 0;
                } else {
                    $hours = ($hoursInput === '' || $hoursInput === null) ? 0 : (float) $hoursInput;
                }

                $date = Carbon::parse($dateKey);

                AttendanceLog::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $dateKey,
                    ],
                    [
                        'hours_worked' => $hours,
                        'attendance_type' => $type,
                        'is_sunday' => $date->isSunday(),
                        'is_holiday' => in_array($dateKey, $publicHolidays, true),
                        'fortnight_number' => $fortnight,
                        'timesheet_status' => $existingStatus,
                        'created_by' => auth()->id(),
                    ]
                );
            }

            // Recalculate summary totals (REG, OT, Sun, Hol) for this employee
            $summary = $this->updateSummary($employee->id, $fortnight);

            // REG, OT, and Sunday hours can be adjusted directly on the
            // summary. Preserve the calculated holiday hours, then update the
            // grand total to match the saved component hours.
            $overrides = $summaryData[$employee->id] ?? [];
            if (!empty($overrides)) {
                $regularHours = array_key_exists('regular_hours', $overrides)
                    ? (float) $overrides['regular_hours']
                    : (float) $summary->regular_hours;
                $overtimeHours = array_key_exists('overtime_hours', $overrides)
                    ? (float) $overrides['overtime_hours']
                    : (float) $summary->overtime_hours;
                $sundayHours = array_key_exists('sunday_hours', $overrides)
                    ? (float) $overrides['sunday_hours']
                    : (float) $summary->sunday_hours;

                $summary->update([
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'sunday_hours' => $sundayHours,
                    'total_hours' => $regularHours + $overtimeHours + $sundayHours + (float) $summary->holiday_hours,
                ]);
            }
            $updatedEmployeeIds->push($employee->id);
        }

        $message = 'Attendance summary saved for ' . $updatedEmployeeIds->unique()->count() . ' employee(s).';
        if ($skippedLocked > 0) {
            $message .= " {$skippedLocked} locked timesheet(s) were skipped.";
        }

        return redirect()->route('attendance.summary', [
            'fortnight' => $fortnight,
            'generated' => 1,
        ])->with('success', $message);
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

        // Check if employee is allowed
        $user = auth()->user();
        $employee = Employee::find($request->employee_id);
        if (!$employee || $employee->status !== 'Active' || !$user->canViewEmployee($employee)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You are not authorized to manage this employee.');
        }

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
        // Check if employee is allowed
        $user = auth()->user();
        $employee = Employee::find($log->employee_id);
        if (!$user->canViewEmployee($employee)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You are not authorized to delete this attendance record.');
        }

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
    private function hasAttendancePermission(User $user, string $permission): bool
    {
        return $user->isSuperAdmin() || $user->can($permission);
    }

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

        $employee = Employee::find($employeeId);
        $isExpatriate = $employee?->isExpatriate() ?? false;
        $nonWorkTypes = ['Annual Leave', 'Leave Without Pay', 'Absent'];
        $period = $this->getFortnightPeriod($fortnight);

        // Expatriate employees are exempt from the holiday-credit / cap-
        // reduction logic entirely: their schedule already accounts for
        // holidays via generateExpatriateSchedule, so holidayHours stays 0
        // and the regular-hour cap is never reduced for them.
        if ($isExpatriate) {
            $holidayHours = 0;
        } else {
            $holidayDates = collect($this->getPublicHolidays($employee?->company_id))
                ->filter(fn ($date) => Carbon::parse($date)->betweenIncluded($period['start'], $period['end']));
            $workedDates = $logs
                ->filter(function ($log) use ($nonWorkTypes) {
                    return !in_array($log->attendance_type ?? 'Work', $nonWorkTypes, true)
                        && (float) $log->hours_worked > 0;
                })
                ->pluck('date')
                ->map(fn ($date) => $date->format('Y-m-d'));

            // Credit a holiday when the employee worked on the immediately
            // preceding day, the holiday itself, or the following day. Work
            // elsewhere in the fortnight does not create the automatic
            // holiday credit.
            // A holiday credit represents one standard scheduled day off.
            // YellowJacket Security's 144-hour national employees work
            // 12-hour shifts, so their holiday credit is 12 hours; everyone
            // else (84-hour employees) uses the standard 8-hour day.
            $standardDayHours = ($employee?->regular_hours_limit === 144) ? 12 : 8;

            $holidayHours = $holidayDates
                ->filter(function ($holidayDate) use ($workedDates) {
                    $holiday = Carbon::parse($holidayDate);
                    $previousDay = $holiday->copy()->subDay()->format('Y-m-d');
                    $holidayDay = $holiday->format('Y-m-d');
                    $nextDay = $holiday->copy()->addDay()->format('Y-m-d');

                    return $workedDates->contains($previousDay)
                        || $workedDates->contains($holidayDay)
                        || $workedDates->contains($nextDay);
                })
                ->count() * $standardDayHours;
        }

        $regularHours = 0;
        $sundayHours = 0;
        $totalHours = 0;
        $isSundayOvertimeExempt = $employee?->isSundayOvertimeExempt() ?? false;

        foreach ($logs as $log) {
            $hours = $log->hours_worked;
            $type = $log->attendance_type ?? 'Work';

            if (in_array($type, $nonWorkTypes)) {
                continue;
            }

            $totalHours += $hours;

            // Holiday credits are calculated above. Work actually performed on
            // a holiday joins the regular-hour pool and only becomes OT after
            // the reduced 84/144-hour cap is exceeded. For expatriates,
            // $isExpatriate is true so is_holiday is simply ignored here since
            // holidayHours is forced to 0 above.
            if ($log->is_sunday && !$log->is_holiday && !$isSundayOvertimeExempt) {
                $sundayHours += $hours;
            } else {
                $regularHours += $hours;
            }
        }

        $regularLimit = $employee?->regular_hours_limit ?? 84;

        // A holiday credit consumes part of the employee's regular-hours
        // entitlement (84 or 144). For example, one 8-hour holiday credit
        // reduces an 84-hour cap to 76; a 12-hour credit reduces a 144-hour
        // cap to 132. Overtime triggers off THIS reduced cap: once worked
        // hours exceed it, the excess is overtime rather than being
        // absorbed into the holiday bucket.
        $regularLimit = max(0, $regularLimit - $holidayHours);

        $overtimeHours = 0;
        if ($regularHours > $regularLimit) {
            $overtimeHours = $regularHours - $regularLimit;
            $regularHours = $regularLimit;
        }

        $totalHours += $holidayHours;

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
        $start = Carbon::createFromDate(date('Y') - 1, 12, 25)->startOfDay();
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
        // startOfDay() is required here: Carbon::createFromDate() only overrides
        // the Y/M/D and silently keeps the current server time-of-day. Without
        // resetting to midnight, a holiday landing on day 1 of the fortnight
        // would fail the betweenIncluded() check below (its 00:00:00 timestamp
        // would be "before" a start that carries e.g. 14:35:00), causing the
        // holiday credit to be silently dropped.
        $start = Carbon::createFromDate($fullYear - 1, 12, 25)->addDays(($week - 1) * 14)->startOfDay();
        $end = $start->copy()->addDays(13)->endOfDay();
        return ['start' => $start, 'end' => $end];
    }

    public function show(Request $request, Employee $employee)
    {
        $user = auth()->user();
        if (!$user->canViewEmployee($employee)) {
            abort(403, 'You are not authorized to view this employee.');
        }

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
        
        return Holiday::where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('is_global', true);
            })
            ->where('is_active', true)
            ->pluck('date')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->values()
            ->toArray();
    }

    private function generateExpatriateSchedule(Employee $employee, string $fortnight, int $scheduleHours, array $publicHolidays, string $currentStatus): void
    {
        $period = $this->getFortnightPeriod($fortnight);
        $records = [];

        for ($i = 0; $i < 14; $i++) {
            $date = $period['start']->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');
            
            $isSaturday = $date->isSaturday();
            $isSunday = $date->isSunday();
            $hours = $isSunday ? 0 : ($scheduleHours === 144 ? 12 : ($isSaturday ? 2 : 8));

            $record = [
                'employee_id'      => $employee->id,
                'date'             => $dateString,
                'hours_worked'     => $hours,
                'attendance_type'  => 'Work',
                'notes'            => 'Generated expatriate schedule',
                'is_sunday'        => $isSunday ? 1 : 0,
                'is_holiday'       => in_array($dateString, $publicHolidays, true) ? 1 : 0,
                'fortnight_number' => $fortnight,
                'updated_at'       => now(),
                'created_at'       => now(),
            ];

            if ($currentStatus === 'Draft') {
                $record['timesheet_status'] = 'Draft';
            }

            $records[] = $record;
        }

        // Single atomic database operation based on the unique index [employee_id, date]
        AttendanceLog::upsert(
            $records,
            ['employee_id', 'date'], // Columns that hold the unique constraint
            ['hours_worked', 'attendance_type', 'notes', 'is_sunday', 'is_holiday', 'fortnight_number', 'updated_at'] // Columns to update on duplicate
        );
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        $fortnight = $request->fortnight ?? $this->getCurrentFortnight();
        $period = $this->getFortnightPeriod($fortnight);
        
        // Get employees
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->whereHas('attendanceLogs', function ($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight)
                    ->where('hours_worked', '>', 0);
            })
            ->orderBy('employee_number')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        // Get attendance logs
        $attendanceLogs = AttendanceLog::whereIn('employee_id', $employees->pluck('id'))
            ->where('fortnight_number', $fortnight)
            ->get()
            ->groupBy('employee_id')
            ->map(function ($logs) {
                return $logs->keyBy(function ($log) {
                    return $log->date->format('Y-m-d');
                });
            });
        
        // Get summaries
        $summaries = AttendanceSummary::whereIn('employee_id', $employees->pluck('id'))
            ->where('fortnight_number', $fortnight)
            ->get()
            ->keyBy('employee_id');
        
        // Get company name - FIXED: use Company model
        $company = \App\Models\Company::find($companyId);
        $companyName = $company ? $company->name : 'Paragon Tech Limited';
        
        // Generate export
        $export = new TimesheetExport(
            $fortnight, 
            $period, 
            $employees, 
            $attendanceLogs, 
            $summaries, 
            $companyName
        );
        
        $filename = 'timesheet_' . $fortnight . '_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download($export, $filename);
    }
}
