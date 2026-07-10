@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    <!-- HEADER -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    @if($employee->photo_path)
                        <img src="{{ Storage::url($employee->photo_path) }}" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 56px; height: 56px; font-size: 22px; background: #6366f1;">
                            {{ substr($employee->full_name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0">{{ $employee->full_name }}</h4>
                        <span class="badge bg-success">{{ $employee->status }}</span>
                    </div>
                    <div class="text-muted" style="font-size: 13px;">
                        {{ $employee->employee_number }} | {{ $employee->position->name ?? 'N/A' }} | {{ $employee->company->name ?? 'N/A' }} | {{ $employee->employee_type }} • Age: {{ $employee->age ?? 'N/A' }}
                    </div>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    <button onclick="confirmDelete()" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- STATS -->
    @php
        $serviceDays = $employee->joining_date ? intval($employee->joining_date->diffInDays(now())) : 0;
        $activeLoans = $employee->loans->where('status', '!=', 'Completed')->count();
        $leaveBalance = $employee->leaveRecords->sum('balance') ?? 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-2 me-2"><i class="fas fa-dollar-sign text-primary"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase" style="font-size: 10px;">Hourly Rate</div>
                        <div class="fw-bold">K {{ number_format($employee->hourly_rate ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded p-2 me-2"><i class="fas fa-calendar-day text-success"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase" style="font-size: 10px;">Service Days</div>
                        <div class="fw-bold">{{ number_format($serviceDays) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded p-2 me-2"><i class="fas fa-umbrella-beach text-info"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase" style="font-size: 10px;">Leave Balance</div>
                        <div class="fw-bold">{{ number_format($leaveBalance, 1) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-purple bg-opacity-10 rounded p-2 me-2"><i class="fas fa-hand-holding-usd text-purple"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase" style="font-size: 10px;">Active Loans</div>
                        <div class="fw-bold">{{ $activeLoans }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPIRY NOTIFICATIONS -->
    @if(!empty($expiringDocs))
    <div class="alert alert-warning border-0 py-2 mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Expiring Documents:</strong>
        @foreach($expiringDocs as $type => $date)
            <span class="ms-2">{{ ucfirst(str_replace('_', ' ', $type)) }}: {{ $date->format('d M Y') }}</span>
        @endforeach
    </div>
    @endif

    <!-- ========================================== -->
    <!-- ALL SECTIONS STACKED VERTICALLY - NO COLUMNS -->
    <!-- ========================================== -->

    <!-- 1. Personal Information -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i>Personal Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Full Name</label>
                    <p class="fw-semibold mb-0">{{ $employee->full_name }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Gender</label>
                    <p class="fw-semibold mb-0">{{ $employee->gender ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Date of Birth</label>
                    <p class="fw-semibold mb-0">{{ $employee->date_of_birth?->format('d M Y') ?? 'N/A' }}</p>
                    <small class="text-muted">Age: {{ $employee->age ?? 'N/A' }}</small>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Marital Status</label>
                    <p class="fw-semibold mb-0">{{ $employee->marital_status ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Joining Date</label>
                    <p class="fw-semibold mb-0">{{ $employee->joining_date?->format('d M Y') ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">End Date</label>
                    <p class="fw-semibold mb-0">{{ $employee->end_date?->format('d M Y') ?? 'N/A' }}</p>
                </div>
                @if($employee->employee_type == 'Expatriate')
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Deployment Date</label>
                    <p class="fw-semibold mb-0">{{ $employee->deployment_date?->format('d M Y') ?? 'N/A' }}</p>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Employee Type</label>
                    <p class="fw-semibold mb-0">
                        <span class="badge {{ $employee->employee_type == 'Expatriate' ? 'bg-primary' : 'bg-success' }} text-white">
                            {{ $employee->employee_type }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Company & Department -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0 fw-bold"><i class="fas fa-building me-2"></i>Company & Department</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Company</label>
                    <p class="fw-semibold mb-0">{{ $employee->company->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Department</label>
                    <p class="fw-semibold mb-0">{{ $employee->department->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Position</label>
                    <p class="fw-semibold mb-0">{{ $employee->position->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Fortnight Hours</label>
                    <p class="fw-semibold mb-0">{{ $employee->fortnight_hours ?? 84 }} hrs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Bank Accounts -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light border-bottom d-flex justify-content-between">
            <h6 class="mb-0 fw-bold"><i class="fas fa-university me-2"></i>Bank Accounts</h6>
            <small class="text-muted">{{ $employee->bankAccounts->count() }} account(s)</small>
        </div>
        <div class="card-body">
            @forelse($employee->bankAccounts as $account)
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <div class="fw-semibold">{{ $account->account_name }}</div>
                    <small class="text-muted">{{ $account->bank_name }} • {{ $account->account_number }}</small>
                    <br><small class="text-muted">BSB: {{ $account->bsb_code }}</small>
                </div>
                @if($account->is_preferred)
                    <span class="badge bg-success">Preferred</span>
                @endif
            </div>
            @empty
            <p class="text-muted text-center mb-0">No bank accounts registered</p>
            @endforelse
        </div>
    </div>

    <!-- 4. Loan History -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light border-bottom d-flex justify-content-between">
            <h6 class="mb-0 fw-bold"><i class="fas fa-hand-holding-usd me-2"></i>Loan History</h6>
            <a href="{{ route('loan-requests.create') }}?employee_id={{ $employee->id }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add</a>
        </div>
        <div class="card-body p-0">
            @if($employee->loans->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="font-size: 10px; text-transform: uppercase;">Type</th>
                            <th class="text-end" style="font-size: 10px; text-transform: uppercase;">Amount</th>
                            <th class="text-end" style="font-size: 10px; text-transform: uppercase;">Remaining</th>
                            <th class="text-center" style="font-size: 10px; text-transform: uppercase;">Status</th>
                            <th class="text-center" style="font-size: 10px; text-transform: uppercase;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->loans as $loan)
                        <tr>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $loan->loan_type }}</span></td>
                            <td class="text-end">K {{ number_format($loan->amount, 2) }}</td>
                            <td class="text-end">
                                <span class="{{ $loan->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    K {{ number_format($loan->remaining_balance, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge 
                                    {{ $loan->status == 'Completed' ? 'bg-success' : 
                                       ($loan->status == 'Pending' ? 'bg-warning' : 
                                       ($loan->status == 'Approved' ? 'bg-info' : 'bg-secondary')) }}">
                                    {{ $loan->status }}
                                </span>
                            </td>
                            <td class="text-center small text-muted">{{ $loan->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-muted py-3 mb-0">No loans recorded</p>
            @endif
        </div>
    </div>

    <!-- 5. NASFUND Details -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0 fw-bold"><i class="fas fa-piggy-bank me-2"></i>NASFUND Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">NASFUND Number</label>
                    <p class="fw-semibold mb-0">{{ $employee->nasfund_number ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Dependents</label>
                    <p class="fw-semibold mb-0">{{ $employee->nasfund_dependents ?? 0 }}</p>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Allocation %</label>
                    <p class="fw-semibold mb-0">{{ $employee->nasfund_allocation_percentage ?? 0 }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Payroll Information -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0 fw-bold"><i class="fas fa-wallet me-2"></i>Payroll Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Hourly Rate</label>
                    <p class="fw-bold text-primary" style="font-size: 18px;">K {{ number_format($employee->hourly_rate ?? 0, 2) }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Monthly Salary</label>
                    <p class="fw-semibold mb-0">K {{ number_format($employee->monthly_salary ?? 0, 2) }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Base Salary</label>
                    <p class="fw-semibold mb-0">K {{ number_format($employee->base_salary ?? 0, 2) }}</p>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Payment Method</label>
                    <span class="badge {{ $employee->payment_method == 'Bank Transfer' ? 'bg-info' : 'bg-warning' }}">{{ $employee->payment_method ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. Expatriate Documents (only if Expatriate) -->
    @if($employee->employee_type == 'Expatriate')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light border-bottom">
            <h6 class="mb-0 fw-bold"><i class="fas fa-passport me-2"></i>Expatriate Documents</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Passport</label>
                    <p class="fw-semibold mb-0">{{ $employee->passport_number ?? 'N/A' }}</p>
                    @if($employee->passport_expiry)
                        <small class="text-muted">Expires: {{ $employee->passport_expiry->format('d M Y') }}</small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Work Permit</label>
                    <p class="fw-semibold mb-0">{{ $employee->work_permit_number ?? 'N/A' }}</p>
                    @if($employee->work_permit_expiry)
                        <small class="text-muted">Expires: {{ $employee->work_permit_expiry->format('d M Y') }}</small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="text-muted small text-uppercase" style="font-size: 10px;">Visa</label>
                    <p class="fw-semibold mb-0">{{ $employee->visa_number ?? 'N/A' }}</p>
                    @if($employee->visa_expiry)
                        <small class="text-muted">Expires: {{ $employee->visa_expiry->format('d M Y') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Delete Employee</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Delete <strong>{{ $employee->full_name }}</strong>?</p>
                <small class="text-danger">This cannot be undone.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('employees.destroy', $employee) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete() {
        var myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        myModal.show();
    }
</script>
@endpush

@push('styles')
<style>
    .bg-purple { background-color: #7c3aed; }
    .text-purple { color: #7c3aed; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .bg-primary.bg-opacity-10 { background-color: rgba(99, 102, 241, 0.1) !important; }
    .bg-success.bg-opacity-10 { background-color: rgba(22, 163, 74, 0.1) !important; }
    .bg-info.bg-opacity-10 { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-purple.bg-opacity-10 { background-color: rgba(124, 58, 237, 0.1) !important; }
    .text-primary { color: #6366f1 !important; }
    .btn-outline-primary { color: #6366f1; border-color: #6366f1; }
    .btn-outline-primary:hover { background-color: #6366f1; color: white; }
    
    .card {
        border-radius: 8px !important;
        border: 1px solid #e5e7eb !important;
    }
    .card-header {
        background-color: #f8fafc !important;
        padding: 10px 20px !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
    .card-body {
        padding: 20px !important;
    }
</style>
@endpush
@endsection