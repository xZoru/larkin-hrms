<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Seed only the Super Admin
        $userData = [
            'name' => 'Super Admin',
            'email' => 'admin@hrms.com',
            'password' => bcrypt('password'),
            'company_id' => null,
            'role' => 'Super Admin'
        ];

        $role = $userData['role'];
        unset($userData['role']);

        $user = User::firstOrCreate(
            ['email' => $userData['email']],
            $userData
        );

        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

        $this->command->info('Super Admin user seeded successfully!');
    }
}