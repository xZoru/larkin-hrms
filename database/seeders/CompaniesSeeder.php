<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompaniesSeeder extends Seeder
{
    public function run()
    {
        // SOW: Multi-Company Support - All 10 companies
        $companies = [
            [
                'code' => 'LKE-POM',
                'name' => 'Larkin Enterprises Ltd - Port Moresby',
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4567',
                'email' => 'info@larkinpom.com',
                'regular_hours' => 84,
                'is_active' => true
            ],
            [
                'code' => 'LKE-LAE',
                'name' => 'Larkin Enterprises Ltd - Lae',
                'address' => 'Lae, Papua New Guinea',
                'phone' => '+675 123 4568',
                'email' => 'info@larkinlae.com',
                'regular_hours' => 84,
                'is_active' => true
            ],
            [
                'code' => 'ADF',
                'name' => 'Ad Focus',
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4569',
                'email' => 'info@adfocus.com',
                'regular_hours' => 84,
                'is_active' => true
            ],
            [
                'code' => 'YJS-POM',
                'name' => 'Yellow Jacket Security Ltd - Port Moresby',
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4570',
                'email' => 'info@yjs-pom.com',
                'regular_hours' => 144, // SOW: YJ Security - 144hrs
                'is_active' => true
            ],
            [
                'code' => 'YJS-LAE',
                'name' => 'Yellow Jacket Security Ltd - Lae',
                'address' => 'Lae, Papua New Guinea',
                'phone' => '+675 123 4571',
                'email' => 'info@yjs-lae.com',
                'regular_hours' => 144, // SOW: YJ Security - 144hrs
                'is_active' => true
            ],
            [
                'code' => 'WAVE',
                'name' => 'Wave Restaurant',
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4572',
                'email' => 'info@wave.com',
                'regular_hours' => 84,
                'is_active' => true
            ],
            [
                'code' => 'CARO',
                'name' => "Caroline's Diner",
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4573',
                'email' => 'info@carolines.com',
                'regular_hours' => 84,
                'is_active' => true
            ],
            [
                'code' => 'PARA',
                'name' => 'Paragon Tech Limited',
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4574',
                'email' => 'info@paragontech.com',
                'regular_hours' => 84,
                'is_active' => true
            ],
            [
                'code' => 'LEF',
                'name' => 'Le Ferge Investments',
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4575',
                'email' => 'info@leferge.com',
                'regular_hours' => 84,
                'is_active' => true
            ],
            [
                'code' => 'HIVE',
                'name' => 'Hive',
                'address' => 'Port Moresby, Papua New Guinea',
                'phone' => '+675 123 4576',
                'email' => 'info@hive.com',
                'regular_hours' => 84,
                'is_active' => true
            ]
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }

        $this->command->info('Companies seeded successfully!');
    }
}