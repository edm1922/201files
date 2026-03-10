<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSearchController;
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
