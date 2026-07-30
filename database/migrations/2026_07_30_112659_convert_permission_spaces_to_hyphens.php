<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Reset cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Fetch all permissions with spaces
        $spacedPermissions = Permission::where('name', 'LIKE', '% %')->get();

        foreach ($spacedPermissions as $oldPermission) {
            $newName = str_replace(' ', '-', $oldPermission->name);

            // Find if the hyphenated version already exists
            $existingPermission = Permission::where('name', $newName)
                ->where('guard_name', $oldPermission->guard_name)
                ->first();

            if ($existingPermission) {
                // Re-assign role relationships to the target permission
                DB::table('role_has_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->update(['permission_id' => $existingPermission->id]);

                // Re-assign user direct relationships to the target permission
                DB::table('model_has_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->update(['permission_id' => $existingPermission->id]);

                // Delete the redundant space-based permission record
                $oldPermission->delete();
            } else {
                // If no duplicate exists, simply rename it
                $oldPermission->update(['name' => $newName]);
            }
        }
    }

    public function down(): void
    {
        // Revert back to spaces if needed
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::all()->each(function ($permission) {
            $permission->update([
                'name' => str_replace('-', ' ', $permission->name)
            ]);
        });
    }
};