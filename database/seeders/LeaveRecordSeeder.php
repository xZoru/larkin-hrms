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
            $monthsEmployed = $employee->joining_date ? $employee->joining_date->diffInMonths(now()) : 12;
            $accruedDays = min(floor($monthsEmployed / 1.5), 9);

            LeaveRecord::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'year' => now()->year,
                ],
                [
                    'leave_balance' => $accruedDays,
                    'leave_taken' => rand(0, 5),
                    'leave_accrued' => $accruedDays,
                    'last_accrual_date' => now()
                ]
            );
        }

        $this->command->info('Leave records seeded successfully!');
    }
}