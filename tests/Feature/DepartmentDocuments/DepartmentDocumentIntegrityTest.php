<?php

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('rejects creating document without department_id', function () {
    $user = User::factory()->admin()->create();

    $documentType = DocumentType::create([
        'department_id' => null,
        'name' => 'Unscoped Type',
        'code' => 'UNSCOPED',
        'has_expiry' => false,
        'max_pages' => 1,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'A',
        'max_capacity' => 500,
    ]);

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->toDateString(),
        'files' => [UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf')],
    ]);

    $response->assertSessionHasErrors(['department_id']);
});

it('rejects persisting document with null department_id at DB layer', function () {
    $documentType = DocumentType::create([
        'department_id' => null,
        'name' => 'Integrity Type',
        'code' => 'INTG',
        'has_expiry' => false,
        'max_pages' => 1,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'A',
        'max_capacity' => 500,
    ]);

    $uploader = User::factory()->admin()->create();

    expect(fn () => Document::create([
        'department_id' => null,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'uploaded_by' => $uploader->id,
        'file_path' => 'documents/departments/0/DEPT-0_INTG_20260325.pdf',
        'original_filename' => 'integrity.pdf',
        'system_filename' => 'DEPT-0_INTG_20260325.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'status' => 'active',
        'date_received' => now()->toDateString(),
    ]))->toThrow(QueryException::class);
});

it('rejects document type from another department', function () {
    $deptA = Department::create([
        'name' => 'Dept A',
        'code' => 'DPA',
        'folder_code' => 'CSC-DPA-0000',
        'description' => 'Department A',
        'is_active' => true,
    ]);

    $deptB = Department::create([
        'name' => 'Dept B',
        'code' => 'DPB',
        'folder_code' => 'CSC-DPB-0000',
        'description' => 'Department B',
        'is_active' => true,
    ]);

    $typeB = DocumentType::create([
        'department_id' => $deptB->id,
        'name' => 'B Type',
        'code' => 'BTYPE',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'A',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $deptA->id,
        'document_type_id' => $typeB->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->toDateString(),
        'files' => [UploadedFile::fake()->create('bad.pdf', 100, 'application/pdf')],
    ]);

    $response->assertSessionHasErrors(['document_type_id']);
});

it('rejects expiry date earlier than date received', function () {
    $department = Department::create([
        'name' => 'Integrity Dept Expiry',
        'code' => 'IDEXP',
        'folder_code' => 'CSC-IDEXP-0000',
        'description' => 'Integrity Dept Expiry',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Expiry Type',
        'code' => 'EXPTY',
        'has_expiry' => true,
        'max_pages' => 2,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'A',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'standard',
        'date_received' => '2026-03-25',
        'expiry_date' => '2026-03-24',
        'files' => [UploadedFile::fake()->create('expiry.pdf', 100, 'application/pdf')],
    ]);

    $response->assertSessionHasErrors(['expiry_date']);
});

it('rejects files count above selected document type max_pages', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Integrity Dept Max',
        'code' => 'IDMAX',
        'folder_code' => 'CSC-IDMAX-0000',
        'description' => 'Integrity Dept Max',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Single Page Type',
        'code' => 'SPAGE',
        'has_expiry' => false,
        'max_pages' => 1,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'A',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'scan_packet',
        'date_received' => now()->toDateString(),
        'files' => [
            UploadedFile::fake()->create('max-1.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('max-2.pdf', 100, 'application/pdf'),
        ],
    ]);

    $response->assertSessionHasErrors(['files']);
});



it('rejects document folder from another department', function () {
    $deptA = Department::create([
        'name' => 'Dept Folder A',
        'code' => 'DFA',
        'folder_code' => 'CSC-DFA-0000',
        'description' => 'Department Folder A',
        'is_active' => true,
    ]);

    $deptB = Department::create([
        'name' => 'Dept Folder B',
        'code' => 'DFB',
        'folder_code' => 'CSC-DFB-0000',
        'description' => 'Department Folder B',
        'is_active' => true,
    ]);

    $typeA = DocumentType::create([
        'department_id' => $deptA->id,
        'name' => 'Type A Folder',
        'code' => 'TAFOL',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $foreignFolder = DocumentFolder::create([
        'department_id' => $deptB->id,
        'parent_id' => null,
        'name' => 'Foreign Folder',
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'G',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $deptA->id,
        'document_type_id' => $typeA->id,
        'folder_location_id' => $folder->id,
        'document_folder_id' => $foreignFolder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->toDateString(),
        'files' => [UploadedFile::fake()->create('folder-boundary.pdf', 100, 'application/pdf')],
    ]);

    $response->assertSessionHasErrors(['document_folder_id']);
});

it('cleans up stored file when persistence fails', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Finance',
        'code' => 'FIN',
        'folder_code' => 'CSC-FIN-0000',
        'description' => 'Finance',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Permit',
        'code' => 'PERMIT',
        'has_expiry' => true,
        'max_pages' => 2,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'B',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->admin()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->toDateString(),
        'force_fail_after_store' => 1,
        'files' => [UploadedFile::fake()->create('cleanup.pdf', 120, 'application/pdf')],
    ]);

    $response->assertSessionHasErrors();

    expect(Document::count())->toBe(0);
    expect(Storage::disk('local')->allFiles('documents/departments/' . $department->id))->toBe([]);
});
