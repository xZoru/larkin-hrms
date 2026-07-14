<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // SOW: Multi-user access with roles
        $permissions = [
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view payroll',
            'process payroll',
            'approve payroll',
            'view reports',
            'generate reports',
            'manage leaves',
            'approve leaves',
            'manage loans',
            'manage discipline',
            'manage settings',
            'view attendance',
            
            // ============ LOAN PERMISSIONS (ADDED) ============
            'view loans',
            'create loans',
            'edit loans',
            'delete loans',
            'approve loans',
            'release loans',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // SOW: Secure login system with different user roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // HR Manager - Add loan permissions
        $hrManager = Role::firstOrCreate(['name' => 'HR Manager', 'guard_name' => 'web']);
        $hrManager->givePermissionTo([
            'view employees',
            'create employees',
            'edit employees',
            'view reports',
            'generate reports',
            'manage leaves',
            'approve leaves',
            'manage loans',
            'manage discipline',
            // Add loan permissions
            'view loans',
            'create loans',
            'edit loans',
            'approve loans',
            'release loans',
        ]);

        $payrollOfficer = Role::firstOrCreate(['name' => 'Payroll Officer', 'guard_name' => 'web']);
        $payrollOfficer->givePermissionTo([
            'view employees',
            'view payroll',
            'process payroll',
            'view reports',
            'generate reports',
            // Add view loans permission
            'view loans',
        ]);

        $departmentManager = Role::firstOrCreate(['name' => 'Department Manager', 'guard_name' => 'web']);
        $departmentManager->givePermissionTo([
            'view employees',
            'approve leaves',
        ]);

        $employee = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employee->givePermissionTo([
            'view employees',
            'view loans', // Employees can view their own loans
        ]);


        $this->command->info('Roles and permissions seeded successfully!');
    }
}