<?php

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Models\User;

it('allows admin and encoder to upload route but blocks viewer', function () {
    $admin = User::factory()->admin()->create();
    $encoder = User::factory()->encoder()->create();
    $viewer = User::factory()->viewer()->create();

    $this->actingAs($admin)
        ->post(route('department-documents.store'), [])
        ->assertStatus(302);

    $this->actingAs($encoder)
        ->post(route('department-documents.store'), [])
        ->assertStatus(302);

    $this->actingAs($viewer)
        ->post(route('department-documents.store'), [])
        ->assertStatus(403);
});

it('allows authenticated users to access explorer index route', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('department-documents.index'))
        ->assertOk();
});

it('scopes index data and listing to the viewer authorized departments', function () {
    $departmentA = Department::create([
        'name' => 'Accounting',
        'code' => 'ACC',
        'folder_code' => 'CSC-ACC-0000',
        'description' => 'Accounting',
        'is_active' => true,
    ]);

    $departmentB = Department::create([
        'name' => 'HR',
        'code' => 'HRD',
        'folder_code' => 'CSC-HRD-0000',
        'description' => 'Human Resources',
        'is_active' => true,
    ]);

    $typeA = DocumentType::create([
        'department_id' => $departmentA->id,
        'name' => 'Accounting Voucher',
        'code' => 'ACCVCH',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $typeB = DocumentType::create([
        'department_id' => $departmentB->id,
        'name' => 'HR Clearance',
        'code' => 'HRCLR',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $folderA = DocumentFolder::create([
        'department_id' => $departmentA->id,
        'parent_id' => null,
        'name' => 'Accounting 2026',
    ]);

    $folderB = DocumentFolder::create([
        'department_id' => $departmentB->id,
        'parent_id' => null,
        'name' => 'HR 2026',
    ]);

    $location = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'Z',
        'column_code' => '9',
    ]);

    $adminUploader = User::factory()->admin()->create();

    Document::create([
        'department_id' => $departmentA->id,
        'document_type_id' => $typeA->id,
        'folder_location_id' => $location->id,
        'document_folder_id' => $folderA->id,
        'uploaded_by' => $adminUploader->id,
        'file_path' => 'documents/departments/' . $departmentA->id . '/acc.pdf',
        'original_filename' => 'accounting.pdf',
        'system_filename' => 'acc.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['accounting.pdf'],
    ]);

    Document::create([
        'department_id' => $departmentB->id,
        'document_type_id' => $typeB->id,
        'folder_location_id' => $location->id,
        'document_folder_id' => $folderB->id,
        'uploaded_by' => $adminUploader->id,
        'file_path' => 'documents/departments/' . $departmentB->id . '/hr.pdf',
        'original_filename' => 'hr.pdf',
        'system_filename' => 'hr.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['hr.pdf'],
    ]);

    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->sync([$departmentA->id]);

    $response = $this->actingAs($viewer)->get(route('department-documents.index'));

    $response->assertOk();
    $response->assertSeeText('Accounting');
    $response->assertDontSeeText('Human Resources');
    $response->assertSee('Accounting Voucher');
    $response->assertDontSee('HR Clearance');
    $response->assertSee('Accounting 2026');
    $response->assertDontSee('HR 2026');
    $response->assertSee('accounting.pdf');
    $response->assertDontSee('hr.pdf');
});

it('allows authorized encoder to create folder inside current department context', function () {
    $department = Department::create([
        'name' => 'Folder Dept',
        'code' => 'FLD',
        'folder_code' => 'CSC-FLD-0000',
        'description' => 'Folder Department',
        'is_active' => true,
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)->post(route('department-documents.folders.store'), [
        'department_id' => $department->id,
        'name' => 'Policies',
        'folder_code' => 'FLD-POL-001',
    ]);

    $folder = DocumentFolder::query()->where('department_id', $department->id)->where('name', 'Policies')->first();

    expect($folder)->not->toBeNull();

    $response->assertRedirect(route('department-documents.index', [
        'department_id' => $department->id,
        'document_folder_id' => $folder->id,
    ]));
});

it('blocks folder creation when parent folder belongs to another department', function () {
    $departmentA = Department::create([
        'name' => 'Dept A Parent',
        'code' => 'DPA2',
        'folder_code' => 'CSC-DPA2-0000',
        'description' => 'Department A Parent',
        'is_active' => true,
    ]);

    $departmentB = Department::create([
        'name' => 'Dept B Parent',
        'code' => 'DPB2',
        'folder_code' => 'CSC-DPB2-0000',
        'description' => 'Department B Parent',
        'is_active' => true,
    ]);

    $foreignParent = DocumentFolder::create([
        'department_id' => $departmentB->id,
        'parent_id' => null,
        'name' => 'Foreign Parent',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$departmentA->id]);

    $response = $this->actingAs($encoder)
        ->from(route('department-documents.index', ['department_id' => $departmentA->id]))
        ->post(route('department-documents.folders.store'), [
            'department_id' => $departmentA->id,
            'parent_id' => $foreignParent->id,
            'name' => 'Should Fail',
            'folder_code' => 'DPA2-FAIL-001',
        ]);

    $response->assertRedirect(route('department-documents.index', ['department_id' => $departmentA->id]));
    $response->assertSessionHasErrors(['name']);
});

