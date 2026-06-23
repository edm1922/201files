<?php

use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\BankTypeController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentDocumentController;
use App\Http\Controllers\DocumentLocationController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSearchController;
use App\Http\Controllers\FolderLocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/force-password-change', [ForcePasswordChangeController::class, 'store'])->name('password.force-change.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
    });

    // ── Admin Settings ──
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::resource('companies', CompanyController::class)->except(['show']);
        Route::patch('companies/{company}/toggle-active', [CompanyController::class, 'toggleActive'])
            ->name('companies.toggle-active');

        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::patch('departments/{department}/toggle-active', [DepartmentController::class, 'toggleActive'])
            ->name('departments.toggle-active');

        Route::resource('bank-types', BankTypeController::class)->except(['show']);
        Route::patch('bank-types/{bankType}/toggle-active', [BankTypeController::class, 'toggleActive'])
            ->name('bank-types.toggle-active');

        // Folder Locations (Physical Storage)
        Route::prefix('folder-locations')->name('folder-locations.')->group(function () {
            Route::get('/', [FolderLocationController::class, 'index'])->name('index');
            Route::post('/row', [FolderLocationController::class, 'storeRow'])->name('store-row');
            Route::put('/{folderLocation}', [FolderLocationController::class, 'update'])->name('update');
            Route::delete('/{folderLocation}', [FolderLocationController::class, 'destroy'])->name('destroy');
        });

        // Document Locations (Department Documents)
        Route::prefix('document-locations')->name('document-locations.')->group(function () {
            Route::get('/', [DocumentLocationController::class, 'index'])->name('index');
            Route::post('/', [DocumentLocationController::class, 'store'])->name('store');
            Route::put('/{documentLocation}', [DocumentLocationController::class, 'update'])->name('update');
            Route::delete('/{documentLocation}', [DocumentLocationController::class, 'destroy'])->name('destroy');
        });

        Route::resource('document-types', DocumentTypeController::class)->except(['show', 'create', 'edit']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        Route::resource('users', UserController::class)->except(['show']);
    });

    // ── 201 Files / Employee Profile Hub ──
    // NOTE: meili-search must be defined BEFORE {employee} wildcard route
    Route::get('/employees/meili-search', [EmployeeSearchController::class, 'meiliSearch'])
        ->name('employees.meiliSearch');

    Route::get('/employees/{id}/update-history', [EmployeeController::class, 'updateHistory'])
        ->name('employees.update-history');

    // ── Archive (admin only) ──
    Route::middleware('role:admin')->group(function () {
        Route::get('/archives', [ArchiveController::class, 'index'])
            ->name('archives.index');
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
        Route::get('/reports/audit-log', [ActivityLogController::class, 'index'])
            ->name('reports.audit-log');

        // Reports & Exporting
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/generate', [ReportController::class, 'index'])->name('generate');
            Route::get('/export/employees', [ReportController::class, 'exportEmployees'])->name('export-employees');
            Route::get('/export/audit-logs', [ReportController::class, 'exportAuditLogs'])->name('export-audit-logs');
            Route::get('/export/company-summary', [ReportController::class, 'exportCompanySummary'])->name('export-company-summary');
            Route::get('/export/storage-utilization', [ReportController::class, 'exportStorageUtilization'])->name('export-storage-utilization');
            Route::get('/export/available-folders', [ReportController::class, 'exportAvailableFolders'])->name('export-available-folders');

            Route::get('/export/expiry-report', [ReportController::class, 'exportExpiryReport'])->name('export-expiry-report');
        });
    });

    Route::get('/201files', [EmployeeController::class, 'create'])->name('201files');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::post('/employees/import-excel', [EmployeeController::class, 'importExcel'])
        ->name('employees.import-excel')
        ->middleware('role:admin,encoder');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');

    Route::prefix('department-documents')->name('department-documents.')->group(function () {
        Route::get('/', [DepartmentDocumentController::class, 'index'])
            ->middleware('can:viewAny,App\Models\Document')
            ->name('index');

        Route::get('/search', [DepartmentDocumentController::class, 'search'])
            ->middleware('can:viewAny,App\Models\Document')
            ->name('search');

        Route::post('/folders', [DepartmentDocumentController::class, 'storeFolder'])
            ->middleware(['role:admin,encoder', 'can:create,App\Models\Document'])
            ->name('folders.store');

        Route::patch('/folders/{folder}', [DepartmentDocumentController::class, 'updateFolder'])
            ->middleware(['role:admin', 'can:create,App\Models\Document'])
            ->name('folders.update');

        Route::delete('/folders/{folder}', [DepartmentDocumentController::class, 'destroyFolder'])
            ->middleware(['role:admin', 'can:create,App\Models\Document'])
            ->name('folders.destroy');

        Route::get('/root-update-history', [DepartmentDocumentController::class, 'rootUpdateHistory'])
            ->middleware(['role:admin'])
            ->name('root-update-history');

        Route::get('/folders/{folder}/update-history', [DepartmentDocumentController::class, 'folderUpdateHistory'])
            ->middleware(['role:admin,encoder', 'can:create,App\Models\Document'])
            ->name('folders.update-history');

        Route::post('/', [DepartmentDocumentController::class, 'store'])
            ->middleware(['role:admin,encoder', 'can:create,App\Models\Document'])
            ->name('store');

        Route::patch('/{document}/archive', [DepartmentDocumentController::class, 'archive'])
            ->middleware(['role:admin,encoder', 'can:archive,document'])
            ->name('archive');

        Route::patch('/{id}/restore', [DepartmentDocumentController::class, 'restore'])
            ->middleware('role:admin')
            ->name('restore');

        Route::delete('/{id}/force-delete', [DepartmentDocumentController::class, 'forceDelete'])
            ->middleware('role:admin')
            ->name('forceDelete');

        Route::get('/{document}/preview', [DepartmentDocumentController::class, 'preview'])
            ->middleware('can:download,document')
            ->name('preview')
            ->withTrashed();

        Route::get('/{document}/download', [DepartmentDocumentController::class, 'download'])
            ->middleware('can:download,document')
            ->name('download')
            ->withTrashed();

        Route::patch('/{document}', [DepartmentDocumentController::class, 'update'])
            ->middleware('role:admin,encoder')
            ->name('update');

        Route::get('/{document}/update-history', [DepartmentDocumentController::class, 'updateHistory'])
            ->middleware('can:download,document')
            ->name('update-history')
            ->withTrashed();
    });
    Route::get('/about', function () {
        $totalEmployees = \App\Models\Employee::count();
        $totalDocuments = \App\Models\Document::count();
        $totalCompanies = \App\Models\Company::where('is_active', true)->count();

        return view('about', compact('totalEmployees', 'totalDocuments', 'totalCompanies'));
    })->name('about');
});

require __DIR__.'/auth.php';
