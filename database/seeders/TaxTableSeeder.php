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
        // NATIONAL (RESIDENT) TAX BRACKETS - FORTNIGHTLY
        // Based on PNG Internal Revenue Commission thresholds
        // Formula: Tax = (Income × Rate%) - Offset
        // ============================================================
        $nationalTaxBrackets = [
            ['min' => 0.00,    'max' => 769.23,  'rate' => 0,  'offset' => 0.00,    'name' => 'National 0% (Tax Free)'],
            ['min' => 769.23,  'max' => 1269.23, 'rate' => 30, 'offset' => 230.77,  'name' => 'National 30%'],
            ['min' => 1269.23, 'max' => 2692.31, 'rate' => 35, 'offset' => 294.23,  'name' => 'National 35%'],
            ['min' => 2692.31, 'max' => 9615.38, 'rate' => 40, 'offset' => 428.84,  'name' => 'National 40%'],
            ['min' => 9615.38, 'max' => null,    'rate' => 42, 'offset' => 621.15,  'name' => 'National 42% (Top Rate)'],
        ];

        // ============================================================
        // EXPATRIATE (NON-RESIDENT) TAX BRACKETS - FORTNIGHTLY
        // ✅ CORRECTED OFFSETS (Gemini verified)
        // Formula: Tax = (Income × Rate%) - Offset
        // ============================================================
        $expatTaxBrackets = [
            ['min' => 0.00,    'max' => 769.23,  'rate' => 22, 'offset' => 0.0000,  'name' => 'Expatriate 22%'],
            ['min' => 769.23,  'max' => 1269.23, 'rate' => 30, 'offset' => 61.5384, 'name' => 'Expatriate 30%'],
            ['min' => 1269.23, 'max' => 2692.31, 'rate' => 35, 'offset' => 125.0000, 'name' => 'Expatriate 35%'],
            ['min' => 2692.31, 'max' => 9615.38, 'rate' => 40, 'offset' => 259.6155, 'name' => 'Expatriate 40%'],
            ['min' => 9615.38, 'max' => null,    'rate' => 42, 'offset' => 451.9231, 'name' => 'Expatriate 42% (Top Rate)'],
        ];

        foreach ($companies as $company) {
            foreach ($nationalTaxBrackets as $bracket) {
                TaxTable::create([
                    'company_id' => $company->id,
                    'name' => $bracket['name'],
                    'employee_type' => 'National',
                    'min_amount' => $bracket['min'],
                    'max_amount' => $bracket['max'],
                    'tax_rate' => $bracket['rate'],
                    'fixed_tax' => $bracket['offset'],
                    'effective_date' => '2024-01-01',
                    'end_date' => null,
                    'is_active' => true,
                ]);
            }

            foreach ($expatTaxBrackets as $bracket) {
                TaxTable::create([
                    'company_id' => $company->id,
                    'name' => $bracket['name'],
                    'employee_type' => 'Expatriate',
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

        $this->command->info('✅ PNG Tax tables seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 National: (Income × Rate%) - Offset');
        $this->command->info('📊 Expatriate: (Income × Rate%) - Offset (✅ Corrected)');
        $this->command->info('');
        $this->command->info('🔢 Expatriate offsets verified at all boundaries:');
        $this->command->info('   22% → 0.0000');
        $this->command->info('   30% → 61.5384  ✅ (Boundary: 769.23 → Tax = 169.23)');
        $this->command->info('   35% → 125.0000 ✅ (Boundary: 1269.23 → Tax = 319.23)');
        $this->command->info('   40% → 259.6155 ✅ (Boundary: 2692.31 → Tax = 817.31)');
        $this->command->info('   42% → 451.9231 ✅ (Boundary: 9615.38 → Tax = 3586.54)');
    }
}