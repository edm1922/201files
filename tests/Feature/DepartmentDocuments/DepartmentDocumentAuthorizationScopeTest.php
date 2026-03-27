<?php

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('returns false when viewer is outside assigned department scope', function () {
    $departmentA = Department::create([
        'name' => 'Scope A',
        'code' => 'SCA',
        'folder_code' => 'CSC-SCA-0000',
        'description' => 'Scope A',
        'is_active' => true,
    ]);

    $departmentB = Department::create([
        'name' => 'Scope B',
        'code' => 'SCB',
        'folder_code' => 'CSC-SCB-0000',
        'description' => 'Scope B',
        'is_active' => true,
    ]);

    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->attach($departmentA->id);

    expect($viewer->canAccessDepartment($departmentB->id))->toBeFalse();
});

it('returns true when viewer is inside assigned department scope', function () {
    $department = Department::create([
        'name' => 'Scope C',
        'code' => 'SCC',
        'folder_code' => 'CSC-SCC-0000',
        'description' => 'Scope C',
        'is_active' => true,
    ]);

    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->attach($department->id);

    expect($viewer->canAccessDepartment($department->id))->toBeTrue();
});

it('policy allows download inside assigned department scope', function () {
    $department = Department::create([
        'name' => 'Scope Policy In',
        'code' => 'SPI',
        'folder_code' => 'CSC-SPI-0000',
        'description' => 'Scope Policy In',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Policy Type In',
        'code' => 'PTIN',
        'has_expiry' => false,
        'max_pages' => 1,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'C',
        'column_code' => '1',
    ]);

    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->attach($department->id);

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'uploaded_by' => $viewer->id,
        'file_path' => 'documents/departments/' . $department->id . '/DEPT-' . $department->id . '_PTIN_20260324.pdf',
        'original_filename' => 'policy-in.pdf',
        'system_filename' => 'DEPT-' . $department->id . '_PTIN_20260324.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'status' => 'active',
        'date_received' => now()->toDateString(),
    ]);

    expect(Gate::forUser($viewer)->allows('download', $document))->toBeTrue();
});

it('policy blocks download outside assigned department scope', function () {
    $departmentA = Department::create([
        'name' => 'Scope Policy Out A',
        'code' => 'SPOA',
        'folder_code' => 'CSC-SPOA-0000',
        'description' => 'Scope Policy Out A',
        'is_active' => true,
    ]);

    $departmentB = Department::create([
        'name' => 'Scope Policy Out B',
        'code' => 'SPOB',
        'folder_code' => 'CSC-SPOB-0000',
        'description' => 'Scope Policy Out B',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $departmentB->id,
        'name' => 'Policy Type Out',
        'code' => 'PTOUT',
        'has_expiry' => false,
        'max_pages' => 1,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'D',
        'column_code' => '1',
    ]);

    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->attach($departmentA->id);

    $document = Document::create([
        'department_id' => $departmentB->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'uploaded_by' => $viewer->id,
        'file_path' => 'documents/departments/' . $departmentB->id . '/DEPT-' . $departmentB->id . '_PTOUT_20260324.pdf',
        'original_filename' => 'policy-out.pdf',
        'system_filename' => 'DEPT-' . $departmentB->id . '_PTOUT_20260324.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'status' => 'active',
        'date_received' => now()->toDateString(),
    ]);

    expect(Gate::forUser($viewer)->allows('download', $document))->toBeFalse();
});

it('blocks access to documents outside authorized departments', function () {
    $departmentA = Department::create([
        'name' => 'Scope D',
        'code' => 'SCD',
        'folder_code' => 'CSC-SCD-0000',
        'description' => 'Scope D',
        'is_active' => true,
    ]);

    $departmentB = Department::create([
        'name' => 'Scope E',
        'code' => 'SCE',
        'folder_code' => 'CSC-SCE-0000',
        'description' => 'Scope E',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $departmentB->id,
        'name' => 'Scoped Type',
        'code' => 'SCTYPE',
        'has_expiry' => false,
        'max_pages' => 1,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'C',
        'column_code' => '1',
    ]);

    $viewer = User::factory()->viewer()->create();

    $document = Document::create([
        'department_id' => $departmentB->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'uploaded_by' => $viewer->id,
        'file_path' => 'documents/departments/' . $departmentB->id . '/DEPT-' . $departmentB->id . '_SCTYPE_20260324.pdf',
        'original_filename' => 'scoped.pdf',
        'system_filename' => 'DEPT-' . $departmentB->id . '_SCTYPE_20260324.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'status' => 'active',
        'date_received' => now()->toDateString(),
    ]);

    $this->actingAs($viewer)
        ->get(route('department-documents.download', $document))
        ->assertStatus(403);
});
