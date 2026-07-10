@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Employee') }}: {{ $employee->full_name }}
    </h2>
@endsection

@section('content')
<style>
    .required-label::after {
        content: ' *';
        color: #ef4444;
        font-weight: bold;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 4px;
    }
    .form-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        background: white;
    }
    .form-input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .form-input:disabled {
        background: #f3f4f6;
        cursor: not-allowed;
    }
    .form-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        background: white;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-select:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .form-error {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }
    .tabs-container {
        display: grid;
        grid-template-columns: repeat(10, 1fr);
        gap: 4px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 2px;
    }
    .tabs-container button {
        font-size: 13px;
        padding: 10px 8px;
        background: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        text-align: center;
        width: 100%;
    }
    .tabs-container button:hover {
        color: #374151;
    }
    .tab-active {
        color: #1f2937 !important;
        font-weight: 600;
        border-bottom: 3px solid #4f46e5 !important;
    }
    @media (max-width: 768px) {
        .tabs-container {
            grid-template-columns: repeat(5, 1fr);
            gap: 2px;
            overflow-x: auto;
        }
        .tabs-container button {
            font-size: 11px;
            padding: 8px 4px;
        }
    }
    @media (max-width: 480px) {
        .tabs-container {
            grid-template-columns: repeat(3, 1fr);
        }
        .tabs-container button {
            font-size: 10px;
            padding: 6px 2px;
        }
    }
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
    .toggle-label {
        font-size: 13px;
        color: #6b7280;
    }
    .toggle-label.active {
        font-weight: 600;
        color: #1f2937;
    }
    .toggle-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .photo-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        transition: border-color 0.2s;
    }
    .photo-upload-area:hover {
        border-color: #6366f1;
    }
    .bank-account-box {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        background: #fafafa;
    }
    .bank-account-box:last-child {
        margin-bottom: 0;
    }
    .section-title {
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 8px;
    }
    .tab-content {
        padding-top: 20px;
    }
    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
        padding: 8px 24px;
        border-radius: 6px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-secondary:hover {
        background: #d1d5db;
    }
    .btn-success {
        background: #22c55e;
        color: white;
        padding: 8px 24px;
        border-radius: 6px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-success:hover {
        background: #16a34a;
    }
    .btn-primary {
        background: #4f46e5;
        color: white;
        padding: 8px 24px;
        border-radius: 6px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-primary:hover {
        background: #4338ca;
    }
    .btn-danger {
        background: #ef4444;
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-danger:hover {
        background: #dc2626;
    }
    .doc-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin-bottom: 16px;
        transition: border-color 0.2s;
    }
    .doc-upload-area:hover {
        border-color: #6366f1;
    }
    .doc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .doc-table th {
        text-align: left;
        padding: 10px 12px;
        background: #f9fafb;
        font-weight: 600;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e5e7eb;
    }
    .doc-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .doc-table tr:hover td {
        background: #f9fafb;
    }
    .doc-preview {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #6b7280;
    }
    .pay-raise-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
    }
    .pay-raise-item:last-child {
        margin-bottom: 0;
    }
    .pay-raise-amount {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }
    .pay-raise-amount span {
        font-size: 14px;
        font-weight: 400;
        color: #6b7280;
    }
    .pay-raise-date {
        font-size: 13px;
        color: #6b7280;
    }
    .pay-raise-badge {
        background: #dbeafe;
        color: #2563eb;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 12px;
    }
    @media (max-width: 768px) {
        .grid-2, .grid-3 {
            grid-template-columns: 1fr;
        }
        .tabs-container {
            gap: 12px;
        }
        .tabs-container button {
            font-size: 12px;
            padding: 8px 2px;
        }
        .doc-table {
            font-size: 12px;
        }
        .doc-table th, .doc-table td {
            padding: 6px 8px;
        }
    }
</style>

<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <strong class="font-bold">Please fix the following errors:</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- ============ TABS ============ -->
                <div class="tabs-container mb-4">
                    <button type="button" class="tab-active" data-tab="basic">Basic Info</button>
                    <button type="button" class="tab-inactive" data-tab="work">Work Info</button>
                    <button type="button" class="tab-inactive" data-tab="salary">Salary</button>
                    <button type="button" class="tab-inactive" data-tab="bank">Bank Details</button>
                    <button type="button" class="tab-inactive" data-tab="documents">Documents</button>
                    <button type="button" class="tab-inactive" data-tab="leaves">Leaves</button>
                    <button type="button" class="tab-inactive" data-tab="attendance">Attendance</button>
                    <button type="button" class="tab-inactive" data-tab="earnings">Earnings</button>
                    <button type="button" class="tab-inactive" data-tab="loans">Loans</button>
                    <button type="button" class="tab-inactive" data-tab="assets">Assets (0)</button>
                </div>

                <!-- ============ MAIN FORM ============ -->
                <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" id="employeeForm">
                    @csrf
                    @method('PUT')

                    <!-- TAB 1: BASIC INFO -->
                    <div id="tab-basic" class="tab-content">
                        <div class="grid-2">
                            <div>
                                <div class="section-title">Employee Image</div>
                                <div class="photo-upload-area">
                                    <div class="flex flex-col items-center">
                                        @if($employee->photo_path)
                                            <img src="{{ Storage::url($employee->photo_path) }}" class="h-24 w-24 rounded-full object-cover mb-3">
                                        @else
                                            <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center mb-3">
                                                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <label class="cursor-pointer bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                                            Choose File
                                            <input type="file" name="photo" class="hidden" accept="image/*">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-400">No file chosen</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="section-title">Personal Information</div>
                                <div class="grid-2">
                                    <div class="form-group">
                                        <label class="form-label required-label">First Name</label>
                                        <input type="text" name="first_name" required class="form-input @error('first_name') border-red-500 @enderror" value="{{ old('first_name', $employee->first_name) }}">
                                        @error('first_name') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-input" value="{{ old('middle_name', $employee->middle_name) }}" placeholder="Middle name">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required-label">Last Name</label>
                                        <input type="text" name="last_name" required class="form-input @error('last_name') border-red-500 @enderror" value="{{ old('last_name', $employee->last_name) }}">
                                        @error('last_name') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Extension Name</label>
                                        <input type="text" name="extension_name" class="form-input" value="{{ old('extension_name', $employee->extension_name) }}" placeholder="Extension name">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-input" value="{{ old('email', $employee->email) }}" placeholder="email@mail.com">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Mobile Number</label>
                                        <input type="text" name="phone" class="form-input" value="{{ old('phone', $employee->phone) }}" placeholder="+675 7448 3385">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required-label">Gender</label>
                                        <select name="gender" required class="form-select @error('gender') border-red-500 @enderror">
                                            <option value="Male" {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ old('gender', $employee->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('gender') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required-label">Marital Status</label>
                                        <select name="marital_status" required class="form-select @error('marital_status') border-red-500 @enderror">
                                            <option value="Single" {{ old('marital_status', $employee->marital_status) == 'Single' ? 'selected' : '' }}>Single</option>
                                            <option value="Married" {{ old('marital_status', $employee->marital_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                            <option value="Divorced" {{ old('marital_status', $employee->marital_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                            <option value="Widowed" {{ old('marital_status', $employee->marital_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                        </select>
                                        @error('marital_status') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required-label">Birth Date</label>
                                        <input type="date" name="date_of_birth" required class="form-input @error('date_of_birth') border-red-500 @enderror" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                                        @error('date_of_birth') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-input" value="{{ old('address', $employee->address) }}" placeholder="#123 address png">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: WORK INFO -->
                    <div id="tab-work" class="tab-content" style="display:none;">
                        <div class="grid-2">
                            <div>
                                <div class="section-title">Company Information</div>
                                <div class="form-group">
                                    <label class="form-label required-label">Company</label>
                                    <select name="company_id" required class="form-select @error('company_id') border-red-500 @enderror">
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $employee->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('company_id') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label">Department</label>
                                    <select name="department_id" required class="form-select @error('department_id') border-red-500 @enderror">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                                
                                <!-- Position/Designation - FIXED -->
                                <div class="form-group">
                                    <label class="form-label required-label">Position/Designation</label>
                                    <select name="position_id" required class="form-select @error('position_id') border-red-500 @enderror">
                                        <option value="">Select Position/Designation</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}
                                                @if($position->department)
                                                    ({{ $position->department->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_id') <p class="form-error">{{ $message }}</p> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Workshift</label>
                                    <select name="workshift" class="form-select">
                                        <option value="">Select Workshift</option>
                                        <option value="Regular Dayshift (08:00 AM - 05:00 PM)" {{ old('workshift', $employee->workshift) == 'Regular Dayshift (08:00 AM - 05:00 PM)' ? 'selected' : '' }}>Regular Dayshift | 08:00 AM - 05:00 PM</option>
                                        <option value="Yellow Jacket Security (06:00 AM - 06:00 PM)" {{ old('workshift', $employee->workshift) == 'Yellow Jacket Security (06:00 AM - 06:00 PM)' ? 'selected' : '' }}>Yellow Jacket Security | 06:00 AM - 06:00 PM</option>
                                        <option value="Driver Shift (07:00 AM - 04:00 PM)" {{ old('workshift', $employee->workshift) == 'Driver Shift (07:00 AM - 04:00 PM)' ? 'selected' : '' }}>Driver Shift | 07:00 AM - 04:00 PM</option>
                                        <option value="Gereka Shift (07:30 AM - 04:30 PM)" {{ old('workshift', $employee->workshift) == 'Gereka Shift (07:30 AM - 04:30 PM)' ? 'selected' : '' }}>Gereka Shift | 07:30 AM - 04:30 PM</option>
                                        <option value="Wave Restaurant (07:00 AM - 07:00 PM)" {{ old('workshift', $employee->workshift) == 'Wave Restaurant (07:00 AM - 07:00 PM)' ? 'selected' : '' }}>Wave Restaurant | 07:00 AM - 07:00 PM</option>
                                        <option value="Kennedy Shift (07:30 AM - 04:30 PM)" {{ old('workshift', $employee->workshift) == 'Kennedy Shift (07:30 AM - 04:30 PM)' ? 'selected' : '' }}>Kennedy Shift | 07:30 AM - 04:30 PM</option>
                                        <option value="Wave New (07:00 AM - 04:00 PM)" {{ old('workshift', $employee->workshift) == 'Wave New (07:00 AM - 04:00 PM)' ? 'selected' : '' }}>Wave New | 07:00 AM - 04:00 PM</option>
                                        <option value="Hyve Store (07:00 AM - 05:00 PM)" {{ old('workshift', $employee->workshift) == 'Hyve Store (07:00 AM - 05:00 PM)' ? 'selected' : '' }}>Hyve Store | 07:00 AM - 05:00 PM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label">Employee Status</label>
                                    <select name="status" required class="form-select @error('status') border-red-500 @enderror">
                                        <option value="Active" {{ old('status', $employee->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ old('status', $employee->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="Terminated" {{ old('status', $employee->status) == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                        <option value="Resigned" {{ old('status', $employee->status) == 'Resigned' ? 'selected' : '' }}>Resigned</option>
                                    </select>
                                    @error('status') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label required-label">Employee Label</label>
                                    <select name="employee_type" required class="form-select @error('employee_type') border-red-500 @enderror">
                                        <option value="National" {{ old('employee_type', $employee->employee_type) == 'National' ? 'selected' : '' }}>National</option>
                                        <option value="Expatriate" {{ old('employee_type', $employee->employee_type) == 'Expatriate' ? 'selected' : '' }}>Expatriate</option>
                                    </select>
                                    @error('employee_type') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <div class="section-title">Employment Dates</div>
                                <div class="form-group">
                                    <label class="form-label required-label">Joining Date</label>
                                    <input type="date" name="joining_date" required class="form-input @error('joining_date') border-red-500 @enderror" value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}">
                                    @error('joining_date') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-input" value="{{ old('end_date', $employee->end_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Deployment Date</label>
                                    <input type="date" name="deployment_date" class="form-input" value="{{ old('deployment_date', $employee->deployment_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="section-title mt-4">NASFUND Details</div>
                                <div class="form-group">
                                    <label class="form-label">NASFUND Collect?</label>
                                    <div class="toggle-container">
                                        <span class="toggle-label" id="nasfundToggleLabel">No</span>
                                        <button type="button" id="nasfundToggle" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors duration-300 focus:outline-none toggle-bg {{ $employee->nasfund_number ? 'active' : '' }}">
                                            <span id="nasfundToggleCircle" class="inline-block w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-300 toggle-circle {{ $employee->nasfund_number ? 'active' : '' }}"></span>
                                        </button>
                                        <span class="toggle-label" id="nasfundToggleLabelOn">Yes</span>
                                    </div>
                                </div>
                                <div id="nasfundNumberContainer" style="{{ $employee->nasfund_number ? 'display:block' : 'display:none' }}">
                                    <div class="form-group">
                                        <label class="form-label">NASFUND Number</label>
                                        <input type="text" name="nasfund_number" id="nasfund_number" class="form-input" value="{{ old('nasfund_number', $employee->nasfund_number) }}" placeholder="Enter NASFUND Number" {{ $employee->nasfund_number ? 'required' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: SALARY -->
                    <div id="tab-salary" class="tab-content" style="display:none;">
                        <div class="grid-2">
                            <div>
                                <div class="section-title">Salary Information</div>
                                
                            <!-- Fortnight Hours -->
                            <div class="form-group">
                                <label class="form-label">Fortnight Hours</label>
                                <select name="fortnight_hours" id="fortnight_hours" class="form-select">
                                    @php
                                        $currentFortnightHours = $employee->fortnight_hours ?? 84;
                                    @endphp
                                    <option value="84" {{ $currentFortnightHours == 84 ? 'selected' : '' }}>Standard (84 hours)</option>
                                    <option value="144" {{ $currentFortnightHours == 144 ? 'selected' : '' }}>Security (144 hours)</option>
                                    <option value="custom" {{ !in_array($currentFortnightHours, [84, 144]) ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>

                            <!-- Custom Hours -->
                            <div class="form-group" id="custom_hours_container" style="{{ !in_array($currentFortnightHours, [84, 144]) ? 'display:block' : 'display:none' }}">
                                <label class="form-label">Custom Fortnight Hours</label>
                                <input type="number" 
                                    name="custom_fortnight_hours" 
                                    id="custom_fortnight_hours" 
                                    class="form-input" 
                                    value="{{ !in_array($currentFortnightHours, [84, 144]) ? $currentFortnightHours : '' }}" 
                                    placeholder="Enter hours"
                                    oninput="calculateHourlyRate()">
                            </div>

                            <!-- Monthly Salary -->
                            <div class="form-group">
                                <label class="form-label">Monthly Salary (K)</label>
                                <input type="number" 
                                    step="0.01" 
                                    name="monthly_salary" 
                                    id="monthly_salary"
                                    class="form-input" 
                                    value="{{ old('monthly_salary', $employee->monthly_salary ?? '') }}" 
                                    placeholder="0.00"
                                    oninput="calculateHourlyRate()">
                                <p class="mt-1 text-xs text-gray-500">Enter monthly salary to auto-calculate hourly rate</p>
                            </div>

                            <!-- Hourly Rate -->
                            <div class="form-group">
                                <label class="form-label">Current Hourly Rate (K)</label>
                                <input type="number" 
                                    step="0.01" 
                                    name="hourly_rate" 
                                    id="hourly_rate"
                                    class="form-input" 
                                    value="{{ old('hourly_rate', $employee->hourly_rate) }}" 
                                    placeholder="0.00"
                                    oninput="calculateMonthlySalary()">
                                <p class="mt-1 text-xs text-gray-500">Enter hourly rate to auto-calculate monthly salary</p>
                            </div>

                                <!-- Payment Method -->
                                <div class="form-group">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="Bank Transfer" {{ old('payment_method', $employee->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="Cash" {{ old('payment_method', $employee->payment_method) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                </div>

                                <!-- Pay Raise Reason -->
                                <div class="form-group">
                                    <label class="form-label">Pay Raise Reason (optional)</label>
                                    <input type="text" name="pay_raise_reason" class="form-input" value="{{ old('pay_raise_reason') }}" placeholder="e.g., Annual increase, Promotion, Performance">
                                </div>

                                <!-- Quick Calculations -->
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">Formula:</span>
                                        <span id="formula_display">
                                            Monthly Salary ÷ {{ number_format(($currentFortnightHours * 26) / 12, 1) }} hours = Hourly Rate
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Based on:</span>
                                        <span id="hours_display">{{ $currentFortnightHours }} hours per fortnight</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="section-title">Quick Calculations</div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Daily Rate (8 hrs):</span>
                                            <span class="font-medium" id="daily_rate">K {{ number_format(($employee->hourly_rate ?? 0) * 8, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Weekly Rate (40 hrs):</span>
                                            <span class="font-medium" id="weekly_rate">K {{ number_format(($employee->hourly_rate ?? 0) * 40, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Fortnightly Rate:</span>
                                            <span class="font-medium" id="fortnightly_rate">K {{ number_format(($employee->hourly_rate ?? 0) * $currentFortnightHours, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Annual Salary:</span>
                                            <span class="font-medium" id="annual_salary">K {{ number_format(($employee->hourly_rate ?? 0) * $currentFortnightHours * 26, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pay Raise History -->
                                <div class="mt-4">
                                    <div class="section-title">Pay Raise History</div>
                                    @php
                                        try {
                                            $payHistory = Illuminate\Support\Facades\DB::table('pay_increase_history')
                                                ->where('employee_id', $employee->id)
                                                ->orderBy('increase_date', 'desc')
                                                ->get();
                                        } catch (\Exception $e) {
                                            $payHistory = collect();
                                        }
                                    @endphp
                                    @if($payHistory->count() > 0)
                                        @foreach($payHistory as $history)
                                            <div class="pay-raise-item">
                                                <div>
                                                    <div class="pay-raise-amount">
                                                        K {{ number_format($history->new_rate, 2) }}
                                                        <span>→ K {{ number_format($history->previous_rate, 2) }}</span>
                                                    </div>
                                                    <div class="pay-raise-date">{{ \Carbon\Carbon::parse($history->increase_date)->format('M d, Y') }}</div>
                                                </div>
                                                <span class="pay-raise-badge">+{{ number_format($history->increase_percentage, 1) }}%</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-sm">No pay raise history available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============ TAB 4: BANK DETAILS ============ -->
                    <div id="tab-bank" class="tab-content" style="display:none;">
                        <div class="section-title">Bank Details</div>
                        
                        <input type="hidden" name="bank_toggle" id="bank_toggle_hidden" value="{{ $employee->bankAccounts->count() > 0 ? 'on' : 'off' }}">
                        
                        <div class="flex items-center space-x-3 mb-4">
                            <span class="toggle-label" id="bankToggleLabel">Off</span>
                            <button type="button" id="bankToggle" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors duration-300 focus:outline-none toggle-bg {{ $employee->bankAccounts->count() > 0 ? 'active' : '' }}">
                                <span id="bankToggleCircle" class="inline-block w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-300 toggle-circle {{ $employee->bankAccounts->count() > 0 ? 'active' : '' }}"></span>
                            </button>
                            <span class="toggle-label" id="bankToggleLabelOn">On</span>
                        </div>

                        <div id="bankDetailsContainer" style="{{ $employee->bankAccounts->count() > 0 ? 'display:block' : 'display:none' }}">
                            @php
                                $bankAccounts = $employee->bankAccounts->keyBy('priority');
                            @endphp
                            @for($i = 0; $i < 2; $i++)  <!-- ✅ START FROM 0 -->
                                @php 
                                    $priority = $i + 1;
                                    $account = $bankAccounts->get($priority);
                                @endphp
                                <div class="bank-account-box">
                                    <h4 class="font-medium text-gray-700 mb-3">Account {{ $priority }}</h4>
                                    <div class="grid-2">
                                        <div class="form-group">
                                            <label class="form-label">Account Name</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][account_name]" class="form-input" value="{{ old('bank_accounts.'.$i.'.account_name', $account->account_name ?? '') }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Account Number</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][account_number]" class="form-input" value="{{ old('bank_accounts.'.$i.'.account_number', $account->account_number ?? '') }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Bank Name</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][bank_name]" class="form-input" value="{{ old('bank_accounts.'.$i.'.bank_name', $account->bank_name ?? '') }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">BSB Code</label>
                                            <input type="text" name="bank_accounts[{{ $i }}][bsb_code]" class="form-input" value="{{ old('bank_accounts.'.$i.'.bsb_code', $account->bsb_code ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                name="preferred_account" 
                                                value="{{ $i }}" 
                                                {{ old('preferred_account', ($account && $account->is_preferred) ?? ($i == 0)) ? 'checked' : '' }} 
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">Preferred Account</span>
                                        </label>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- TAB: LEAVES -->
                    <div id="tab-leaves" class="tab-content" style="display:none;">
                        <p class="text-gray-500 text-sm">Leaves section coming soon...</p>
                    </div>

                    <!-- TAB: ATTENDANCE -->
                    <div id="tab-attendance" class="tab-content" style="display:none;">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-gray-500 text-sm">View and manage attendance logs for this employee.</p>
                            <a href="{{ route('employees.attendance', $employee) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">View Full Attendance</a>
                        </div>
                        <div class="mb-4">
                            <form method="GET" action="{{ route('employees.edit', $employee) }}" class="flex items-center gap-3">
                                <input type="hidden" name="tab" value="attendance">
                                <label class="block text-sm font-medium text-gray-700">Select Fortnight</label>
                                <select name="fortnight" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm py-1.5 px-3" onchange="this.form.submit()">
                                    @php
                                        $employeeFortnights = $employee->attendanceSummaries()
                                            ->orderBy('created_at', 'desc')
                                            ->pluck('fortnight_number')
                                            ->toArray();
                                        if (empty($employeeFortnights)) {
                                            $currentYear = date('y');
                                            for ($i = 1; $i <= 26; $i++) {
                                                $employeeFortnights[] = $currentYear . str_pad($i, 2, '0', STR_PAD_LEFT);
                                            }
                                            $employeeFortnights = array_reverse($employeeFortnights);
                                        }
                                        $selectedFortnight = request('fortnight') ?? ($employeeFortnights[0] ?? null);
                                    @endphp
                                    @foreach($employeeFortnights as $fn)
                                        <option value="{{ $fn }}" {{ $selectedFortnight == $fn ? 'selected' : '' }}>{{ $fn }}</option>
                                    @endforeach
                                </select>
                                @if(request('fortnight'))
                                    <a href="{{ route('employees.edit', $employee) }}?tab=attendance" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
                                @endif
                            </form>
                        </div>
                        @php
                            $selectedFortnight = request('fortnight') ?? ($employee->attendanceSummaries()->orderBy('created_at', 'desc')->first()->fortnight_number ?? null);
                            $currentFortnight = $employee->attendanceSummaries()->where('fortnight_number', $selectedFortnight)->first();
                            $currentFortnightLogs = $employee->attendanceLogs()->where('fortnight_number', $selectedFortnight)->orderBy('date', 'asc')->get();
                            $period = null;
                            if ($selectedFortnight) {
                                $attendanceController = new \App\Http\Controllers\AttendanceController();
                                $period = $attendanceController->getFortnightPeriod($selectedFortnight);
                            }
                        @endphp
                        @if($currentFortnight || $currentFortnightLogs->count() > 0)
                            @if($period)
                                <div class="text-sm text-gray-500 mb-3">Period: {{ $period['start']->format('M d, Y') }} - {{ $period['end']->format('M d, Y') }}</div>
                            @endif
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                                    <div class="text-center"><div class="text-sm text-gray-500">Total Hours</div><div class="text-xl font-bold">{{ number_format($currentFortnight->total_hours ?? $currentFortnightLogs->sum('hours_worked') ?? 0, 1) }}</div></div>
                                    <div class="text-center"><div class="text-sm text-gray-500">Regular</div><div class="text-xl font-bold text-green-600">{{ number_format($currentFortnight->regular_hours ?? 0, 1) }}</div></div>
                                    <div class="text-center"><div class="text-sm text-gray-500">Overtime</div><div class="text-xl font-bold text-orange-600">{{ number_format($currentFortnight->overtime_hours ?? 0, 1) }}</div></div>
                                    <div class="text-center"><div class="text-sm text-gray-500">Sunday</div><div class="text-xl font-bold text-purple-600">{{ number_format($currentFortnight->sunday_hours ?? 0, 1) }}</div></div>
                                    <div class="text-center"><div class="text-sm text-gray-500">Holiday</div><div class="text-xl font-bold text-red-600">{{ number_format($currentFortnight->holiday_hours ?? 0, 1) }}</div></div>
                                    <div class="text-center"><div class="text-sm text-gray-500">Present Days</div><div class="text-xl font-bold">{{ $currentFortnight->present_days ?? $currentFortnightLogs->where('hours_worked', '>', 0)->count() ?? 0 }}/{{ $currentFortnight->total_days ?? $currentFortnightLogs->count() ?? 0 }}</div></div>
                                </div>
                            </div>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50"><tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Time In</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Time Out</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Hours</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Break</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($currentFortnightLogs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2">{{ $log->date->format('M d, Y') }}</td>
                                            <td class="px-3 py-2">{{ $log->date->format('l') }}</td>
                                            <td class="px-3 py-2 text-center">{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '-' }}</td>
                                            <td class="px-3 py-2 text-center">{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '-' }}</td>
                                            <td class="px-3 py-2 text-right font-medium">{{ number_format($log->hours_worked, 1) }}</td>
                                            <td class="px-3 py-2 text-center">{{ $log->has_break ? '✅' : '❌' }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($log->is_holiday)<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Holiday</span>
                                                @elseif($log->is_sunday)<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">Sunday</span>
                                                @else<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Regular</span>@endif
                                            </td>
                                            <td class="px-3 py-2 text-center">@if($log->hours_worked > 0)<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Present</span>@else<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Absent</span>@endif</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="px-3 py-6 text-center text-gray-500">No attendance logs found for this fortnight.</td></tr>
                                        @endforelse
                                    </tbody>
                                    @if($currentFortnightLogs->count() > 0)
                                    <tfoot class="bg-gray-50 font-semibold">
                                        <tr><td class="px-3 py-2 text-right" colspan="4">TOTAL</td><td class="px-3 py-2 text-right">{{ number_format($currentFortnightLogs->sum('hours_worked'), 1) }}</td><td class="px-3 py-2" colspan="3"></td></tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        @else
                            <div class="bg-gray-50 rounded-lg p-6 text-center">
                                <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-gray-500">No attendance records found.</p>
                                <p class="text-sm text-gray-400 mt-2">Click "View Full Attendance" to add logs.</p>
                            </div>
                        @endif
                    </div>

                    <!-- TAB: EARNINGS -->
                    <div id="tab-earnings" class="tab-content" style="display:none;">
                        <p class="text-gray-500 text-sm">Summary of Earnings coming soon...</p>
                    </div>

                    <!-- TAB: LOANS -->
                    <div id="tab-loans" class="tab-content" style="display:none;">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900">Loan History</h4>
                            <a href="{{ route('loan-requests.create') }}?employee_id={{ $employee->id }}" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">+ Add Loan</a>
                        </div>
                        @if($employee->loans->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50"><tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Loan Type</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Deduction/Cutoff</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Paid</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($employee->loans as $loan)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ $loan->loan_type }}</span></td>
                                            <td class="px-3 py-2 text-right font-medium">K {{ number_format($loan->amount, 2) }}</td>
                                            <td class="px-3 py-2 text-right">K {{ number_format($loan->deduction_per_cutoff, 2) }}</td>
                                            <td class="px-3 py-2 text-right"><span class="text-{{ $loan->remaining_balance > 0 ? 'red-600' : 'green-600' }} font-bold">K {{ number_format($loan->remaining_balance, 2) }}</span></td>
                                            <td class="px-3 py-2 text-right">K {{ number_format($loan->total_paid, 2) }}</td>
                                            <td class="px-3 py-2 text-center"><span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $statusColors[$loan->status] ?? 'gray' }}-100 text-{{ $statusColors[$loan->status] ?? 'gray' }}-800">{{ $loan->status }}</span></td>
                                            <td class="px-3 py-2 text-center text-xs text-gray-500">{{ $loan->created_at->format('M d, Y') }}</td>
                                            <td class="px-3 py-2 text-center"><a href="{{ route('loan-requests.show', $loan) }}" class="text-blue-600 hover:text-blue-800 text-xs">View</a></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr><td class="px-3 py-2 font-bold" colspan="2">TOTAL</td><td class="px-3 py-2 text-right font-bold">K {{ number_format($employee->loans->sum('amount'), 2) }}</td><td class="px-3 py-2 text-right font-bold text-red-600">K {{ number_format($employee->loans->sum('remaining_balance'), 2) }}</td><td class="px-3 py-2 text-right font-bold text-green-600">K {{ number_format($employee->loans->sum('total_paid'), 2) }}</td><td colspan="3"></td></tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p>No loan records found for this employee.</p>
                                <a href="{{ route('loan-requests.create') }}?employee_id={{ $employee->id }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800 text-sm">+ Request a Loan</a>
                            </div>
                        @endif
                    </div>

                    <!-- TAB: ASSETS -->
                    <div id="tab-assets" class="tab-content" style="display:none;">
                        <p class="text-gray-500 text-sm">Assets (0) coming soon...</p>
                    </div>

                    <!-- ============ SAVE BUTTONS ============ -->
                    <div class="mt-8 border-t border-gray-200 pt-6 flex items-center justify-end space-x-3">
                        <a href="{{ route('employees.index') }}" class="btn-secondary">Cancel</a>
                        <button type="button" name="action" value="update" class="btn-success" onclick="document.getElementById('employeeForm').submit();">Update</button>
                        <button type="button" name="action" value="update_stay" class="btn-primary" onclick="document.getElementById('employeeForm').submit();">Update & Stay</button>
                    </div>

                </form>
                <!-- ============ MAIN FORM ENDS HERE ============ -->

                <!-- ============ DOCUMENTS TAB (MOVED OUTSIDE MAIN FORM) ============ -->
                <div id="tab-documents" class="tab-content" style="display:none;">
                    <div class="section-title">Upload Document</div>
                    
                    @if(session('document_success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                            ✅ {{ session('document_success') }}
                        </div>
                    @endif
                    
                    @if(session('document_error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                            ❌ {{ session('document_error') }}
                        </div>
                    @endif

                    <form action="{{ route('employees.upload-document', ['employee' => $employee->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="doc-upload-area">
                            <div class="flex flex-col items-center">
                                <svg class="h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="text-sm text-gray-500 mb-2">Choose File</p>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <label class="cursor-pointer bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Choose File
                                        <input type="file" name="document" id="documentFile" class="hidden" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required>
                                    </label>
                                    <input type="text" name="document_name" placeholder="Document Name" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                                    <select name="document_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                                        <option value="Contract">Contract</option>
                                        <option value="Certificate">Certificate</option>
                                        <option value="ID">ID</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm">Upload</button>
                                </div>
                                <p class="mt-2 text-xs text-gray-400" id="fileNameDisplay">No file chosen</p>
                            </div>
                        </div>
                    </form>

                    <!-- Uploaded Documents Table -->
                    <div class="section-title mt-6">Uploaded Documents</div>
                    <div class="overflow-x-auto">
                        <table class="doc-table">
                            <thead><tr>
                                <th>#</th><th>Preview</th><th>Document Name</th><th>Type</th><th>Uploaded At</th><th>Action</th>
                            </tr></thead>
                            <tbody>
                                @if($employee->documents && $employee->documents->count() > 0)
                                    @foreach($employee->documents as $index => $doc)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="doc-preview">
                                                @if(in_array($doc->mime_type, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']))
                                                    <img src="{{ Storage::url($doc->file_path) }}" class="h-10 w-10 object-cover rounded">
                                                @else
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $doc->document_name }}</td>
                                        <td><span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">{{ $doc->document_type ?? 'N/A' }}</span></td>
                                        <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                                                <form method="POST" action="{{ route('employees.document.destroy', ['employee' => $employee->id, 'document' => $doc->id]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this document?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="6" class="text-center text-gray-500 py-4">No documents uploaded yet.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Expatriate Documents -->
                    <div id="expatriate-docs-section" style="{{ $employee->employee_type == 'Expatriate' ? 'display:block' : 'display:none' }}">
                        <div class="mt-6 border-t border-gray-200 pt-4">
                            <div class="section-title">Expatriate Documents</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Passport Number</label>
                                    <input type="text" name="passport_number" class="form-input" value="{{ old('passport_number', $employee->passport_number) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Passport Expiry</label>
                                    <input type="date" name="passport_expiry" class="form-input" value="{{ old('passport_expiry', $employee->passport_expiry?->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Work Permit Number</label>
                                    <input type="text" name="work_permit_number" class="form-input" value="{{ old('work_permit_number', $employee->work_permit_number) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Work Permit Expiry</label>
                                    <input type="date" name="work_permit_expiry" class="form-input" value="{{ old('work_permit_expiry', $employee->work_permit_expiry?->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Visa Number</label>
                                    <input type="text" name="visa_number" class="form-input" value="{{ old('visa_number', $employee->visa_number) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Visa Expiry</label>
                                    <input type="date" name="visa_expiry" class="form-input" value="{{ old('visa_expiry', $employee->visa_expiry?->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {

            const tabs = document.querySelectorAll('[data-tab]');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    tabs.forEach(function(t) {
                        t.classList.remove('tab-active');
                        t.classList.add('tab-inactive');
                    });
                    this.classList.add('tab-active');
                    this.classList.remove('tab-inactive');

                    contents.forEach(function(c) {
                        c.style.display = 'none';
                    });

                    const target = document.getElementById('tab-' + this.dataset.tab);
                    if (target) {
                        target.style.display = 'block';
                    }
                });
            });

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
                    nasfundOffLabel.classList.add('active');
                    nasfundOnLabel.classList.add('active');
                    if (nasfundInput) {
                        nasfundInput.setAttribute('required', 'required');
                    }
                } else {
                    nasfundContainer.style.display = 'none';
                    nasfundToggle.classList.remove('active');
                    nasfundCircle.classList.remove('active');
                    nasfundOffLabel.classList.remove('active');
                    nasfundOnLabel.classList.remove('active');
                    if (nasfundInput) {
                        nasfundInput.removeAttribute('required');
                        nasfundInput.classList.remove('border-red-500', 'bg-red-50');
                    }
                }
            }

            if (nasfundToggle) {
                nasfundToggle.addEventListener('click', function() {
                    const isVisible = nasfundContainer.style.display !== 'none';
                    toggleNasfund(!isVisible);
                });
            }

            // ============ BANK TOGGLE (FIXED) ============
            const bankToggle = document.getElementById('bankToggle');
            const bankCircle = document.getElementById('bankToggleCircle');
            const bankContainer = document.getElementById('bankDetailsContainer');
            const bankOffLabel = document.getElementById('bankToggleLabel');
            const bankOnLabel = document.getElementById('bankToggleLabelOn');
            const bankHiddenInput = document.getElementById('bank_toggle_hidden');
            const bankInputs = bankContainer ? bankContainer.querySelectorAll('input:not([name*="is_preferred"])') : [];

            function toggleBankFields(show) {
                // ✅ Update hidden input value
                if (bankHiddenInput) {
                    bankHiddenInput.value = show ? 'on' : 'off';
                }
                
                if (show) {
                    bankContainer.style.display = 'block';
                    bankToggle.classList.add('active');
                    bankCircle.classList.add('active');
                    bankOffLabel.classList.add('active');
                    bankOnLabel.classList.add('active');
                    
                    // Enable inputs
                    bankInputs.forEach(function(input) {
                        input.disabled = false;
                    });
                } else {
                    bankContainer.style.display = 'none';
                    bankToggle.classList.remove('active');
                    bankCircle.classList.remove('active');
                    bankOffLabel.classList.remove('active');
                    bankOnLabel.classList.remove('active');
                    
                    // Disable and clear inputs
                    bankInputs.forEach(function(input) {
                        input.disabled = true;
                        input.value = '';
                    });
                }
            }

            if (bankToggle) {
                // Initialize based on existing bank accounts
                const hasBankAccounts = {{ $employee->bankAccounts->count() > 0 ? 'true' : 'false' }};
                toggleBankFields(hasBankAccounts);

                bankToggle.addEventListener('click', function() {
                    const isVisible = bankContainer.style.display !== 'none';
                    toggleBankFields(!isVisible);
                });
            }

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

            const documentFile = document.getElementById('documentFile');
            const fileNameDisplay = document.getElementById('fileNameDisplay');

            if (documentFile && fileNameDisplay) {
                documentFile.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(1) + ' KB)';
                        fileNameDisplay.style.color = '#16a34a';
                    } else {
                        fileNameDisplay.textContent = 'No file chosen';
                        fileNameDisplay.style.color = '#94a3b8';
                    }
                });
            }

            const employeeTypeSelect = document.querySelector('select[name="employee_type"]');
            const expatriateDocsSection = document.getElementById('expatriate-docs-section');

            if (employeeTypeSelect && expatriateDocsSection) {
                function toggleExpatriateDocs() {
                    if (employeeTypeSelect.value === 'Expatriate') {
                        expatriateDocsSection.style.display = 'block';
                    } else {
                        expatriateDocsSection.style.display = 'none';
                    }
                }
                toggleExpatriateDocs();
                employeeTypeSelect.addEventListener('change', toggleExpatriateDocs);
            }
        });
    })();
</script>
@endsection