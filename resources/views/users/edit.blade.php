@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit User
        </h2>
        <div class="text-sm text-gray-500">
            Management / Users / Edit
        </div>
    </div>
@endsection

@section('content')
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 24px;
        max-width: 768px;
        margin: 0 auto;
    }
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .form-control.error {
        border-color: #dc2626;
    }
    .form-control:disabled {
        background: #f3f4f6;
        cursor: not-allowed;
    }
    .form-error {
        font-size: 12px;
        color: #dc2626;
        margin-top: 4px;
    }
    .btn-submit {
        background: #4f46e5;
        color: white;
        padding: 10px 32px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-submit:hover {
        background: #4338ca;
    }
    .btn-back {
        background: #f1f5f9;
        color: #475569;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #e2e8f0;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-grid .full-width {
        grid-column: 1 / -1;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
    }
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #4f46e5;
        cursor: pointer;
    }
    .checkbox-group label {
        font-size: 14px;
        color: #374151;
        cursor: pointer;
    }
    .help-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.active {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-4">
            <a href="{{ route('users.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>

        <div class="form-card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Edit User</h2>
                <span class="badge-status {{ $user->is_active ? 'active' : 'inactive' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <!-- Name -->
                    <div class="full-width">
                        <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') error @enderror" 
                               value="{{ old('name', $user->name) }}" placeholder="John Doe" required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Companies - Multiple Selection -->
                    <div class="full-width">
                        <label class="form-label">Companies <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2 mt-2 p-3 border border-gray-200 rounded-lg bg-gray-50">
                            @foreach($companies as $company)
                                <div class="checkbox-group">
                                    <input type="checkbox" name="companies[]" value="{{ $company->id }}" 
                                        id="company_{{ $company->id }}"
                                        @checked(in_array($company->id, $userCompanies))>
                                    <label for="company_{{ $company->id }}">{{ $company->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="help-text">Select one or more companies this user can access.</div>
                        @error('companies')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Default Company -->
                    <div class="full-width" id="defaultCompanyContainer">
                        <label class="form-label">Default Company <span class="text-red-500">*</span></label>
                        <select name="default_company" class="form-control @error('default_company') error @enderror" required>
                            <option value="">Select Default Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" 
                                        @selected(($defaultCompany && $defaultCompany->id == $company->id) || old('default_company') == $company->id)
                                        style="{{ in_array($company->id, $userCompanies) ? '' : 'display:none;' }}">
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="help-text">This will be the primary company for this user.</div>
                        @error('default_company')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const checkboxes = document.querySelectorAll('input[name="companies[]"]');
                        const defaultSelect = document.querySelector('select[name="default_company"]');
                        
                        function updateDefaultOptions() {
                            const selected = [];
                            checkboxes.forEach(cb => {
                                if (cb.checked) {
                                    selected.push(cb.value);
                                }
                            });
                            
                            const options = defaultSelect.querySelectorAll('option');
                            options.forEach(opt => {
                                if (opt.value) {
                                    opt.style.display = selected.includes(opt.value) ? '' : 'none';
                                }
                            });
                            
                            if (selected.length === 1) {
                                defaultSelect.value = selected[0];
                            } else if (!defaultSelect.value || !selected.includes(defaultSelect.value)) {
                                defaultSelect.value = '';
                            }
                        }
                        
                        checkboxes.forEach(cb => {
                            cb.addEventListener('change', updateDefaultOptions);
                        });
                        
                        updateDefaultOptions();
                    });
                    </script>

                    <!-- Email -->
                    <div class="full-width">
                        <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') error @enderror" 
                               value="{{ old('email', $user->email) }}" placeholder="john@example.com" required>
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                                        <!-- User Type -->
                    <div class="full-width">
                        <label class="form-label">User Type <span class="text-red-500">*</span></label>
                        <select name="user_type" class="form-control @error('user_type') error @enderror" required>
                            <option value="all" @selected(old('user_type', $user->user_type) == 'all')>All Employees (Can view both National & Expatriate)</option>
                            <option value="national" @selected(old('user_type', $user->user_type) == 'national')>National Only</option>
                            <option value="expatriate" @selected(old('user_type', $user->user_type) == 'expatriate')>Expatriate Only</option>
                        </select>
                        <div class="help-text">Determines which employees this user can view.</div>
                        @error('user_type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') error @enderror" 
                               placeholder="Leave blank to keep current">
                        <div class="help-text">Min 8 characters. Leave blank to keep current password.</div>
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" 
                               placeholder="Confirm new password">
                    </div>

                    <!-- Roles -->
                    <div class="full-width">
                        <label class="form-label">Roles</label>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            @foreach($roles as $role)
                                <div class="checkbox-group">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                           id="role_{{ $role->id }}"
                                           @checked(in_array($role->id, $userRoles))>
                                    <label for="role_{{ $role->id }}">{{ ucfirst($role->name) }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="help-text">Assign one or more roles to this user.</div>
                    </div>

                    <!-- Active Status -->
                    <div class="full-width">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                   @checked(old('is_active', $user->is_active))>
                            <label for="is_active">Active</label>
                        </div>
                        <div class="help-text">Inactive users cannot log in.</div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('users.index') }}" class="btn-back">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection