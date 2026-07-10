<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Loan;
use App\Models\Employee;
use Carbon\Carbon;

class LoanSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::take(3)->get();

        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        $loanTypes = ['Cash Advance', 'Loan', 'Company Deductions'];
        $statuses = ['Pending', 'Approved', 'Released', 'On-Hold', 'Rejected', 'Completed'];

        foreach ($employees as $index => $employee) {
            $amount = fake()->randomFloat(2, 100, 5000);
            $installmentCount = fake()->numberBetween(2, 12);
            $deductionPerCutoff = $amount / $installmentCount;
            
            $status = $statuses[array_rand($statuses)];
            
            $loan = Loan::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'loan_type' => $loanTypes[array_rand($loanTypes)],
                'amount' => $amount,
                'deduction_per_cutoff' => $deductionPerCutoff,
                'remaining_balance' => $status === 'Completed' ? 0 : $amount,
                'total_paid' => $status === 'Completed' ? $amount : 0,
                'installment_count' => $installmentCount,
                'payments_made' => $status === 'Completed' ? $installmentCount : 0,
                'reason' => fake()->sentence(5),
                'status' => $status,
                'created_by' => 1, // Admin user
            ]);

            if (in_array($status, ['Approved', 'Released', 'On-Hold', 'Completed'])) {
                $loan->update([
                    'approved_by' => 1,
                    'approved_date' => Carbon::now()->subDays(fake()->numberBetween(1, 30)),
                ]);
            }

            if (in_array($status, ['Released', 'Completed'])) {
                $loan->update([
                    'released_by' => 1,
                    'released_date' => Carbon::now()->subDays(fake()->numberBetween(1, 15)),
                ]);
            }

            // Add payments
            if (in_array($status, ['Released', 'Completed'])) {
                $paymentsMade = $status === 'Completed' ? $installmentCount : fake()->numberBetween(1, max(1, $installmentCount - 1));
                $paidAmount = 0;
                
                for ($i = 1; $i <= $paymentsMade; $i++) {
                    $paymentAmount = $i === $paymentsMade && $status === 'Completed' 
                        ? $amount - $paidAmount 
                        : $deductionPerCutoff;
                    
                    if ($paymentAmount > 0 && $paymentAmount <= ($amount - $paidAmount)) {
                        // Pass processed_by = 1 (admin user)
                        $loan->addPayment(
                            $paymentAmount,
                            null,
                            "Auto payment #{$i}",
                            1  // <-- Pass admin user ID
                        );
                        $paidAmount += $paymentAmount;
                    }
                }
            }
        }

        $this->command->info('Loans seeded successfully!');
    }
}