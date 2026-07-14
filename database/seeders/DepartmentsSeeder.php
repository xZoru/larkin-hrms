<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsSeeder extends Seeder
{
    public function run()
    {
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

        foreach ($departments as $dept) {
            // This now creates exactly 1 universal row per department name
            Department::firstOrCreate(
                [
                    'name' => $dept,
                    'company_id' => null, // null to make it universal
                ],
                [
                    'code' => 'GLOBAL-' . substr($dept, 0, 3),
                    'description' => $dept . ' Universal System Department',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Universal Departments seeded successfully!');
    }
}
