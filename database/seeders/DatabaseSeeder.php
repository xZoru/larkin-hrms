<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CompaniesSeeder::class,
            UsersSeeder::class,
            DepartmentsSeeder::class,
            TaxTableSeeder::class,
            EmployeeSeeder::class,        
            NotificationSeeder::class,
            PermissionSeeder::class,
        ]);
    }
}