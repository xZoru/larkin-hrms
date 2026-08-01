<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('loans')->orderBy('id')->eachById(function ($loan) {
            $paymentTotals = DB::table('loan_payments')
                ->where('loan_id', $loan->id)
                ->selectRaw('COALESCE(SUM(amount), 0) as total_paid, COUNT(*) as payments_made')
                ->first();

            $totalPaid = min((float) $loan->amount, (float) $paymentTotals->total_paid);
            $remainingBalance = max(0, (float) $loan->amount - $totalPaid);

            DB::table('loans')->where('id', $loan->id)->update([
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance,
                'payments_made' => $paymentTotals->payments_made,
                'status' => $remainingBalance <= 0 && $paymentTotals->payments_made > 0
                    ? 'Completed'
                    : $loan->status,
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Payment history remains the source of truth; do not discard it.
    }
};
