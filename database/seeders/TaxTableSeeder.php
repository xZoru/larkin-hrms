<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxTable;

class TaxTableSeeder extends Seeder
{
    public function run()
    {
        // ============================================================
        // PNG TAX BRACKETS - FORTNIGHTLY
        // Based on PNG Internal Revenue Commission thresholds
        // Formula: Tax = (Income - Threshold) × Rate%
        // Threshold (fixed_tax) = 769.00 for ALL brackets
        // ============================================================

        // First, deactivate all existing tax tables
        TaxTable::where('employee_type', 'National')->update(['is_active' => false]);

        $taxBrackets = [
            [
                'min' => 0.00,
                'max' => 769.23,
                'rate' => 0,
                'threshold' => 769.00,
                'name' => 'PNG Tax 0% (Tax Free)'
            ],
            [
                'min' => 769.23,
                'max' => 1269.23,
                'rate' => 30,
                'threshold' => 769.00,
                'name' => 'PNG Tax 30%'
            ],
            [
                'min' => 1269.23,
                'max' => 2692.31,
                'rate' => 35,
                'threshold' => 769.00,
                'name' => 'PNG Tax 35%'
            ],
            [
                'min' => 2692.31,
                'max' => 9615.38,
                'rate' => 40,
                'threshold' => 769.00,
                'name' => 'PNG Tax 40%'
            ],
            [
                'min' => 9615.38,
                'max' => null,
                'rate' => 42,
                'threshold' => 769.00,
                'name' => 'PNG Tax 42% (Top Rate)'
            ],
        ];

        foreach ($taxBrackets as $bracket) {
            TaxTable::updateOrCreate(
                [
                    'name' => $bracket['name'],
                    'company_id' => null, // Global tax tables
                    'employee_type' => 'National',
                    'min_amount' => $bracket['min'],
                ],
                [
                    'max_amount' => $bracket['max'],
                    'tax_rate' => $bracket['rate'],
                    'fixed_tax' => $bracket['threshold'], // Tax-Free Threshold
                    'effective_date' => '2024-01-01',
                    'end_date' => null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ PNG Tax tables seeded successfully with 769.00 threshold!');
    }
}