<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveRecord;
use App\Models\Employee;

class LeaveRecordSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            // SOW: Annual Leave Accrual - 1 leave day earned every 1.5 months, max 9 days/year
            $monthsEmployed = $employee->joining_date->diffInMonths(now());
            $accruedDays = min(floor($monthsEmployed / 1.5), 9);

            LeaveRecord::create([
                'employee_id' => $employee->id,
                'year' => now()->year,
                'leave_balance' => $accruedDays,
                'leave_taken' => rand(0, 5),
                'leave_accrued' => $accruedDays,
                'last_accrual_date' => now()
            ]);
        }

        $this->command->info('Leave records seeded successfully!');
    }
}