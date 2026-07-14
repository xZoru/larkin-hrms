<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxTable;

class TaxTableSeeder extends Seeder
{
    public function run()
    {
        // ============================================================
        // RESIDENT TAX BRACKETS - FORTNIGHTLY (Universal System Rates)
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

        foreach ($taxBrackets as $bracket) {
            //  Inserts exactly 5 global rows total with company_id set to null
            TaxTable::firstOrCreate(
                [
                    'name' => 'Resident ' . $bracket['name'],
                    'company_id' => null, //  Global identifier
                    'employee_type' => 'National',
                ],
                [
                    'min_amount' => $bracket['min'],
                    'max_amount' => $bracket['max'],
                    'tax_rate' => $bracket['rate'],
                    'fixed_tax' => $bracket['offset'],
                    'effective_date' => '2024-01-01',
                    'end_date' => null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Universal Tax tables seeded successfully!');
    }
}
