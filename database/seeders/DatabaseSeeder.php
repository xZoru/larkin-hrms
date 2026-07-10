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
            MemoTemplateSeeder::class,
            EmployeeSeeder::class,        
            LeaveRecordSeeder::class,     
            LoanSeeder::class,            
            NotificationSeeder::class,    
        ]);
    }
}