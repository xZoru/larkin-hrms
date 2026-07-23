@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Create Employee') }}
    </h2>
@endsection

@section('content')
<style>
    .required-label::after {
        content: ' *';
        color: #ef4444;
        font-weight: bold;
    }
    [required] {
        border-color: #d1d5db;
    }
    [required]:invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2;
    }
    [required]:valid {
        border-color: #d1d5db;
        background-color: white;
    }
    /* Toggle Switch Styles */
    .toggle-bg {
        background-color: #d1d5db;
        transition: background-color 0.3s;
    }
    .toggle-bg.active {
        background-color: #2563eb;
    }
    .toggle-circle {
        transition: transform 0.3s;
        transform: translateX(2px);
    }
    .toggle-circle.active {
        transform: translateX(22px);
    }
    .bank-required {
        border-color: #d1d5db;
    }
    .bank-required:invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2;
    }
    .bank-required:valid {
        border-color: #d1d5db;
        background-color: white;
    }
</style>

<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" id="employeeForm">
                    @csrf

                    <!-- VALIDATION ERRORS -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Employee Basic Information & Image -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Left: Employee Image -->
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Employee Image</h3>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">Choose File</p>
                                    <input type="file" name="photo" class="mt-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="mt-1 text-xs text-gray-400">No file chosen</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Employee Basic Information -->
                        <div class="md:col-span-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Employee Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 required-label">Company</label>
                                    <select name="company_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('company_id') border-red-500 @enderror">
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('company_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Employee Number</label>
                                    <input type="text" name="employee_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('employee_number') }}" placeholder="Auto-generated">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 required-label">First Name</label>
                                    <input type="text" name="first_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('first_name') border-red-500 @enderror" value="{{ old('first_name') }}">
                                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                                    <input type="text" name="middle_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('middle_name') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 required-label">Last Name</label>
                                    <input type="text" name="last_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('last_name') border-red-500 @enderror" value="{{ old('last_name') }}">
                                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Extension Name</label>
                                    <input type="text" name="extension_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('extension_name') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 required-label">Gender</label>
                                    <select name="gender" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('gender') border-red-500 @enderror">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 required-label">Marital Status</label>
                                    <select name="marital_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('marital_status') border-red-500 @enderror">
                                        <option value="">Select Marital Status</option>
                                        <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                        <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                        <option value="Divorced" {{ old('marital_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="Widowed" {{ old('marital_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                    @error('marital_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 required-label">Birth Date</label>
                                    <input type="text" 
                                        name="date_of_birth" 
                                        id="date_of_birth"
                                        required 
                                        class="flatpickr-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('date_of_birth') border-red-500 @enderror" 
                                        value="{{ old('date_of_birth') }}"
                                        placeholder="DD/MM/YYYY">
                                    @error('date_of_birth') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="text" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('phone') }}" placeholder="+058109">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input type="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('email') }}" placeholder="email@mail.com">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input type="text" name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('address') }}" placeholder="#123 address png">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Company Information -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Employee Company Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required-label">Joining Date</label>
                                <input type="text" 
                                    name="joining_date" 
                                    id="joining_date"
                                    required 
                                    class="flatpickr-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('joining_date') border-red-500 @enderror" 
                                    value="{{ old('joining_date') }}"
                                    placeholder="DD/MM/YYYY">
                                @error('joining_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="text" 
                                    name="end_date" 
                                    id="end_date"
                                    class="flatpickr-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    value="{{ old('end_date') }}"
                                    placeholder="DD/MM/YYYY">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deployment Date Home Country</label>
                                <input type="text" 
                                    name="deployment_date" 
                                    id="deployment_date"
                                    class="flatpickr-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    value="{{ old('deployment_date') }}"
                                    placeholder="DD/MM/YYYY">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Workshift</label>
                                <select name="workshift" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Workshift</option>
                                    <option value="Regular Dayshift (08:00 AM - 05:00 PM)" {{ old('workshift') == 'Regular Dayshift (08:00 AM - 05:00 PM)' ? 'selected' : '' }}>Regular Dayshift | 08:00 AM - 05:00 PM</option>
                                    <option value="Yellow Jacket Security (06:00 AM - 06:00 PM)" {{ old('workshift') == 'Yellow Jacket Security (06:00 AM - 06:00 PM)' ? 'selected' : '' }}>Yellow Jacket Security | 06:00 AM - 06:00 PM</option>
                                    <option value="Driver Shift (07:00 AM - 04:00 PM)" {{ old('workshift') == 'Driver Shift (07:00 AM - 04:00 PM)' ? 'selected' : '' }}>Driver Shift | 07:00 AM - 04:00 PM</option>
                                    <option value="Gereka Shift (07:30 AM - 04:30 PM)" {{ old('workshift') == 'Gereka Shift (07:30 AM - 04:30 PM)' ? 'selected' : '' }}>Gereka Shift | 07:30 AM - 04:30 PM</option>
                                    <option value="Wave Restaurant (07:00 AM - 07:00 PM)" {{ old('workshift') == 'Wave Restaurant (07:00 AM - 07:00 PM)' ? 'selected' : '' }}>Wave Restaurant | 07:00 AM - 07:00 PM</option>
                                    <option value="Kennedy Shift (07:30 AM - 04:30 PM)" {{ old('workshift') == 'Kennedy Shift (07:30 AM - 04:30 PM)' ? 'selected' : '' }}>Kennedy Shift | 07:30 AM - 04:30 PM</option>
                                    <option value="Wave New (07:00 AM - 04:00 PM)" {{ old('workshift') == 'Wave New (07:00 AM - 04:00 PM)' ? 'selected' : '' }}>Wave New | 07:00 AM - 04:00 PM</option>
                                    <option value="Hyve Store (07:00 AM - 05:00 PM)" {{ old('workshift') == 'Hyve Store (07:00 AM - 05:00 PM)' ? 'selected' : '' }}>Hyve Store | 07:00 AM - 05:00 PM</option>
                                </select>
                            </div>
                            <!-- Position/Designation -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required-label">Position/Designation</label>
                                <input type="text" name="position" list="position_suggestions" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('position') border-red-500 @enderror"
                                    value="{{ old('position') }}"
                                    placeholder="Type a position/designation">
                                <datalist id="position_suggestions">
                                    @foreach($positions as $position)
                                        <option value="{{ $position }}"></option>
                                    @endforeach
                                </datalist>
                                @error('position') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required-label">Employee Status</label>
                                <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('status') border-red-500 @enderror">
                                    <option value="">Select Employee Status</option>
                                    <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="Terminated" {{ old('status') == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                    <option value="Resigned" {{ old('status') == 'Resigned' ? 'selected' : '' }}>Resigned</option>
                                </select>
                                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required-label">Department</label>
                                <input type="text" 
                                    name="department_name" 
                                    list="department_suggestions" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('department_name') border-red-500 @enderror"
                                    value="{{ old('department_name') }}"
                                    placeholder="Type or select a department">
                                <datalist id="department_suggestions">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}"></option>
                                    @endforeach
                                </datalist>
                                @error('department_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required-label">Employee Label</label>
                                <select name="employee_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('employee_type') border-red-500 @enderror">
                                    <option value="">Select Label</option>
                                    <option value="National" {{ old('employee_type') == 'National' ? 'selected' : '' }}>National</option>
                                    <option value="Expatriate" {{ old('employee_type') == 'Expatriate' ? 'selected' : '' }}>Expatriate</option>
                                </select>
                                @error('employee_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SALARY SECTION -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Salary Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- Monthly Salary -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Monthly Salary (K)</label>
                                <input type="number" 
                                    step="0.01" 
                                    name="monthly_salary" 
                                    id="monthly_salary"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    value="{{ old('monthly_salary') }}" 
                                    placeholder="0.00"
                                    oninput="calculateHourlyRate()">
                                <p class="mt-1 text-xs text-gray-500">Enter monthly salary to auto-calculate hourly rate</p>
                            </div>
                            
                            <!-- Hourly Rate -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Hourly Rate (K)</label>
                                <input type="number" 
                                    step="0.01" 
                                    name="hourly_rate" 
                                    id="hourly_rate"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    value="{{ old('hourly_rate') }}" 
                                    placeholder="0.00"
                                    oninput="calculateMonthlySalary()">
                                <p class="mt-1 text-xs text-gray-500">Enter hourly rate to auto-calculate monthly salary</p>
                            </div>

                            <!-- Allowance -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Allowance (K)</label>
                                <input type="number"
                                    step="0.01"
                                    min="0"
                                    name="allowance"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('allowance') border-red-500 @enderror"
                                    value="{{ old('allowance') }}"
                                    placeholder="0.00">
                                @error('allowance') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <!-- Fortnight Hours -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fortnight Hours</label>
                                <select name="fortnight_hours" id="fortnight_hours" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="84">Standard (84 hours)</option>
                                    <option value="144">Security (144 hours)</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            
                            <div id="custom_hours_container" style="display:none;">
                                <label class="block text-sm font-medium text-gray-700">Custom Fortnight Hours</label>
                                <input type="number" name="custom_fortnight_hours" id="custom_fortnight_hours" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    placeholder="Enter hours">
                            </div>
                            
                            <!-- Quick Calculations -->
                            <div class="md:col-span-2 mt-2 p-3 bg-blue-50 rounded-lg">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">Daily Rate (8 hrs):</span>
                                        <span class="font-medium" id="daily_rate">K 0.00</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Weekly Rate (40 hrs):</span>
                                        <span class="font-medium" id="weekly_rate">K 0.00</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Fortnightly Rate:</span>
                                        <span class="font-medium" id="fortnightly_rate">K 0.00</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Annual Salary:</span>
                                        <span class="font-medium" id="annual_salary">K 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NASFUND SECTION -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">NASFUND Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- NASFUND Collect? - Toggle -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">NASFUND Collect?</label>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600" id="nasfundToggleLabel">No</span>
                                    <button type="button" id="nasfundToggle" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors duration-300 focus:outline-none toggle-bg">
                                        <span id="nasfundToggleCircle" class="inline-block w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-300 toggle-circle"></span>
                                    </button>
                                    <span class="text-sm text-gray-600" id="nasfundToggleLabelOn">Yes</span>
                                </div>
                            </div>

                            <!-- NASFUND Number - Input (hidden by default) -->
                            <div id="nasfundNumberContainer" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700">NASFUND Number</label>
                                <input type="text" name="nasfund_number" id="nasfund_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('nasfund_number') }}" placeholder="Enter NASFUND Number">
                            </div>
                        </div>
                    </div>

                    <!-- ============ BANK DETAILS ============ -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Bank Details</h3>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-600" id="bankToggleLabel">Off</span>
                                <button type="button" id="bankToggle" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors duration-300 focus:outline-none toggle-bg">
                                    <span id="bankToggleCircle" class="inline-block w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-300 toggle-circle"></span>
                                </button>
                                <span class="text-sm text-gray-600" id="bankToggleLabelOn">On</span>
                            </div>
                        </div>

                        <input type="hidden" name="bank_toggle" id="bank_toggle_hidden" value="off">

                        <div id="bankDetailsContainer" style="display: none;">
                            @for($i = 0; $i < 2; $i++)
                                <div class="bank-account-box">
                                    <h4 class="font-medium text-gray-700 mb-3">Account {{ $i + 1 }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Account Name</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][account_name]" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                                value="{{ old('bank_accounts.'.$i.'.account_name') }}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Account Number</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][account_number]" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                                value="{{ old('bank_accounts.'.$i.'.account_number') }}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][bank_name]" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                                value="{{ old('bank_accounts.'.$i.'.bank_name') }}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">BSB Code</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][bsb_code]" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                                value="{{ old('bank_accounts.'.$i.'.bsb_code') }}">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="preferred_account" value="{{ $i }}" 
                                                {{ $i == 0 ? 'checked' : '' }} 
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">Preferred Account</span>
                                        </label>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="mt-8 border-t border-gray-200 pt-6 flex items-center justify-end space-x-3">
                        <a href="{{ route('employees.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded">Cancel</a>
                        <button type="submit" name="action" value="save" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ✅ JAVASCRIPT -->
<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            // ============ NASFUND TOGGLE ============
            const nasfundToggle = document.getElementById('nasfundToggle');
            const nasfundCircle = document.getElementById('nasfundToggleCircle');
            const nasfundContainer = document.getElementById('nasfundNumberContainer');
            const nasfundOffLabel = document.getElementById('nasfundToggleLabel');
            const nasfundOnLabel = document.getElementById('nasfundToggleLabelOn');
            const nasfundInput = document.getElementById('nasfund_number');

            function toggleNasfund(show) {
                if (show) {
                    nasfundContainer.style.display = 'block';
                    nasfundToggle.classList.add('active');
                    nasfundCircle.classList.add('active');
                    nasfundOffLabel.style.fontWeight = 'normal';
                    nasfundOnLabel.style.fontWeight = 'bold';
                    nasfundInput.setAttribute('required', 'required');
                    nasfundInput.classList.add('required-label');
                } else {
                    nasfundContainer.style.display = 'none';
                    nasfundToggle.classList.remove('active');
                    nasfundCircle.classList.remove('active');
                    nasfundOffLabel.style.fontWeight = 'bold';
                    nasfundOnLabel.style.fontWeight = 'normal';
                    nasfundInput.removeAttribute('required');
                    nasfundInput.classList.remove('required-label');
                    nasfundInput.classList.remove('border-red-500', 'bg-red-50');
                }
            }

            if (nasfundToggle) {
                // Default: OFF
                toggleNasfund(false);

                nasfundToggle.addEventListener('click', function() {
                    const isVisible = nasfundContainer.style.display !== 'none';
                    toggleNasfund(!isVisible);
                });

                // If old value exists, turn ON
                @if(old('nasfund_number'))
                    setTimeout(function() {
                        if (nasfundContainer.style.display === 'none') {
                            toggleNasfund(true);
                        }
                    }, 100);
                @endif
            }

            // ============ BANK TOGGLE ============
            const bankToggle = document.getElementById('bankToggle');
            const bankCircle = document.getElementById('bankToggleCircle');
            const bankContainer = document.getElementById('bankDetailsContainer');
            const bankOffLabel = document.getElementById('bankToggleLabel');
            const bankOnLabel = document.getElementById('bankToggleLabelOn');
            const bankHiddenInput = document.getElementById('bank_toggle_hidden');
            const bankInputs = bankContainer ? bankContainer.querySelectorAll('input:not([name*="preferred_account"])') : [];

            function toggleBankFields(show) {
                // ✅ Update hidden input value
                if (bankHiddenInput) {
                    bankHiddenInput.value = show ? 'on' : 'off';
                }
                
                if (show) {
                    bankContainer.style.display = 'block';
                    bankToggle.classList.add('active');
                    bankCircle.classList.add('active');
                    bankOffLabel.style.fontWeight = 'normal';
                    bankOnLabel.style.fontWeight = 'bold';
                    
                    bankInputs.forEach(function(input) {
                        input.disabled = false;
                    });
                } else {
                    bankContainer.style.display = 'none';
                    bankToggle.classList.remove('active');
                    bankCircle.classList.remove('active');
                    bankOffLabel.style.fontWeight = 'bold';
                    bankOnLabel.style.fontWeight = 'normal';
                    
                    bankInputs.forEach(function(input) {
                        input.disabled = true;
                        input.value = '';
                    });
                }
            }

            if (bankToggle) {
                // Default: OFF
                toggleBankFields(false);

                bankToggle.addEventListener('click', function() {
                    const isVisible = bankContainer.style.display !== 'none';
                    toggleBankFields(!isVisible);
                });

                // If old value exists, turn ON
                @if(old('bank_toggle') == 'on' || old('bank_accounts.0.account_number'))
                    setTimeout(function() {
                        toggleBankFields(true);
                    }, 100);
                @endif
            }

            // ============ HIGHLIGHT REQUIRED FIELDS ============
            const form = document.getElementById('employeeForm');
            if (form) {
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        field.classList.add('border-red-500', 'bg-red-50');
                    }
                    field.addEventListener('input', function() {
                        if (this.value.trim()) {
                            this.classList.remove('border-red-500', 'bg-red-50');
                        } else {
                            this.classList.add('border-red-500', 'bg-red-50');
                        }
                    });
                });
            }
        });

        // ============ SALARY AUTO-CALCULATION ============
        function calculateHourlyRate() {
            const monthlySalary = parseFloat(document.getElementById('monthly_salary').value);
            const fortnightHours = parseInt(document.getElementById('fortnight_hours').value);
            const customHours = parseInt(document.getElementById('custom_fortnight_hours').value);
            
            // Calculate monthly hours
            let hoursPerFortnight = fortnightHours;
            if (fortnightHours === 'custom') {
                hoursPerFortnight = customHours || 84;
            }
            const monthlyHours = (hoursPerFortnight * 26) / 12;
            
            if (monthlySalary && monthlySalary > 0) {
                const hourlyRate = monthlySalary / monthlyHours;
                document.getElementById('hourly_rate').value = hourlyRate.toFixed(2);
                updateQuickCalculations(hourlyRate, hoursPerFortnight);
                document.getElementById('formula_display').textContent = 
                    `Monthly Salary (K${monthlySalary.toFixed(2)}) ÷ ${monthlyHours.toFixed(1)} hours = K${hourlyRate.toFixed(2)}/hr`;
            }
        }

        function calculateMonthlySalary() {
            const hourlyRate = parseFloat(document.getElementById('hourly_rate').value);
            const fortnightHours = parseInt(document.getElementById('fortnight_hours').value);
            const customHours = parseInt(document.getElementById('custom_fortnight_hours').value);
            
            let hoursPerFortnight = fortnightHours;
            if (fortnightHours === 'custom') {
                hoursPerFortnight = customHours || 84;
            }
            const monthlyHours = (hoursPerFortnight * 26) / 12;
            
            if (hourlyRate && hourlyRate > 0) {
                const monthlySalary = hourlyRate * monthlyHours;
                document.getElementById('monthly_salary').value = monthlySalary.toFixed(2);
                updateQuickCalculations(hourlyRate, hoursPerFortnight);
                document.getElementById('formula_display').textContent = 
                    `Hourly Rate (K${hourlyRate.toFixed(2)}) × ${monthlyHours.toFixed(1)} hours = K${monthlySalary.toFixed(2)}/month`;
            }
        }

        function updateQuickCalculations(hourlyRate, hoursPerFortnight) {
            const dailyRate = hourlyRate * 8;
            const weeklyRate = hourlyRate * 40;
            const fortnightlyRate = hourlyRate * hoursPerFortnight;
            const annualSalary = fortnightlyRate * 26;
            
            document.getElementById('daily_rate').textContent = `K ${dailyRate.toFixed(2)}`;
            document.getElementById('weekly_rate').textContent = `K ${weeklyRate.toFixed(2)}`;
            document.getElementById('fortnightly_rate').textContent = `K ${fortnightlyRate.toFixed(2)}`;
            document.getElementById('annual_salary').textContent = `K ${annualSalary.toFixed(2)}`;
            
            document.getElementById('hours_display').textContent = 
                `${hoursPerFortnight} hours per fortnight × 26 fortnights ÷ 12 months = ${(hoursPerFortnight * 26 / 12).toFixed(1)} hours/month`;
        }

        // ============ FORTNIGHT HOURS SELECTOR ============
        document.addEventListener('DOMContentLoaded', function() {
            const fortnightSelect = document.getElementById('fortnight_hours');
            const customContainer = document.getElementById('custom_hours_container');
            const customInput = document.getElementById('custom_fortnight_hours');
            
            if (fortnightSelect) {
                fortnightSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customContainer.style.display = 'block';
                        customInput.focus();
                    } else {
                        customContainer.style.display = 'none';
                        // Recalculate with selected value
                        calculateHourlyRate();
                    }
                });
            }
            
            if (customInput) {
                customInput.addEventListener('input', function() {
                    calculateHourlyRate();
                });
            }
        });

                // ============ FLATPKR DATE PICKER ============
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all date pickers
            flatpickr(".flatpickr-input", {
                dateFormat: "d/m/Y",
                allowInput: true,
                weekNumbers: true,
                altInput: true,
                altFormat: "Y/m/d",
            });

            // You can also add specific configs for different fields if needed
            flatpickr("#date_of_birth", {
                dateFormat: "d/m/Y",
                allowInput: true,
                maxDate: new Date(), // Can't select future dates
                altInput: true,
                altFormat: "d/m/Y",
            });

            flatpickr("#joining_date", {
                dateFormat: "d/m/Y",
                allowInput: true,
                altInput: true,
                altFormat: "Y/m/d",
            });
        });
        
    })();
</script>
@endsection
