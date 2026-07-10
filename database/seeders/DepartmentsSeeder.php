<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Company;

class DepartmentsSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();

        $departments = [
            'HR',
            'Finance',
            'IT',
            'Operations',
            'Sales',
            'Marketing',
            'Security',
            'Administration'
        ];

        foreach ($companies as $company) {
            foreach ($departments as $dept) {
                // ✅ Use firstOrCreate to avoid duplicates
                Department::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $dept,
                    ],
                    [
                        'code' => $company->code . '-' . substr($dept, 0, 3),
                        'description' => $dept . ' Department for ' . $company->name,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Departments seeded successfully!');
    }
}