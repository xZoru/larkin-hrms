@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Bank Details: {{ $company->name }}
        </h2>
        <div class="text-sm text-gray-500">
            Management / Company Bank Details / Edit
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
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
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
    .form-control::placeholder {
        color: #94a3b8;
    }
    .form-hint {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
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
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
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
        .grid-2 { grid-template-columns: 1fr; }
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
                        <i class="fas fa-university text-indigo-300 mr-2"></i> Edit Bank Details
                    </div>
                    <div class="header-subtitle mt-1">
                        {{ $company->name }}
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('company-bank-details.index') }}" class="btn-cancel" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-arrow-left"></i> Back
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

        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-pen"></i> Bank Account Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('company-bank-details.update', $company) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" 
                                   value="{{ old('bank_name', $company->bank_name) }}" 
                                   placeholder="e.g., BSP Bank, ANZ, Kina Bank">
                            <div class="form-hint">Name of the bank</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">BSB Code</label>
                            <input type="text" name="bsb_code" class="form-control" 
                                   value="{{ old('bsb_code', $company->bsb_code) }}" 
                                   placeholder="e.g., 088-950">
                            <div class="form-hint">6-digit BSB code (with or without hyphen)</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control" 
                                   value="{{ old('bank_account_number', $company->bank_account_number) }}" 
                                   placeholder="e.g., 7009276416">
                            <div class="form-hint">Bank account number</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="bank_account_name" class="form-control" 
                                   value="{{ old('bank_account_name', $company->bank_account_name) }}" 
                                   placeholder="e.g., LARKIN ENTERPRISES LTD">
                            <div class="form-hint">Name on the account (will be used in ABA file)</div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            These bank details will be used as defaults when generating ABA files in the 
                            <strong>Payroll → ABA Bank File</strong> module. You can still override them during generation.
                        </p>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <a href="{{ route('company-bank-details.index') }}" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Save Bank Details
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection