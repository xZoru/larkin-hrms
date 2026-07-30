<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // All system permissions using hyphens
        $permissions = [
            'view-companies',
            'create-companies',
            'edit-companies',
            'delete-companies',
            'view-employees',
            'create-employees',
            'edit-employees',
            'delete-employees',
            'view-payroll',
            'process-payroll',
            'approve-payroll',
            'view-reports',
            'generate-reports',
            'manage-leaves',
            'approve-leaves',
            'manage-loans',
            'manage-discipline',
            'manage-settings',
            'view-attendance',
            'view-loans',
            'create-loans',
            'edit-loans',
            'delete-loans',
            'approve-loans',
            'release-loans',
        ];

        // Seed permissions into DB
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission, 
                'guard_name' => 'web'
            ]);
        }

        // Create Super Admin role and grant all permissions
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin', 
            'guard_name' => 'web'
        ]);
        
        $superAdmin->syncPermissions(Permission::all());

        $this->command->info('Super Admin role and permissions seeded successfully!');
    }
}