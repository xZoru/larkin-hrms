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
        $company = Company::where('code', 'LKE-POM')->first();
        
        if (!$company) {
            $this->command->error('Company LKE-POM not found. Please run CompaniesSeeder first.');
            return;
        }

        $department = Department::where('company_id', $company->id)->first();

        $employees = [
            [
                'employee_number' => 'LKP-123',
                'full_name' => 'Lani Nuani',
                'position' => 'Painter',
                'gender' => 'Female',
                'employee_type' => 'National',
                'date_of_birth' => '1990-05-15',
                'joining_date' => '2020-01-15',
                'hourly_rate' => 6.50,
                'payment_method' => 'Bank Transfer',
                'status' => 'Active',
                'nasfund_number' => 'NF123456',
                'nasfund_dependents' => 2,
                'nasfund_allocation_percentage' => 100,
                'base_salary' => 6.50 * 84,
            ],
            [
                'employee_number' => 'LKP-242',
                'full_name' => 'Ura Bane',
                'position' => 'Painter',
                'gender' => 'Male',
                'employee_type' => 'National',
                'date_of_birth' => '1988-08-22',
                'joining_date' => '2019-06-10',
                'hourly_rate' => 6.00,
                'payment_method' => 'Bank Transfer',
                'status' => 'Active',
                'nasfund_number' => 'NF123457',
                'nasfund_dependents' => 3,
                'nasfund_allocation_percentage' => 100,
                'base_salary' => 6.00 * 84,
            ],
            [
                'employee_number' => 'LKP-245',
                'full_name' => 'Jeffery Sturgess',
                'position' => 'Glass Fitter',
                'gender' => 'Male',
                'employee_type' => 'National',
                'date_of_birth' => '1985-12-01',
                'joining_date' => '2018-03-20',
                'hourly_rate' => 7.00,
                'payment_method' => 'Bank Transfer',
                'status' => 'Active',
                'nasfund_number' => 'NF123458',
                'nasfund_dependents' => 1,
                'nasfund_allocation_percentage' => 100,
                'base_salary' => 7.00 * 84,
            ],
            [
                'employee_number' => 'LKP-262',
                'full_name' => 'Kevin Hauhi',
                'position' => 'Carpenter',
                'gender' => 'Male',
                'employee_type' => 'National',
                'date_of_birth' => '1992-03-10',
                'joining_date' => '2021-09-01',
                'hourly_rate' => 5.50,
                'payment_method' => 'Bank Transfer',
                'status' => 'Active',
                'nasfund_number' => 'NF123459',
                'nasfund_dependents' => 2,
                'nasfund_allocation_percentage' => 100,
                'base_salary' => 5.50 * 84,
            ],
            [
                'employee_number' => 'LKP-265',
                'full_name' => 'Elaine Benoma',
                'position' => 'Admin Clerk',
                'gender' => 'Female',
                'employee_type' => 'National',
                'date_of_birth' => '1995-07-25',
                'joining_date' => '2022-02-14',
                'hourly_rate' => 6.00,
                'payment_method' => 'Bank Transfer',
                'status' => 'Active',
                'nasfund_number' => 'NF123460',
                'nasfund_dependents' => 0,
                'nasfund_allocation_percentage' => 100,
                'base_salary' => 6.00 * 84,
            ],
            [
                'employee_number' => 'LKP-282',
                'full_name' => 'Bernard Gabi',
                'position' => 'Driver',
                'gender' => 'Male',
                'employee_type' => 'National',
                'date_of_birth' => '1987-11-18',
                'joining_date' => '2019-10-01',
                'hourly_rate' => 5.50,
                'payment_method' => 'Bank Transfer',
                'status' => 'Active',
                'nasfund_number' => 'NF123461',
                'nasfund_dependents' => 3,
                'nasfund_allocation_percentage' => 100,
                'base_salary' => 5.50 * 84,
            ],
            [
                'employee_number' => 'LKP-285',
                'full_name' => 'Foncy Levo',
                'position' => 'Carpenter',
                'gender' => 'Male',
                'employee_type' => 'National',
                'date_of_birth' => '1991-09-05',
                'joining_date' => '2020-07-15',
                'hourly_rate' => 6.50,
                'payment_method' => 'Bank Transfer',
                'status' => 'Active',
                'nasfund_number' => 'NF123462',
                'nasfund_dependents' => 2,
                'nasfund_allocation_percentage' => 100,
                'base_salary' => 6.50 * 84,
            ],
        ];

        foreach ($employees as $employeeData) {
            $employee = Employee::create(array_merge($employeeData, [
                'company_id' => $company->id,
                'department_id' => $department?->id,
            ]));

            // Create bank account
            BankAccount::create([
                'employee_id' => $employee->id,
                'account_name' => $employee->full_name,
                'account_number' => 'ACC' . rand(10000000, 99999999),
                'bank_name' => 'BSP',
                'bsb_code' => '123' . rand(100, 999),
                'is_preferred' => true,
                'priority' => 1,
                'is_active' => true
            ]);
        }

        $this->command->info('Sample employees seeded successfully!');
    }
}