<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\Folder;
use App\Models\User;

beforeEach(function () {
    Company::query()->create(['id' => 1, 'name' => 'Fallback Co', 'code' => 'DFT']);
});

test('new employee auto-assigns next number even when available folders exist', function () {
    $user = User::factory()->admin()->create();
    $company = Company::query()->create(['name' => 'Human Resources', 'code' => 'HR']);

    Folder::query()->create([
        'company_id' => $company->id,
        'sequence_number' => 7,
        'folder_code' => 'CSC-HR-0007',
        'is_available' => true,
    ]);

    $response = $this->actingAs($user)->post(route('employees.store'), [
        'system_id' => 'SYS-REUSE-001',
        'first_name' => 'JUAN',
        'middle_name' => 'D',
        'last_name' => 'DELA CRUZ',
        'status' => 'active',
        'company_id' => $company->id,
    ]);

    $response->assertRedirect();

    $employee = Employee::query()->where('system_id', 'SYS-REUSE-001')->firstOrFail();
    $folder = $employee->folder()->firstOrFail();

    expect($folder->folder_code)->toBe('CSC-HR-0008');
    expect($folder->is_available)->toBeFalse();
});

test('next employee gets newly created code when no reusable folder exists', function () {
    $user = User::factory()->admin()->create();
    $company = Company::query()->create(['name' => 'Operations', 'code' => 'OPS']);

    $response = $this->actingAs($user)->post(route('employees.store'), [
        'system_id' => 'SYS-NEXT-001',
        'first_name' => 'MARIA',
        'middle_name' => 'L',
        'last_name' => 'SANTOS',
        'status' => 'active',
        'company_id' => $company->id,
    ]);

    $response->assertRedirect();

    $employee = Employee::query()->where('system_id', 'SYS-NEXT-001')->firstOrFail();
    $folder = $employee->folder()->firstOrFail();

    expect($folder->folder_code)->toBe('CSC-OPS-0001');
    expect($folder->is_available)->toBeFalse();

    $response2 = $this->actingAs($user)->post(route('employees.store'), [
        'system_id' => 'SYS-NEXT-002',
        'first_name' => 'PEDRO',
        'middle_name' => 'Q',
        'last_name' => 'REYES',
        'status' => 'active',
        'company_id' => $company->id,
    ]);

    $response2->assertRedirect();

    $employee2 = Employee::query()->where('system_id', 'SYS-NEXT-002')->firstOrFail();
    $folder2 = $employee2->folder()->firstOrFail();

    expect($folder2->folder_code)->toBe('CSC-OPS-0002');
    expect((int) $folder2->sequence_number)->toBe(2);
});

test('employee can choose a specific available folder code', function () {
    $user = User::factory()->admin()->create();
    $company = Company::query()->create(['name' => 'Accounting', 'code' => 'ACC']);

    Folder::query()->create([
        'company_id' => $company->id,
        'sequence_number' => 2,
        'folder_code' => 'CSC-ACC-0002',
        'is_available' => true,
    ]);

    $selectedFolder = Folder::query()->create([
        'company_id' => $company->id,
        'sequence_number' => 9,
        'folder_code' => 'CSC-ACC-0009',
        'is_available' => true,
    ]);

    $response = $this->actingAs($user)->post(route('employees.store'), [
        'system_id' => 'SYS-SELECT-001',
        'first_name' => 'LIZA',
        'middle_name' => 'M',
        'last_name' => 'GONZALES',
        'status' => 'active',
        'company_id' => $company->id,
        'folder_id' => $selectedFolder->id,
    ]);

    $response->assertRedirect();

    $employee = Employee::query()->where('system_id', 'SYS-SELECT-001')->firstOrFail();
    expect((int) $employee->folder_id)->toBe((int) $selectedFolder->id);

    $selectedFolder->refresh();
    expect($selectedFolder->is_available)->toBeFalse();
    expect($selectedFolder->folder_code)->toBe('CSC-ACC-0009');
});
