<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FolderLocationController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DepartmentDocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/force-password-change', [ForcePasswordChangeController::class, 'store'])->name('password.force-change.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Admin Settings ──
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::resource('companies', CompanyController::class)->except(['show']);
        Route::patch('companies/{company}/toggle-active', [CompanyController::class, 'toggleActive'])
             ->name('companies.toggle-active');
             
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::patch('departments/{department}/toggle-active', [DepartmentController::class, 'toggleActive'])
             ->name('departments.toggle-active');

        // Folder Locations (Physical Storage)
        Route::prefix('folder-locations')->name('folder-locations.')->group(function () {
            Route::get('/', [FolderLocationController::class, 'index'])->name('index');
            Route::post('/row', [FolderLocationController::class, 'storeRow'])->name('store-row');
            Route::post('/row/{row_name}/column', [FolderLocationController::class, 'storeColumn'])->name('store-column');
            Route::delete('/row/{row_name}/column/{column_code}', [FolderLocationController::class, 'destroyColumn'])->name('destroy-column');
            Route::delete('/row/{row_name}', [FolderLocationController::class, 'destroyRow'])->name('destroy-row');
            Route::put('/{folderLocation}', [FolderLocationController::class, 'update'])->name('update');
            Route::delete('/{folderLocation}', [FolderLocationController::class, 'destroy'])->name('destroy');
        });

        Route::resource('document-types', DocumentTypeController::class)->except(['show', 'create', 'edit']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
             ->name('users.reset-password');
        Route::resource('users', UserController::class)->except(['show']);
    });

    // ── 201 Files / Employee Profile Hub ──
    // NOTE: milli-search must be defined BEFORE {employee} wildcard route
    Route::get('/employees/milli-search', [EmployeeSearchController::class, 'milliSearch'])
        ->name('employees.milliSearch');

    Route::get('/employees/{id}/update-history', [EmployeeController::class, 'updateHistory'])
        ->name('employees.update-history');

    // ── Archive (admin and encoder) ──
    Route::middleware('role:admin,encoder')->group(function () {
        Route::get('/employees/archive', [EmployeeController::class, 'archiveIndex'])
            ->name('employees.archive');
        Route::get('/employees/{id}/details', [EmployeeController::class, 'details'])
            ->name('employees.details');
        Route::patch('/employees/{id}/restore', [EmployeeController::class, 'restore'])
            ->name('employees.restore');
    });

    // ── Admin Only ──
    Route::middleware('role:admin')->group(function () {
        Route::delete('/employees/{id}/force-delete', [EmployeeController::class, 'forceDestroy'])
            ->name('employees.forceDestroy');

        // Audit & Activity Logs
        Route::get('/reports/audit-log', [\App\Http\Controllers\ActivityLogController::class, 'index'])
            ->name('reports.audit-log');

        // Reports & Exporting
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/generate', [\App\Http\Controllers\ReportController::class, 'index'])->name('generate');
            Route::get('/export/employees', [\App\Http\Controllers\ReportController::class, 'exportEmployees'])->name('export-employees');
            Route::get('/export/audit-logs', [\App\Http\Controllers\ReportController::class, 'exportAuditLogs'])->name('export-audit-logs');
            Route::get('/export/company-summary', [\App\Http\Controllers\ReportController::class, 'exportCompanySummary'])->name('export-company-summary');
            Route::get('/export/storage-utilization', [\App\Http\Controllers\ReportController::class, 'exportStorageUtilization'])->name('export-storage-utilization');
            Route::get('/export/available-folders', [\App\Http\Controllers\ReportController::class, 'exportAvailableFolders'])->name('export-available-folders');
        });
    });

    Route::get('/201files',              [EmployeeController::class, 'create'])->name('201files');
    Route::post('/employees',            [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}',  [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}',  [EmployeeController::class, 'update'])->name('employees.update');

    Route::prefix('department-documents')->name('department-documents.')->group(function () {
        Route::get('/', [DepartmentDocumentController::class, 'index'])
            ->middleware('can:viewAny,App\Models\Document')
            ->name('index');

        Route::post('/folders', [DepartmentDocumentController::class, 'storeFolder'])
            ->middleware(['role:admin,encoder', 'can:create,App\Models\Document'])
            ->name('folders.store');

        Route::post('/', [DepartmentDocumentController::class, 'store'])
            ->middleware(['role:admin,encoder', 'can:create,App\Models\Document'])
            ->name('store');

        Route::patch('/{document}/archive', [DepartmentDocumentController::class, 'archive'])
            ->middleware(['role:admin,encoder', 'can:archive,document'])
            ->name('archive');

        Route::patch('/{id}/restore', [DepartmentDocumentController::class, 'restore'])
            ->middleware('role:admin')
            ->name('restore');

        Route::get('/{document}/download', [DepartmentDocumentController::class, 'download'])
            ->middleware('can:download,document')
            ->name('download');
    });
});

require __DIR__.'/auth.php';
