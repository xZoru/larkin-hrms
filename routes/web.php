<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\ABAGeneratorController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\TaxTableController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\CompanyBankDetailsController;

// OR if using the route group:

Route::middleware(['auth'])->prefix('tax-tables')->name('tax-tables.')->group(function () {
    Route::get('/', [TaxTableController::class, 'index'])->name('index');
    // ... etc
});



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// ABA Routes
Route::prefix('aba')->name('aba.')->middleware(['auth'])->group(function () {
    Route::get('/', [ABAGeneratorController::class, 'index'])->name('index');
    Route::post('/generate', [ABAGeneratorController::class, 'generate'])->name('generate');
    Route::get('/show/{id}', [ABAGeneratorController::class, 'show'])->name('show');
    Route::get('/download/{id}', [ABAGeneratorController::class, 'download'])->name('download');
    Route::get('/history', [ABAGeneratorController::class, 'history'])->name('history');
    Route::get('/preview/{id}', [ABAGeneratorController::class, 'preview'])->name('preview');
    Route::delete('/delete/{id}', [ABAGeneratorController::class, 'destroy'])->name('destroy');
    Route::post('/save-manual-entries', [ABAGeneratorController::class, 'saveManualEntries'])->name('save.manual.entries'); //newly added route for saving manual entries
    Route::delete('/delete-manual-entry/{payrollItemId}', [ABAGeneratorController::class, 'deleteManualEntry'])->name('delete.manual.entry');
        // newly added route for previewing payroll before generating ABA file
    Route::get('/preview-payroll/{payrollId}', [ABAGeneratorController::class, 'previewPayroll'])->name('preview.payroll');
    
    Route::get('/api/payrolls-by-company', [ABAGeneratorController::class, 'getPayrollsByCompany'])->name('api.payrolls.by-company');
    Route::get('/export-excel/{id}', [ABAGeneratorController::class, 'exportExcel'])->name('export.excel');
    
    
    Route::get('/api/payrolls-by-company', [ABAGeneratorController::class, 'getPayrollsByCompany'])->name('api.payrolls.by-company');
    
    
    Route::get('/export-excel/{id}', [ABAGeneratorController::class, 'exportExcel'])->name('export.excel');
});
// ============ EMPLOYEE ROUTES ============
Route::middleware(['auth', 'company.access'])->group(function () {
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/documents', [EmployeeController::class, 'uploadDocument'])->name('employees.upload-document');
    Route::get('employees/expiring-documents', [EmployeeController::class, 'getExpiringDocuments'])->name('employees.expiring-documents');
    Route::delete('employees/{employee}/documents/{document}', [EmployeeController::class, 'destroyDocument'])
    ->name('employees.document.destroy');
});

// ============ API ROUTES ============
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ============ COMPANY ROUTES ============
Route::post('/company/switch/{company}', [CompanyController::class, 'switch'])
    ->middleware(['auth'])
    ->name('company.switch');

// ============ ATTENDANCE ROUTES ============
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/bulk-update', [AttendanceController::class, 'bulkUpdate'])->name('attendance.bulk.update');
    Route::delete('/attendance/{log}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    Route::get('/employees/{employee}/attendance', [AttendanceController::class, 'show'])->name('employees.attendance');
});

// ============ PAYROLL ROUTES ============
Route::middleware(['auth'])->group(function () {
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('/payroll', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('/payroll/summary', [PayrollController::class, 'summary'])->name('payroll.summary');
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::get('/payroll/{payroll}/export-aba', [PayrollController::class, 'exportABA'])->name('payroll.export-aba');
    Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
});

// ============ LOAN REQUESTS ROUTES ============
Route::middleware(['auth'])->group(function () {
    // Main CRUD routes
    Route::get('/loan-requests', [LoanRequestController::class, 'index'])->name('loan-requests.index');
    Route::get('/loan-requests/create', [LoanRequestController::class, 'create'])->name('loan-requests.create');
    Route::post('/loan-requests', [LoanRequestController::class, 'store'])->name('loan-requests.store');
    Route::get('/loan-requests/{loanRequest}', [LoanRequestController::class, 'show'])->name('loan-requests.show');
    Route::get('/loan-requests/{loanRequest}/edit', [LoanRequestController::class, 'edit'])->name('loan-requests.edit');
    Route::put('/loan-requests/{loanRequest}', [LoanRequestController::class, 'update'])->name('loan-requests.update');
    Route::delete('/loan-requests/{loanRequest}', [LoanRequestController::class, 'destroy'])->name('loan-requests.destroy');
    
    // Status update routes
    Route::post('/loan-requests/{loanRequest}/approve', [LoanRequestController::class, 'approve'])->name('loan-requests.approve');
    Route::post('/loan-requests/{loanRequest}/release', [LoanRequestController::class, 'release'])->name('loan-requests.release');
    Route::post('/loan-requests/{loanRequest}/reject', [LoanRequestController::class, 'reject'])->name('loan-requests.reject');
    Route::post('/loan-requests/{loanRequest}/hold', [LoanRequestController::class, 'hold'])->name('loan-requests.hold');
    
    // Payment routes
    Route::get('/loan-requests/{loanRequest}/payments', [LoanRequestController::class, 'paymentHistory'])->name('loan-requests.payments');
    Route::post('/loan-requests/{loanRequest}/payment', [LoanRequestController::class, 'addManualPayment'])->name('loan-requests.add-payment');
    
    // Bulk operation
    Route::post('/loan-requests/bulk', [LoanRequestController::class, 'bulkStore'])->name('loan-requests.bulk-store');
    
    // Employee specific
    Route::get('/my-loans', [LoanRequestController::class, 'myLoans'])->name('loan-requests.my-loans');
});

// ============ API ROUTES FOR AJAX ============
Route::middleware(['auth'])->group(function () {
    Route::get('/api/employees/search', [LoanRequestController::class, 'searchEmployees'])->name('api.employees.search');
    Route::get('/api/employees/{employee}/loans', [LoanRequestController::class, 'employeeLoans'])->name('api.employee.loans');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
});

Route::middleware(['auth'])->prefix('tax-tables')->name('tax-tables.')->group(function () {
    Route::get('/', [TaxTableController::class, 'index'])->name('index');
    Route::get('/create', [TaxTableController::class, 'create'])->name('create');
    Route::post('/', [TaxTableController::class, 'store'])->name('store');
    Route::get('/{taxTable}/edit', [TaxTableController::class, 'edit'])->name('edit');
    Route::put('/{taxTable}', [TaxTableController::class, 'update'])->name('update');
    Route::delete('/{taxTable}', [TaxTableController::class, 'destroy'])->name('destroy');
    Route::post('/{taxTable}/toggle', [TaxTableController::class, 'toggle'])->name('toggle');
});

// ============ HOLIDAYS ROUTES ============
Route::middleware(['auth'])->prefix('holidays')->name('holidays.')->group(function () {
    Route::get('/', [HolidayController::class, 'index'])->name('index');
    Route::get('/create', [HolidayController::class, 'create'])->name('create');
    Route::post('/', [HolidayController::class, 'store'])->name('store');
    Route::get('/{holiday}/edit', [HolidayController::class, 'edit'])->name('edit');
    Route::put('/{holiday}', [HolidayController::class, 'update'])->name('update');
    Route::delete('/{holiday}', [HolidayController::class, 'destroy'])->name('destroy');
    Route::post('/{holiday}/toggle', [HolidayController::class, 'toggle'])->name('toggle');
});

// ============ COMPANY BANK DETAILS ROUTES ============
Route::middleware(['auth'])->prefix('company-bank-details')->name('company-bank-details.')->group(function () {
    Route::get('/', [CompanyBankDetailsController::class, 'index'])->name('index');
    Route::get('/{company}/edit', [CompanyBankDetailsController::class, 'edit'])->name('edit');
    Route::put('/{company}', [CompanyBankDetailsController::class, 'update'])->name('update');
});



require __DIR__.'/auth.php';