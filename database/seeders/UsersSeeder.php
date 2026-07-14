<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $company = Company::first();

        // SOW: Secure login system with multi-user access
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@hrms.com',
                'password' => bcrypt('password'),
                'company_id' => null,
                'role' => 'Super Admin'
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr@hrms.com',
                'password' => bcrypt('password'),
                'company_id' => $company?->id,
                'role' => 'HR Manager'
            ],
            [
                'name' => 'Payroll Officer',
                'email' => 'payroll@hrms.com',
                'password' => bcrypt('password'),
                'company_id' => $company?->id,
                'role' => 'Payroll Officer'
            ],
            [
                'name' => 'Department Manager',
                'email' => 'manager@hrms.com',
                'password' => bcrypt('password'),
                'company_id' => $company?->id,
                'role' => 'Department Manager'
            ],
            [
                'name' => 'Employee User',
                'email' => 'employee@hrms.com',
                'password' => bcrypt('password'),
                'company_id' => $company?->id,
                'role' => 'Employee'
            ]
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            $user = User::create($userData);
            $user->assignRole($role);
        }

        $this->command->info('Users seeded successfully!');
    }
}