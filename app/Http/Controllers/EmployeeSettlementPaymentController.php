<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSettlementPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeSettlementPaymentController extends Controller
{
    public function createFinalPay()
    {
        return view('settlements.final-pay', $this->formData('final_pay'));
    }

    public function createAnnualLeavePay()
    {
        return view('settlements.annual-leave-pay', $this->formData('annual_leave_pay'));
    }

    public function storeFinalPay(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer', 'commenced_at' => 'required|date', 'ended_at' => 'required|date|after_or_equal:commenced_at',
            'hours_per_day' => 'required|numeric|min:0.01', 'hourly_rate' => 'required|numeric|min:0',
            'issuer_name' => 'required|string|max:255', 'issuer_position' => 'required|string|max:255',
        ]);

        $employee = $this->employeeForCurrentCompany($data['employee_id']);
        $start = Carbon::parse($data['commenced_at']);
        $end = Carbon::parse($data['ended_at']);
        $days = $start->diffInDays($end);
        $months = round($days / 30.333333, 2);
        $amount = round(1.5 * $data['hours_per_day'] * $data['hourly_rate'] * $months, 2);

        $payment = EmployeeSettlementPayment::create(array_merge($data, [
            'company_id' => auth()->user()->getCurrentCompanyId(), 'employee_id' => $employee->id,
            'type' => 'final_pay', 'service_days' => $days, 'service_months' => $months,
            'amount' => $amount, 'created_by' => auth()->id(),
        ]));

        return $this->download($payment);
    }

    public function storeAnnualLeavePay(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer', 'commenced_at' => 'required|date', 'ended_at' => 'required|date|after_or_equal:commenced_at',
            'hours_per_week' => 'required|numeric|min:0.01', 'leave_weeks' => 'required|numeric|min:0', 'hourly_rate' => 'required|numeric|min:0',
            'issuer_name' => 'required|string|max:255', 'issuer_position' => 'required|string|max:255',
        ]);

        $employee = $this->employeeForCurrentCompany($data['employee_id']);
        $start = Carbon::parse($data['commenced_at']);
        $end = Carbon::parse($data['ended_at']);
        $days = $start->diffInDays($end);
        $fraction = min(1, $days / 365);
        $amount = round($data['hourly_rate'] * $data['hours_per_week'] * $data['leave_weeks'] * $fraction, 2);

        $payment = EmployeeSettlementPayment::create(array_merge($data, [
            'company_id' => auth()->user()->getCurrentCompanyId(), 'employee_id' => $employee->id,
            'type' => 'annual_leave_pay', 'service_days' => $days, 'service_months' => round($days / 30.333333, 2),
            'service_fraction' => $fraction, 'amount' => $amount, 'created_by' => auth()->id(),
        ]));

        return $this->download($payment);
    }

    public function download(EmployeeSettlementPayment $payment)
    {
        abort_unless($payment->company_id === auth()->user()->getCurrentCompanyId(), 403);
        $payment->load('employee.company');
        $company = $payment->employee->company;
        $this->attachPayslipLogo($company);
        $view = $payment->type === 'final_pay' ? 'settlements.final-pay-pdf' : 'settlements.annual-leave-pay-pdf';

        return Pdf::loadView($view, compact('payment', 'company'))
            ->setPaper('a4')
            ->download($payment->type . '-' . $payment->employee->employee_number . '.pdf');
    }

    private function formData(string $type): array
    {
        $companyId = auth()->user()->getCurrentCompanyId();
        return [
            'employees' => Employee::where('company_id', $companyId)->orderBy('full_name')->get(),
            'issuerName' => auth()->user()->name,
            'issuerPosition' => auth()->user()->user_type ?? 'Authorized Officer',
            'type' => $type,
        ];
    }

    private function employeeForCurrentCompany(int $employeeId): Employee
    {
        return Employee::where('company_id', auth()->user()->getCurrentCompanyId())->findOrFail($employeeId);
    }

    private function attachPayslipLogo($company): void
    {
        if (!$company) {
            return;
        }

        $logoPath = null;
        if (!empty($company->logo_path)) {
            $path = public_path($company->logo_path);
            if (file_exists($path)) {
                $logoPath = $path;
            }
        }

        if (!$logoPath && $company->name) {
            $companyName = strtolower(str_replace(' ', '-', trim($company->name)));
            foreach (['jpg', 'png', 'jpeg'] as $extension) {
                $path = public_path('images/' . $companyName . '.' . $extension);
                if (file_exists($path)) {
                    $logoPath = $path;
                    break;
                }
            }
        }

        if (!$logoPath) {
            foreach (['logo.jpg', 'logo.png', 'logo.jpeg', 'logo.JPG', 'logo.PNG'] as $file) {
                $path = public_path('images/' . $file);
                if (file_exists($path)) {
                    $logoPath = $path;
                    break;
                }
            }
        }

        $company->logo_data = $logoPath
            ? 'data:image/' . (strtolower(pathinfo($logoPath, PATHINFO_EXTENSION)) === 'jpg' ? 'jpeg' : strtolower(pathinfo($logoPath, PATHINFO_EXTENSION))) . ';base64,' . base64_encode(file_get_contents($logoPath))
            : null;
    }
}
