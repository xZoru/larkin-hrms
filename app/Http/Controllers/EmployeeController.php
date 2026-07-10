<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\BankAccount;
use App\Models\Position;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class EmployeeController extends Controller
{
    // Display a listing of employees
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $employees = Employee::with(['company', 'department', 'position'])
            ->when($companyId, function($query) use ($companyId) {
                return $query->where('company_id', $companyId);
            })
            ->when($request->search, function($query) use ($request) {
                return $query->where(function($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->search . '%')
                      ->orWhere('employee_number', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->position_id, function($query) use ($request) {
                return $query->where('position_id', $request->position_id);
            })
            ->when($request->department, function($query) use ($request) {
                return $query->where('department_id', $request->department);
            })
            ->when($request->employee_type, function($query) use ($request) {
                return $query->where('employee_type', $request->employee_type);
            })
            ->when($request->status, function($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->paginate(20);

        // Get positions for the filter dropdown
        $positions = Position::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('employees.index', compact('employees', 'positions'));
    }

    // Show the form for creating a new employee
    public function create()
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super Admin')) {
            $companies = Company::where('is_active', true)->get();
        } else {
            $companies = Company::where('id', $user->company_id)->get();
        }
        
        $departments = Department::where('company_id', $user->company_id)->get();
        
        $positions = Position::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->with('department')
            ->orderBy('name')
            ->get();

        return view('employees.create', compact('companies', 'departments', 'positions'));
    }

    // Store a newly created employee
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        $data['first_name'] = $request->first_name;
        $data['middle_name'] = $request->middle_name;
        $data['last_name'] = $request->last_name;
        $data['extension_name'] = $request->extension_name;

        $fullName = $request->first_name;
        if ($request->middle_name) {
            $fullName .= ' ' . $request->middle_name;
        }
        $fullName .= ' ' . $request->last_name;
        if ($request->extension_name) {
            $fullName .= ' ' . $request->extension_name;
        }

        $data['full_name'] = $fullName;

        // Save position_id (foreign key)
        $data['position_id'] = $request->position_id;

        $data['status'] = $request->status ?? 'Active';
        $data['employee_type'] = $request->employee_type ?? 'National';
        $data['marital_status'] = $request->marital_status;
        $data['gender'] = $request->gender;
        $data['joining_date'] = $request->joining_date;
        $data['date_of_birth'] = $request->date_of_birth;
        $data['company_id'] = $request->company_id;
        $data['department_id'] = $request->department_id;

        $fortnightHours = $request->fortnight_hours ?? 84;
        if ($request->fortnight_hours === 'custom') {
            $fortnightHours = $request->custom_fortnight_hours ?? 84;
        }
        
        // Save fortnight hours
        $data['fortnight_hours'] = $fortnightHours;
        
        // If monthly salary is provided, calculate hourly rate
        if ($request->filled('monthly_salary') && $request->monthly_salary > 0) {
            $monthlyHours = ($fortnightHours * 26) / 12;
            $data['hourly_rate'] = round($request->monthly_salary / $monthlyHours, 2);
            $data['monthly_salary'] = $request->monthly_salary;
        } 
        // If hourly rate is provided, calculate monthly salary
        else if ($request->filled('hourly_rate') && $request->hourly_rate > 0) {
            $monthlyHours = ($fortnightHours * 26) / 12;
            $data['monthly_salary'] = round($request->hourly_rate * $monthlyHours, 2);
            $data['hourly_rate'] = $request->hourly_rate;
        }
        
        // Calculate base salary (fortnightly)
        if (!empty($data['hourly_rate'])) {
            $data['base_salary'] = $data['hourly_rate'] * $fortnightHours;
        }

        // Generate employee number if not provided
        if (empty($data['employee_number'])) {
            $company = Company::find($data['company_id']);
            $lastEmployee = Employee::where('company_id', $data['company_id'])
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastEmployee) {
                $lastNumber = intval(substr($lastEmployee->employee_number, -4));
                $count = $lastNumber + 1;
            } else {
                $count = 1;
            }
            
            $data['employee_number'] = substr($company->code, 0, 3) . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        // Calculate base salary (84 hours per fortnight)
        if (!empty($data['hourly_rate'])) {
            $data['base_salary'] = $data['hourly_rate'] * 84;
        }

        // Create employee with all data
        $employee = Employee::create($data);

        // Save bank accounts (up to 2) - only if toggle is ON
        if ($request->input('bank_toggle') == 'on') {
            if ($request->has('bank_accounts')) {
                foreach ($request->bank_accounts as $index => $account) {
                    if (!empty($account['account_number'])) {
                        $isPreferred = false;
                        if ($request->input('preferred_account') == $index) {
                            $isPreferred = true;
                        }
                        
                        BankAccount::create([
                            'employee_id' => $employee->id,
                            'account_name' => $account['account_name'],
                            'account_number' => $account['account_number'],
                            'bank_name' => $account['bank_name'],
                            'bsb_code' => $account['bsb_code'],
                            'is_preferred' => $isPreferred,
                            'priority' => $index + 1,
                            'is_active' => true
                        ]);
                    }
                }
            }
        }

        // Check which button was clicked
        if ($request->input('action') === 'save_new') {
            return redirect()->route('employees.create')
                ->with('success', 'Employee created successfully. Add another?');
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    // Display the specified employee
    public function show(Employee $employee)
    {
        $employee->load(['company', 'department', 'position', 'bankAccounts', 'documents']);
        
        // Expiry notifications (90 days before)
        $expiringDocs = [];
        if ($employee->employee_type === 'Expatriate') {
            if ($employee->passport_expiry && $employee->passport_expiry <= now()->addDays(90)) {
                $expiringDocs['passport'] = $employee->passport_expiry;
            }
            if ($employee->visa_expiry && $employee->visa_expiry <= now()->addDays(90)) {
                $expiringDocs['visa'] = $employee->visa_expiry;
            }
            if ($employee->work_permit_expiry && $employee->work_permit_expiry <= now()->addDays(90)) {
                $expiringDocs['work_permit'] = $employee->work_permit_expiry;
            }
        }

        return view('employees.show', compact('employee', 'expiringDocs'));
    }

    // Show the form for editing the specified employee
    public function edit(Employee $employee)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super Admin')) {
            $companies = Company::where('is_active', true)->get();
        } else {
            $companies = Company::where('id', $user->company_id)->get();
        }
        
        $departments = Department::where('company_id', $employee->company_id)->get();
        
        $positions = Position::where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->with('department')
            ->orderBy('name')
            ->get();
                
        $employee->load(['bankAccounts', 'documents', 'loans' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);
        
        return view('employees.edit', compact('employee', 'companies', 'departments', 'positions'));
    }

    // Update the specified employee
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        // Store individual name fields
        $data['first_name'] = $request->first_name;
        $data['middle_name'] = $request->middle_name;
        $data['last_name'] = $request->last_name;
        $data['extension_name'] = $request->extension_name;

        // Combine name fields into full_name
        $fullName = $request->first_name;
        if ($request->middle_name) {
            $fullName .= ' ' . $request->middle_name;
        }
        $fullName .= ' ' . $request->last_name;
        if ($request->extension_name) {
            $fullName .= ' ' . $request->extension_name;
        }

        $data['full_name'] = $fullName;

        // Save position_id (foreign key) - FIXED: use position_id, not position
        $data['position_id'] = $request->position_id;

        // Check if hourly_rate changed
        $oldRate = $employee->hourly_rate;
        $newRate = $request->hourly_rate;

            $fortnightHours = $request->fortnight_hours ?? 84;
    if ($request->fortnight_hours === 'custom') {
        $fortnightHours = $request->custom_fortnight_hours ?? 84;
    }
    
    // Save fortnight hours
    $data['fortnight_hours'] = $fortnightHours;
    
    // Check if hourly_rate changed
    $oldRate = $employee->hourly_rate;
    $newRate = $request->hourly_rate;
    
    // If monthly salary is provided, calculate hourly rate
    if ($request->filled('monthly_salary') && $request->monthly_salary > 0) {
        $monthlyHours = ($fortnightHours * 26) / 12;
        $data['hourly_rate'] = round($request->monthly_salary / $monthlyHours, 2);
        $data['monthly_salary'] = $request->monthly_salary;
    } 
    // If hourly rate is provided, calculate monthly salary
    else if ($request->filled('hourly_rate') && $request->hourly_rate > 0) {
        $monthlyHours = ($fortnightHours * 26) / 12;
        $data['monthly_salary'] = round($request->hourly_rate * $monthlyHours, 2);
        $data['hourly_rate'] = $request->hourly_rate;
    }
    
    // Calculate base salary (fortnightly)
    if (!empty($data['hourly_rate'])) {
        $data['base_salary'] = $data['hourly_rate'] * $fortnightHours;
    }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        // Calculate base salary
        if (!empty($data['hourly_rate'])) {
            $data['base_salary'] = $data['hourly_rate'] * 84;
        }

        $employee->update($data);

        // Save pay raise history if rate changed
        if ($oldRate != $newRate && $newRate > 0) {
            $increasePercentage = $oldRate > 0 
                ? (($newRate - $oldRate) / $oldRate) * 100 
                : 0;

            \App\Models\PayIncreaseHistory::create([
                'employee_id' => $employee->id,
                'previous_rate' => $oldRate,
                'new_rate' => $newRate,
                'increase_percentage' => $increasePercentage,
                'increase_date' => now(),
                'reason' => $request->pay_raise_reason ?? 'Salary adjustment',
                'approved_by' => auth()->id()
            ]);
        }

        // Update bank accounts based on toggle state
        if ($request->input('bank_toggle') == 'on') {
            if ($request->has('bank_accounts')) {
                $employee->bankAccounts()->delete();
                
                foreach ($request->bank_accounts as $index => $account) {
                    if (!empty($account['account_number'])) {
                        $isPreferred = false;
                        if ($request->input('preferred_account') == $index) {
                            $isPreferred = true;
                        }
                        
                        BankAccount::create([
                            'employee_id' => $employee->id,
                            'account_name' => $account['account_name'],
                            'account_number' => $account['account_number'],
                            'bank_name' => $account['bank_name'],
                            'bsb_code' => $account['bsb_code'],
                            'is_preferred' => $isPreferred,
                            'priority' => $index + 1,
                            'is_active' => true
                        ]);
                    }
                }
            }
        } else {
            $employee->bankAccounts()->delete();
        }

        // Check which button was clicked
        if ($request->input('action') === 'update_stay') {
            return redirect()->route('employees.edit', $employee)
                ->with('success', 'Employee updated successfully. Continue editing?');
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    // Delete the specified employee
    public function destroy(Employee $employee)
    {
        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function uploadDocument(Request $request, Employee $employee)
    {
        $validator = validator($request->all(), [
            'document' => 'required|file|max:10240',
            'document_name' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed', ['errors' => $validator->errors()->toArray()]);
            return redirect()->route('employees.edit', $employee->id)
                ->with('document_error', 'Validation failed: ' . $validator->errors()->first());
        }

        try {
            $path = $request->file('document')->store('employees/documents/' . $employee->id, 'public');

            $employee->documents()->create([
                'document_name' => $request->document_name,
                'document_type' => $request->document_type ?? 'Other',
                'file_path' => $path,
                'file_name' => $request->file('document')->getClientOriginalName(),
                'mime_type' => $request->file('document')->getMimeType(),
                'file_size' => $request->file('document')->getSize(),
                'uploaded_by' => auth()->id()
            ]);

            \Log::info('Document uploaded successfully', ['employee_id' => $employee->id, 'path' => $path]);

            return redirect()->route('employees.edit', $employee->id)
                ->with('document_success', 'Document "' . $request->document_name . '" uploaded successfully!');
                    
        } catch (\Exception $e) {
            \Log::error('Upload error', ['message' => $e->getMessage()]);
            return redirect()->route('employees.edit', $employee->id)
                ->with('document_error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    // Get expiring documents (for dashboard notifications)
    public function getExpiringDocuments()
    {
        $companyId = auth()->user()->company_id;
        
        $employees = Employee::where('company_id', $companyId)
            ->where(function($query) {
                $query->where('passport_expiry', '<=', now()->addDays(90))
                      ->orWhere('visa_expiry', '<=', now()->addDays(90))
                      ->orWhere('work_permit_expiry', '<=', now()->addDays(90));
            })
            ->get(['id', 'full_name', 'employee_number', 'passport_expiry', 'visa_expiry', 'work_permit_expiry']);

        return response()->json($employees);
    }

    public function destroyDocument(Employee $employee, $documentId)
    {
        try {
            $document = $employee->documents()->findOrFail($documentId);
            
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            $document->delete();
            
            return redirect()->route('employees.edit', $employee->id)
                ->with('document_success', 'Document deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('employees.edit', $employee->id)
                ->with('document_error', 'Error deleting document: ' . $e->getMessage());
        }
    }
}