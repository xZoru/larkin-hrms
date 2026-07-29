<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\BankAccount;
use App\Models\Loan;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure Paragon Tech Limited exists
        $company = Company::firstOrCreate(
            ['name' => 'Paragon Tech Limited'],
            ['status' => 'Active']
        );

        // 2. Define Departments
        $adminDept = Department::firstOrCreate(['name' => 'Administrations', 'company_id' => $company->id]);
        $accountsDept = Department::firstOrCreate(['name' => 'Accounts', 'company_id' => $company->id]);

        // ==========================================
        // EMPLOYEE 1: Wilxon Mar Baja Andres (PAR-0001)
        // ==========================================
        $wilxon = Employee::updateOrCreate(
            ['employee_number' => 'PAR-0001'],
            [
                'company_id'      => $company->id,
                'department_id'   => $adminDept->id ?? null,
                'full_name'       => 'Wilxon Mar Baja Andres',
                'gender'          => 'Male',
                'date_of_birth'   => '1998-11-11',
                'marital_status'  => 'Single',
                'joining_date'    => '1998-10-05',
                'employee_type'   => 'Expatriate',
                'position_name'   => 'Corporate Services Manager',
                'fortnight_hours' => 84,
                'hourly_rate'     => 16.48,
                'monthly_salary'  => 3000.00,
                'base_salary'     => 1384.62,
                'allowance'       => 300.00,
                'payment_method'  => 'Bank Transfer',
                'status'          => 'Active',
            ]
        );

        // Wilxon Bank Account
        BankAccount::updateOrCreate(
            ['employee_id' => $wilxon->id],
            [
                'account_name'   => 'WILXON MAR BAJA ANDRES',
                'bank_name'      => 'Credit Bank PNG',
                'account_number' => '10005292',
                'bsb'            => '078-001',
                'is_preferred'   => true,
            ]
        );

        // Wilxon Loan
        Loan::updateOrCreate(
            ['employee_id' => $wilxon->id, 'amount' => 500.00],
            [
                'company_id'        => $company->id,
                'type'              => 'Loan',
                'remaining_balance' => 0.00,
                'status'            => 'Released',
                'created_at'        => '2026-07-23',
            ]
        );


        // ==========================================
        // EMPLOYEE 2: Karl David Tavas Valmonte (PAR-0002)
        // ==========================================
        $karl = Employee::updateOrCreate(
            ['employee_number' => 'PAR-0002'],
            [
                'company_id'      => $company->id,
                'department_id'   => null, // N/A on screenshot
                'full_name'       => 'Karl David Tavas Valmonte',
                'gender'          => 'Male',
                'date_of_birth'   => '1998-11-11',
                'marital_status'  => 'Single',
                'joining_date'    => '2024-05-28',
                'employee_type'   => 'Expatriate',
                'position_name'   => 'IT Manager',
                'fortnight_hours' => 84,
                'hourly_rate'     => 16.48,
                'monthly_salary'  => 3000.00,
                'base_salary'     => 1384.62,
                'allowance'       => 300.00,
                'payment_method'  => 'Bank Transfer',
                'status'          => 'Active',
            ]
        );

        // Karl Bank Account
        BankAccount::updateOrCreate(
            ['employee_id' => $karl->id],
            [
                'account_name'   => 'KARL DAVID TAVAS VALMONTE',
                'bank_name'      => 'Credit Bank PNG',
                'account_number' => '10005294',
                'bsb'            => '078-001',
                'is_preferred'   => true,
            ]
        );

        // Karl Loan
        Loan::updateOrCreate(
            ['employee_id' => $karl->id, 'amount' => 500.00],
            [
                'company_id'        => $company->id,
                'type'              => 'Loan',
                'remaining_balance' => 0.00,
                'status'            => 'Released',
                'created_at'        => '2026-07-23',
            ]
        );


        // ==========================================
        // EMPLOYEE 3: Joyce Ann Tavara Ugay (PAR-0003)
        // ==========================================
        $joyce = Employee::updateOrCreate(
            ['employee_number' => 'PAR-0003'],
            [
                'company_id'      => $company->id,
                'department_id'   => $accountsDept->id ?? null,
                'full_name'       => 'Joyce Ann Tavara Ugay',
                'gender'          => 'Female',
                'date_of_birth'   => '1998-07-04',
                'marital_status'  => 'Single',
                'joining_date'    => '2025-06-08',
                'employee_type'   => 'Expatriate',
                'position_name'   => 'Practise Manager',
                'fortnight_hours' => 84,
                'hourly_rate'     => 13.74,
                'monthly_salary'  => 2500.00,
                'base_salary'     => 1153.85,
                'allowance'       => 300.00,
                'payment_method'  => 'Bank Transfer',
                'status'          => 'Active',
            ]
        );

        // Joyce Bank Account
        BankAccount::updateOrCreate(
            ['employee_id' => $joyce->id],
            [
                'account_name'   => 'JOYCE ANN TAVARA UGAY',
                'bank_name'      => 'Credit Bank PNG',
                'account_number' => '10013986',
                'bsb'            => '078-001',
                'is_preferred'   => true,
            ]
        );

        $this->command->info('3 Expatriate employees, bank accounts, and loans seeded successfully!');
    }
}