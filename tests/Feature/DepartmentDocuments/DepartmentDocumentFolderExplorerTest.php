<?php

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('shows nested folder tree and breadcrumb within selected department', function () {
    $department = Department::create([
        'name' => 'Explorer Dept',
        'code' => 'EXD',
        'folder_code' => 'CSC-EXD-0000',
        'description' => 'Explorer Department',
        'is_active' => true,
    ]);

    $root = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Root Policies',
    ]);

    $child = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => $root->id,
        'name' => '2026',
    ]);

    $user = User::factory()->viewer()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($user)->get(route('department-documents.index', [
        'department_id' => $department->id,
        'document_folder_id' => $child->id,
    ]));

    $response->assertOk();
    $response->assertSee('Root Policies');
    $response->assertSee('2026');
    $response->assertSee('Documents in Current Scope');
});

it('hides folder tree entries from unauthorized departments', function () {
    $departmentA = Department::create([
        'name' => 'Allowed Dept',
        'code' => 'ALD',
        'folder_code' => 'CSC-ALD-0000',
        'description' => 'Allowed Department',
        'is_active' => true,
    ]);

    $departmentB = Department::create([
        'name' => 'Hidden Dept',
        'code' => 'HDD',
        'folder_code' => 'CSC-HDD-0000',
        'description' => 'Hidden Department',
        'is_active' => true,
    ]);

    DocumentFolder::create([
        'department_id' => $departmentA->id,
        'parent_id' => null,
        'name' => 'Allowed Folder',
    ]);

    DocumentFolder::create([
        'department_id' => $departmentB->id,
        'parent_id' => null,
        'name' => 'Hidden Folder',
    ]);

    $user = User::factory()->viewer()->create();
    $user->authorizedDepartments()->sync([$departmentA->id]);

    $response = $this->actingAs($user)->get(route('department-documents.index', [
        'department_id' => $departmentA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Allowed Folder');
    $response->assertDontSee('Hidden Folder');
});

it('renames folder within authorized department', function () {
    $department = Department::create([
        'name' => 'Rename Dept',
        'code' => 'RND',
        'folder_code' => 'CSC-RND-0000',
        'description' => 'Rename Department',
        'is_active' => true,
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Old Name',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->patch(route('department-documents.folders.update', $folder), [
            'name' => 'Renamed Folder',
        ]);

    $response->assertRedirect(route('department-documents.index', [
        'department_id' => $department->id,
        'document_folder_id' => $folder->id,
    ]));

    expect($folder->fresh()->name)->toBe('Renamed Folder');
});

it('deletes empty folder and redirects to parent context', function () {
    $department = Department::create([
        'name' => 'Delete Dept',
        'code' => 'DLD',
        'folder_code' => 'CSC-DLD-0000',
        'description' => 'Delete Department',
        'is_active' => true,
    ]);

    $parent = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Parent',
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => $parent->id,
        'name' => 'Child Empty',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->delete(route('department-documents.folders.destroy', $folder));

    $response->assertRedirect(route('department-documents.index', [
        'department_id' => $department->id,
        'document_folder_id' => $parent->id,
    ]));

    expect(DocumentFolder::query()->whereKey($folder->id)->exists())->toBeFalse();
});

it('returns json payload for ajax folder creation', function () {
    $department = Department::create([
        'name' => 'Ajax Create Dept',
        'code' => 'AJC',
        'folder_code' => 'CSC-AJC-0000',
        'description' => 'Ajax Create Department',
        'is_active' => true,
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->postJson(route('department-documents.folders.store'), [
            'department_id' => $department->id,
            'name' => 'Async Folder',
            'folder_code' => 'AJC-ASYNC-001',
        ]);

    $response->assertCreated();
    $response->assertJsonPath('ok', true);
    $response->assertJsonPath('folder.name', 'Async Folder');
    $response->assertJsonPath('folder.department_id', $department->id);
    $response->assertJsonPath('folder.folder_code', 'AJC-ASYNC-001');
    $response->assertJsonPath('redirect_url', route('department-documents.index', [
        'department_id' => $department->id,
        'document_folder_id' => $response->json('folder.id'),
    ]));
});

it('rejects ajax folder creation when folder code already exists in department', function () {
    $department = Department::create([
        'name' => 'Ajax Duplicate Code Dept',
        'code' => 'ADC',
        'folder_code' => 'CSC-ADC-0000',
        'description' => 'Ajax Duplicate Code Department',
        'is_active' => true,
    ]);

    DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Existing Folder',
        'folder_code' => 'ADC-EXIST-001',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->postJson(route('department-documents.folders.store'), [
            'department_id' => $department->id,
            'name' => 'Another Folder',
            'folder_code' => 'adc-exist-001',
        ]);

    $response->assertStatus(422);
    $response->assertJsonPath('ok', false);
    $response->assertJsonPath('errors.folder_code.0', 'Folder code already exists in this department.');
});

it('returns json payload for ajax folder rename', function () {
    $department = Department::create([
        'name' => 'Ajax Rename Dept',
        'code' => 'AJR',
        'folder_code' => 'CSC-AJR-0000',
        'description' => 'Ajax Rename Department',
        'is_active' => true,
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Before Rename',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->patchJson(route('department-documents.folders.update', $folder), [
            'name' => 'After Rename',
        ]);

    $response->assertOk();
    $response->assertJsonPath('ok', true);
    $response->assertJsonPath('folder.id', $folder->id);
    $response->assertJsonPath('folder.name', 'After Rename');

    expect($folder->fresh()->name)->toBe('After Rename');
});

it('returns json validation error when ajax folder delete is blocked', function () {
    $department = Department::create([
        'name' => 'Ajax Delete Guard Dept',
        'code' => 'AJD',
        'folder_code' => 'CSC-AJD-0000',
        'description' => 'Ajax Delete Guard Department',
        'is_active' => true,
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Has Child',
    ]);

    DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => $folder->id,
        'name' => 'Nested',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->deleteJson(route('department-documents.folders.destroy', $folder));

    $response->assertStatus(422);
    $response->assertJsonPath('ok', false);
    $response->assertJsonPath('errors.name.0', 'Folder cannot be deleted because it has subfolders.');

    expect(DocumentFolder::query()->whereKey($folder->id)->exists())->toBeTrue();
});

it('streams inline preview for authorized pdf document', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Preview Dept',
        'code' => 'PRV',
        'folder_code' => 'CSC-PRV-0000',
        'description' => 'Preview Department',
        'is_active' => true,
    ]);

    $type = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Preview Type',
        'code' => 'PRVT',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $location = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'P',
        'column_code' => '1',
    ]);

    $uploader = User::factory()->admin()->create();
    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->sync([$department->id]);

    $path = 'documents/departments/' . $department->id . '/preview.pdf';
    Storage::disk('local')->put($path, '%PDF-1.4 test');

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $type->id,
        'folder_location_id' => $location->id,
        'document_folder_id' => null,
        'uploaded_by' => $uploader->id,
        'file_path' => $path,
        'original_filename' => 'preview.pdf',
        'system_filename' => 'preview.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['preview.pdf'],
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('department-documents.preview', $document));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'inline; filename="preview.pdf"');
});

it('rejects inline preview for unsupported file types', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Preview Reject Dept',
        'code' => 'PRR',
        'folder_code' => 'CSC-PRR-0000',
        'description' => 'Preview Reject Department',
        'is_active' => true,
    ]);

    $type = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Word Type',
        'code' => 'WORDT',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $location = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'W',
        'column_code' => '2',
    ]);

    $uploader = User::factory()->admin()->create();
    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->sync([$department->id]);

    $path = 'documents/departments/' . $department->id . '/notes.docx';
    Storage::disk('local')->put($path, 'dummy content');

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $type->id,
        'folder_location_id' => $location->id,
        'document_folder_id' => null,
        'uploaded_by' => $uploader->id,
        'file_path' => $path,
        'original_filename' => 'notes.docx',
        'system_filename' => 'notes.docx',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['notes.docx'],
    ]);

    $this->actingAs($viewer)
        ->get(route('department-documents.preview', $document))
        ->assertStatus(415);
});

it('renders docx preview button with download route for office embed', function () {
    $department = Department::create([
        'name' => 'Docx Preview Dept',
        'code' => 'DPD',
        'folder_code' => 'CSC-DPD-0000',
        'description' => 'Docx Preview Department',
        'is_active' => true,
    ]);

    $type = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Docx Type',
        'code' => 'DOCX',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $location = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'D',
        'column_code' => '9',
    ]);

    $uploader = User::factory()->admin()->create();
    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->sync([$department->id]);

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $type->id,
        'folder_location_id' => $location->id,
        'document_folder_id' => null,
        'uploaded_by' => $uploader->id,
        'file_path' => 'documents/departments/' . $department->id . '/manual.docx',
        'original_filename' => 'manual.docx',
        'system_filename' => 'manual.docx',
        'page_count' => 1,
        'file_size_bytes' => 2048,
        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['manual.docx'],
    ]);

    $response = $this->actingAs($viewer)->get(route('department-documents.index', [
        'department_id' => $department->id,
    ]));

    $response->assertOk();
    $response->assertSee('data-preview-kind="docx"', false);
    $response->assertSee('data-preview-url="' . e(route('department-documents.download', $document)) . '"', false);
});

it('renders xlsx preview button with download route for office embed', function () {
    $department = Department::create([
        'name' => 'Sheet Preview Dept',
        'code' => 'SPD',
        'folder_code' => 'CSC-SPD-0000',
        'description' => 'Sheet Preview Department',
        'is_active' => true,
    ]);

    $type = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Sheet Type',
        'code' => 'SHEET',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $location = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'S',
        'column_code' => '8',
    ]);

    $uploader = User::factory()->admin()->create();
    $viewer = User::factory()->viewer()->create();
    $viewer->authorizedDepartments()->sync([$department->id]);

    $document = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $type->id,
        'folder_location_id' => $location->id,
        'document_folder_id' => null,
        'uploaded_by' => $uploader->id,
        'file_path' => 'documents/departments/' . $department->id . '/budget.xlsx',
        'original_filename' => 'budget.xlsx',
        'system_filename' => 'budget.xlsx',
        'page_count' => 1,
        'file_size_bytes' => 3072,
        'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['budget.xlsx'],
    ]);

    $response = $this->actingAs($viewer)->get(route('department-documents.index', [
        'department_id' => $department->id,
    ]));

    $response->assertOk();
    $response->assertSee('data-preview-kind="sheet"', false);
    $response->assertSee('data-preview-url="' . e(route('department-documents.download', $document)) . '"', false);
});

it('returns json payload for ajax folder rename via post method spoofing', function () {
    $department = Department::create([
        'name' => 'Ajax Spoof Rename Dept',
        'code' => 'ASR',
        'folder_code' => 'CSC-ASR-0000',
        'description' => 'Ajax Spoof Rename Department',
        'is_active' => true,
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Before Spoof Rename',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->post(route('department-documents.folders.update', $folder), [
            '_method' => 'PATCH',
            'name' => 'After Spoof Rename',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertOk();
    $response->assertJsonPath('ok', true);
    $response->assertJsonPath('folder.name', 'After Spoof Rename');

    expect($folder->fresh()->name)->toBe('After Spoof Rename');
});

it('returns json payload for ajax folder delete via post method spoofing', function () {
    $department = Department::create([
        'name' => 'Ajax Spoof Delete Dept',
        'code' => 'ASD',
        'folder_code' => 'CSC-ASD-0000',
        'description' => 'Ajax Spoof Delete Department',
        'is_active' => true,
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Delete Me',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($encoder)
        ->post(route('department-documents.folders.destroy', $folder), [
            '_method' => 'DELETE',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertOk();
    $response->assertJsonPath('ok', true);

    expect(DocumentFolder::query()->whereKey($folder->id)->exists())->toBeFalse();
});
