<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'extension_name' => 'nullable|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'gender' => 'required|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'employee_type' => 'required|in:National,Expatriate',
            'date_of_birth' => 'required|date|before:today',
            'photo' => 'nullable|image|max:2048',
            'joining_date' => 'required|date',
            'end_date' => 'nullable|date|after:joining_date',
            'deployment_date' => 'nullable|date',
            'passport_number' => 'nullable|string|max:50',
            'passport_expiry' => 'nullable|date',
            'work_permit_number' => 'nullable|string|max:50',
            'work_permit_expiry' => 'nullable|date',
            'visa_number' => 'nullable|string|max:50',
            'visa_expiry' => 'nullable|date',
            'nasfund_number' => 'nullable|string|max:50',
            'nasfund_dependents' => 'nullable|integer|min:0',
            'nasfund_allocation_percentage' => 'nullable|numeric|min:0|max:100',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'fortnight_hours' => 'nullable|integer|min:1',
            'custom_fortnight_hours' => 'nullable|integer|min:1',
            'payment_method' => 'nullable|in:Bank Transfer,Cash',
            'status' => 'nullable|in:Active,Inactive,Terminated,Resigned',
            'position' => 'required|string|max:255',
            // Bank fields - NOT REQUIRED
            'bank_accounts' => 'nullable|array|max:2',
            'bank_accounts.*.account_name' => 'nullable|string|max:255',
            'bank_accounts.*.account_number' => 'nullable|string|max:50',
            'bank_accounts.*.bank_name' => 'nullable|string|max:255',
            'bank_accounts.*.bsb_code' => 'nullable|string|max:20',
        ];
    }
}
