<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Check all employees for expiring documents and create notifications
     */
    public function checkExpiries($companyId = null)
    {
        $query = Employee::with(['company']);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $employees = $query->get();
        $notificationsCreated = 0;
        
        foreach ($employees as $employee) {
            $notificationsCreated += $this->checkEmployeeExpiries($employee);
        }
        
        return $notificationsCreated;
    }

    /**
     * Check expiries for a single employee
     */
    public function checkEmployeeExpiries($employee)
    {
        $created = 0;
        
        // Check Passport (Expatriates only)
        if ($employee->isExpatriate() && $employee->passport_expiry) {
            $created += $this->createNotificationIfExpiring(
                $employee,
                'passport',
                $employee->passport_expiry,
                'Passport Expiring Soon',
                'Passport for ' . $employee->full_name . ' expires on ' . Carbon::parse($employee->passport_expiry)->format('d M Y')
            );
        }
        
        // Check Visa (Expatriates only)
        if ($employee->isExpatriate() && $employee->visa_expiry) {
            $created += $this->createNotificationIfExpiring(
                $employee,
                'visa',
                $employee->visa_expiry,
                'Visa Expiring Soon',
                'Visa for ' . $employee->full_name . ' expires on ' . Carbon::parse($employee->visa_expiry)->format('d M Y')
            );
        }
        
        // Check Work Permit (Expatriates only)
        if ($employee->isExpatriate() && $employee->work_permit_expiry) {
            $created += $this->createNotificationIfExpiring(
                $employee,
                'work_permit',
                $employee->work_permit_expiry,
                'Work Permit Expiring Soon',
                'Work Permit for ' . $employee->full_name . ' expires on ' . Carbon::parse($employee->work_permit_expiry)->format('d M Y')
            );
        }
        
        return $created;
    }

    /**
     * Create notification if document is expiring within 90 days
     */
    private function createNotificationIfExpiring($employee, $type, $expiryDate, $title, $message)
    {
        $days = Carbon::parse($expiryDate)->diffInDays(Carbon::now());
        
        // Only notify if within 90 days and not already expired
        if ($days > 90 || $days < 0) {
            // Delete any existing notifications for this document if outside range
            Notification::where('employee_id', $employee->id)
                ->where('type', $type)
                ->delete();
            return 0;
        }

        // Check if notification already exists
        $existing = Notification::where('employee_id', $employee->id)
            ->where('type', $type)
            ->where('is_read', false)
            ->first();

        if ($existing) {
            // Update if days have changed
            if ($existing->days_before != $days) {
                $existing->update([
                    'expiry_date' => $expiryDate,
                    'days_before' => $days,
                    'message' => $message . ' (' . $days . ' days remaining)',
                ]);
            }
            return 0;
        }

        // Create new notification
        Notification::create([
            'employee_id' => $employee->id,
            'title' => $title,
            'message' => $message . ' (' . $days . ' days remaining)',
            'type' => $type,
            'expiry_date' => $expiryDate,
            'days_before' => $days,
            'is_read' => false,
            'link' => route('employees.show', $employee->id),
            'data' => [
                'employee_name' => $employee->full_name,
                'employee_number' => $employee->employee_number,
                'document_type' => $type,
            ]
        ]);

        return 1;
    }

    /**
     * Get all active notifications for a user's company
     */
    public function getActiveNotifications($companyId)
    {
        $employeeIds = Employee::where('company_id', $companyId)->pluck('id');
        
        return Notification::whereIn('employee_id', $employeeIds)
            ->where('is_read', false)
            ->with(['employee'])
            ->orderBy('days_before', 'asc')
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get count of active notifications for a user's company
     */
    public function getNotificationCount($companyId)
    {
        $employeeIds = Employee::where('company_id', $companyId)->pluck('id');
        
        return Notification::whereIn('employee_id', $employeeIds)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get urgent notifications (expiring within 30 days)
     */
    public function getUrgentNotifications($companyId)
    {
        $employeeIds = Employee::where('company_id', $companyId)->pluck('id');
        
        return Notification::whereIn('employee_id', $employeeIds)
            ->where('is_read', false)
            ->where('days_before', '<=', 30)
            ->where('days_before', '>=', 0)
            ->with(['employee'])
            ->orderBy('days_before', 'asc')
            ->get();
    }
}