@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Holiday
        </h2>
        <div class="text-sm text-gray-500">
            Management / Holidays / Edit
        </div>
    </div>
@endsection

@section('content')
<style>
    .edit-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .edit-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .edit-header .header-subtitle {
        font-size: 14px;
        color: #a0aec0;
    }
    .edit-header .holiday-name {
        color: #a78bfa;
        font-weight: 600;
    }
    
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    .form-card .card-header {
        background: #f8fafc;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #1a1f36;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-card .card-header i {
        color: #6366f1;
    }
    .form-card .card-body {
        padding: 24px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    .form-group:last-child {
        margin-bottom: 0;
    }
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }
    .form-label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }
    .form-control:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .form-control:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }
    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 0;
    }
    .form-check input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #6366f1;
        cursor: pointer;
    }
    .form-check label {
        font-size: 14px;
        color: #334155;
        cursor: pointer;
    }
    
    .btn-cancel {
        background: #e2e8f0;
        color: #475569;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-cancel:hover {
        background: #cbd5e1;
    }
    .btn-submit {
        background: #4f46e5;
        color: white;
        padding: 10px 24px;
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
    
    .alert-custom {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .alert-custom.alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .alert-custom .icon {
        font-size: 18px;
        flex-shrink: 0;
    }
    
    @media (max-width: 768px) {
        .edit-header { padding: 16px; }
        .edit-header .header-title { font-size: 16px; }
        .form-card .card-body { padding: 16px; }
        .btn-cancel, .btn-submit { width: 100%; text-align: center; }
        .form-actions { flex-direction: column; gap: 8px; }
    }
</style>

<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="edit-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-calendar-edit text-indigo-300 mr-2"></i> Edit Holiday
                    </div>
                    <div class="header-subtitle mt-1">
                        Editing: <span class="holiday-name">{{ $holiday->name }}</span>
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('holidays.index') }}" class="btn-cancel" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-arrow-left"></i> Back to Holidays
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert-custom alert-danger">
                <span class="icon">❌</span>
                <div>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Edit Form -->
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-pen"></i> Holiday Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('holidays.update', $holiday) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">
                            Holiday Name <span class="required">*</span>
                        </label>
                        <input type="text" name="name" required 
                               class="form-control" 
                               value="{{ old('name', $holiday->name) }}" 
                               placeholder="e.g., Independence Day">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Date <span class="required">*</span>
                        </label>
                        <input type="date" name="date" required 
                               class="form-control" 
                               value="{{ old('date', $holiday->date->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" 
                                  class="form-control" 
                                  placeholder="Optional description...">{{ old('description', $holiday->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="is_recurring" value="1" 
                                   {{ old('is_recurring', $holiday->is_recurring) ? 'checked' : '' }}>
                            <label>Recurring annually (e.g., Christmas every year)</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $holiday->is_active) ? 'checked' : '' }}>
                            <label>Active</label>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <a href="{{ route('holidays.index') }}" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Update Holiday
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection