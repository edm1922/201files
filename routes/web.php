<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSearchController;
use App\Http\Controllers\PhysicalLocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/201files', function () {
        return view('201files');
    })->name('201files');

    // ── Admin Settings ──
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::resource('companies', CompanyController::class)->except(['show']);
        Route::patch('companies/{company}/toggle-active', [CompanyController::class, 'toggleActive'])
             ->name('companies.toggle-active');
             
        Route::resource('departments', DepartmentController::class)->except(['show', 'destroy']);
        Route::resource('physical-locations', PhysicalLocationController::class)->except(['show', 'destroy']);
        Route::resource('document-types', DocumentTypeController::class)->except(['show', 'destroy']);
    });
    // ── 201 Files / Employee Profile Hub ──
    // NOTE: milli-search must be defined BEFORE {employee} wildcard route
    Route::get('/employees/milli-search', [EmployeeSearchController::class, 'milliSearch'])
        ->name('employees.milliSearch');

    Route::get('/201files',              [EmployeeController::class, 'create'])->name('201files');
    Route::post('/employees',            [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}',  [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}',  [EmployeeController::class, 'update'])->name('employees.update');
});

require __DIR__.'/auth.php';
