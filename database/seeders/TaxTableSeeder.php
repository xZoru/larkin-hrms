<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxTable;
use App\Models\Company;

class TaxTableSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();

        // ============================================================
        // RESIDENT TAX BRACKETS - FORTNIGHTLY (Applied to ALL employees)
        // Based on PNG Internal Revenue Commission thresholds
        // Formula: Tax = (Income × Rate%) - Offset
        // ============================================================
        $taxBrackets = [
            ['min' => 0.00,    'max' => 769.23,  'rate' => 0,  'offset' => 0.00,    'name' => '0% (Tax Free)'],
            ['min' => 769.23,  'max' => 1269.23, 'rate' => 30, 'offset' => 230.77,  'name' => '30%'],
            ['min' => 1269.23, 'max' => 2692.31, 'rate' => 35, 'offset' => 294.23,  'name' => '35%'],
            ['min' => 2692.31, 'max' => 9615.38, 'rate' => 40, 'offset' => 428.84,  'name' => '40%'],
            ['min' => 9615.38, 'max' => null,    'rate' => 42, 'offset' => 621.15,  'name' => '42% (Top Rate)'],
        ];

        foreach ($companies as $company) {
            foreach ($taxBrackets as $bracket) {
                TaxTable::create([
                    'company_id' => $company->id,
                    'name' => 'Resident ' . $bracket['name'],
                    'employee_type' => 'National', // ✅ All employees use National rates
                    'min_amount' => $bracket['min'],
                    'max_amount' => $bracket['max'],
                    'tax_rate' => $bracket['rate'],
                    'fixed_tax' => $bracket['offset'],
                    'effective_date' => '2024-01-01',
                    'end_date' => null,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ Tax tables seeded successfully!');
        $this->command->info('📊 All employees use Resident tax rates (National rates)');
        $this->command->info('');
        $this->command->info('📊 Formula: Tax = (Income × Rate%) - Offset');
        $this->command->info('');
        $this->command->info('📈 Rates: 0%, 30%, 35%, 40%, 42%');
        $this->command->info('📈 Offsets: 0, 230.77, 294.23, 428.84, 621.15');
    }
}