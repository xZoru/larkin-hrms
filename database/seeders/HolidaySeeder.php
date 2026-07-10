<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use App\Models\Company;

class HolidaySeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();

        $holidays = [
            ['name' => 'New Year\'s Day', 'date' => '2026-01-01'],
            ['name' => 'Good Friday', 'date' => '2026-04-10'],
            ['name' => 'Easter Monday', 'date' => '2026-04-13'],
            ['name' => 'Queen\'s Birthday', 'date' => '2026-06-08'],
            ['name' => 'Remembrance Day', 'date' => '2026-07-20'],
            ['name' => 'Independence Day', 'date' => '2026-09-16'],
            ['name' => 'Christmas Day', 'date' => '2026-12-25'],
            ['name' => 'Boxing Day', 'date' => '2026-12-26'],
        ];

        foreach ($companies as $company) {
            foreach ($holidays as $holiday) {
                Holiday::create([
                    'company_id' => $company->id,
                    'name' => $holiday['name'],
                    'date' => $holiday['date'],
                    'description' => 'Public holiday',
                    'is_recurring' => true,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ PNG Public holidays seeded successfully!');
    }
}