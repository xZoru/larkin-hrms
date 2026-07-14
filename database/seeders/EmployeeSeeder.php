<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\BankAccount;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Sample employees seeded successfully!');
    }
}