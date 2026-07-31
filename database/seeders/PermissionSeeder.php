<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-departments',
            'view-company-bank-details',
            'view-tax-tables',
            'view-holidays',
            'view-employees',
            'view-attendance',
            'save-attendance',
            'lock-attendance',
            'unlock-attendance',
            'view-payroll',
            'view-loans',
            'view-reports',
            'view-leave',
            'view-backups',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }
}
