<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Employee;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::where('employee_type', 'Expatriate')->get();

        foreach ($employees as $employee) {
            // SOW: Expiry Notifications - 90 days before
            if ($employee->passport_expiry && $employee->passport_expiry <= now()->addDays(90)) {
                Notification::create([
                    'employee_id' => $employee->id,
                    'title' => 'Passport Expiring Soon',
                    'message' => 'Your passport ({{passport_number}}) will expire on {{passport_expiry}}. Please renew immediately.',
                    'type' => 'Passport Expiry',
                    'expiry_date' => $employee->passport_expiry,
                    'days_before' => 90,
                    'is_read' => false,
                    'link' => route('employees.show', $employee),
                    'data' => [
                        'passport_number' => $employee->passport_number,
                        'passport_expiry' => $employee->passport_expiry->format('Y-m-d')
                    ]
                ]);
            }

            if ($employee->visa_expiry && $employee->visa_expiry <= now()->addDays(90)) {
                Notification::create([
                    'employee_id' => $employee->id,
                    'title' => 'Visa Expiring Soon',
                    'message' => 'Your visa ({{visa_number}}) will expire on {{visa_expiry}}. Please renew immediately.',
                    'type' => 'Visa Expiry',
                    'expiry_date' => $employee->visa_expiry,
                    'days_before' => 90,
                    'is_read' => false,
                    'link' => route('employees.show', $employee),
                    'data' => [
                        'visa_number' => $employee->visa_number,
                        'visa_expiry' => $employee->visa_expiry->format('Y-m-d')
                    ]
                ]);
            }

            if ($employee->work_permit_expiry && $employee->work_permit_expiry <= now()->addDays(90)) {
                Notification::create([
                    'employee_id' => $employee->id,
                    'title' => 'Work Permit Expiring Soon',
                    'message' => 'Your work permit ({{work_permit_number}}) will expire on {{work_permit_expiry}}. Please renew immediately.',
                    'type' => 'Work Permit Expiry',
                    'expiry_date' => $employee->work_permit_expiry,
                    'days_before' => 90,
                    'is_read' => false,
                    'link' => route('employees.show', $employee),
                    'data' => [
                        'work_permit_number' => $employee->work_permit_number,
                        'work_permit_expiry' => $employee->work_permit_expiry->format('Y-m-d')
                    ]
                ]);
            }
        }

        $this->command->info('Notifications seeded successfully!');
    }
}