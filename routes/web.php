<?php

use App\Http\Controllers\CabinetController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentTypeController;
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

        // Cabinets & Racks (replaces physical-locations)
        Route::get('cabinets', [CabinetController::class, 'index'])->name('cabinets.index');
        Route::post('cabinets/racks', [CabinetController::class, 'storeRack'])->name('cabinets.store-rack');
        Route::put('cabinets/racks/{rack}', [CabinetController::class, 'updateRack'])->name('cabinets.update-rack');
        Route::delete('cabinets/racks/{rack}', [CabinetController::class, 'destroyRack'])->name('cabinets.destroy-rack');

        Route::resource('document-types', DocumentTypeController::class)->except(['show', 'create', 'edit']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
             ->name('users.reset-password');
        Route::resource('users', UserController::class)->except(['show']);
    });

    // ── 201 Files / Employee Profile Hub ──
    // NOTE: milli-search must be defined BEFORE {employee} wildcard route
    Route::get('/employees/milli-search', [EmployeeSearchController::class, 'milliSearch'])
        ->name('employees.milliSearch');

    Route::get('/201files',              [EmployeeController::class, 'create'])->name('201files');
    Route::post('/employees',            [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}',  [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}',  [EmployeeController::class, 'update'])->name('employees.update');

    // ── Archive (admin only) ──
    Route::middleware('role:admin')->group(function () {
        Route::get('/employees/archive', [EmployeeController::class, 'archiveIndex'])
            ->name('employees.archive');
        Route::patch('/employees/{employee}/restore', [EmployeeController::class, 'restore'])
            ->name('employees.restore');
        Route::delete('/employees/{employee}/force-delete', [EmployeeController::class, 'forceDestroy'])
            ->name('employees.forceDestroy');
    });
});

require __DIR__.'/auth.php';
