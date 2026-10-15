<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class SickLeavePolicy
{
    private const ANNUAL_ALLOWANCE = 9;

    public function balanceOn(Employee $employee, string|Carbon $value): array
    {
        if (!$employee->joining_date) {
            return [
                'allowance' => self::ANNUAL_ALLOWANCE,
                'used' => 0,
                'available' => 0,
                'eligible_from' => null,
                'is_eligible' => false,
                'resets_on' => null,
            ];
        }

        $joiningDate = Carbon::parse($employee->joining_date)->startOfDay();
        $date = Carbon::parse($value)->startOfDay();
        $eligibleFrom = $joiningDate->copy()->addMonthsNoOverflow(3);
        $yearStart = $this->serviceYearStart($joiningDate, $date);
        $usedDays = $this->usedDaysInWindow($employee, $yearStart, $yearStart->copy()->addYearNoOverflow()->subDay());
        $isEligible = $date->gte($eligibleFrom);

        return [
            'allowance' => self::ANNUAL_ALLOWANCE,
            'used' => $usedDays,
            'available' => $isEligible ? max(0, self::ANNUAL_ALLOWANCE - $usedDays) : 0,
            'eligible_from' => $eligibleFrom,
            'is_eligible' => $isEligible,
            'resets_on' => $yearStart->copy()->addYearNoOverflow(),
        ];
    }

    public function validateDates(
        Employee $employee,
        array $dates,
        ?int $excludeLeaveRequestId = null,
        array $excludeAttendanceLogIds = []
    ): ?string {
        if (!$employee->joining_date) {
            return 'A joining date is required before sick leave can be recorded.';
        }

        if (!$dates) {
            return null;
        }

        $joiningDate = Carbon::parse($employee->joining_date)->startOfDay();
        $eligibleFrom = $joiningDate->copy()->addMonthsNoOverflow(3);
        $requestedByYear = [];

        foreach ($dates as $value) {
            $date = Carbon::parse($value)->startOfDay();

            if ($date->lt($eligibleFrom)) {
                return 'Sick leave is available after the employee completes 3 months of service (' . $eligibleFrom->toDateString() . ').';
            }

            $yearStart = $this->serviceYearStart($joiningDate, $date);

            $key = $yearStart->toDateString();
            $requestedByYear[$key] = ($requestedByYear[$key] ?? 0) + 1;
        }

        foreach ($requestedByYear as $yearStartDate => $requestedDays) {
            $yearStart = Carbon::parse($yearStartDate)->startOfDay();
            $yearEnd = $yearStart->copy()->addYearNoOverflow()->subDay();

            $usedDays = $this->usedDaysInWindow($employee, $yearStart, $yearEnd, $excludeLeaveRequestId, $excludeAttendanceLogIds);

            if ($usedDays + $requestedDays > self::ANNUAL_ALLOWANCE) {
                $available = max(0, self::ANNUAL_ALLOWANCE - $usedDays);
                return "The employee has {$available} sick leave day(s) remaining in this service year. The annual allowance is 9 days and resets on the work anniversary.";
            }
        }

        return null;
    }

    private function serviceYearStart(Carbon $joiningDate, Carbon $date): Carbon
    {
        $yearsSinceJoining = max(0, $date->year - $joiningDate->year);
        $yearStart = $joiningDate->copy()->addYearsNoOverflow($yearsSinceJoining);

        return $yearStart->gt($date) ? $yearStart->subYearNoOverflow() : $yearStart;
    }

    private function usedDaysInWindow(
        Employee $employee,
        Carbon $yearStart,
        Carbon $yearEnd,
        ?int $excludeLeaveRequestId = null,
        array $excludeAttendanceLogIds = []
    ): int {
        $existingRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', 'Sick')
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereDate('start_date', '<=', $yearEnd->toDateString())
            ->whereDate('end_date', '>=', $yearStart->toDateString())
            ->when($excludeLeaveRequestId, fn ($query) => $query->where('id', '!=', $excludeLeaveRequestId))
            ->when($excludeAttendanceLogIds, fn ($query) => $query->where(function ($query) use ($excludeAttendanceLogIds) {
                $query->whereNull('source_attendance_log_id')
                    ->orWhereNotIn('source_attendance_log_id', $excludeAttendanceLogIds);
            }))
            ->get(['start_date', 'end_date']);

        $usedDateKeys = [];
        foreach ($existingRequests as $leaveRequest) {
            $overlapStart = Carbon::parse($leaveRequest->start_date)->max($yearStart);
            $overlapEnd = Carbon::parse($leaveRequest->end_date)->min($yearEnd);

            for ($date = $overlapStart->copy(); $date->lte($overlapEnd); $date->addDay()) {
                $usedDateKeys[$date->toDateString()] = true;
            }
        }

        return count($usedDateKeys);
    }
}
