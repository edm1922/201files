<?php

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Models\User;
use App\Services\DocumentMergeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('renders department document explorer page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('department-documents.index'))
        ->assertOk();
});

it('uploads single and multi-file department documents with hybrid upload modes', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Upload Finance',
        'code' => 'UPF',
        'folder_code' => 'CSC-UPF-0000',
        'description' => 'Upload Department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Permit Upload',
        'code' => 'PERMUP',
        'has_expiry' => true,
        'max_pages' => 3,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'D',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $mergeServiceMock = Mockery::mock(DocumentMergeService::class);
    $mergeServiceMock->shouldReceive('buildPdf')->andReturnUsing(function ($files) {
        $tempPath = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempPath, 'dummy pdf content');

        return [
            'temp_path' => $tempPath,
            'page_count' => 1,
            'source_names' => array_map(fn ($f) => $f->getClientOriginalName(), $files),
        ];
    });
    app()->instance(DocumentMergeService::class, $mergeServiceMock);

    $single = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->toDateString(),
        'files' => [UploadedFile::fake()->create('single.pdf', 120, 'application/pdf')],
    ]);

    $single->assertRedirect(route('department-documents.index', ['department_id' => $department->id]));

    $multi = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'scan_packet',
        'date_received' => now()->toDateString(),
        'files' => [
            UploadedFile::fake()->create('one.jpg', 100, 'image/jpeg'),
            UploadedFile::fake()->create('two.jpg', 100, 'image/jpeg'),
        ],
    ]);

    $multi->assertRedirect(route('department-documents.index', ['department_id' => $department->id]));

    $documents = Document::query()
        ->where('department_id', $department->id)
        ->orderBy('id')
        ->get();

    expect($documents)->toHaveCount(2);

    $singleDocument = $documents->first();
    $mergedDocument = $documents->last();

    expect($singleDocument->upload_mode)->toBe('standard');
    expect($singleDocument->mime_type)->toBe('application/pdf');
    expect($singleDocument->system_filename)->toMatch('/^DEPT-'.$department->id.'_PERMUP_\\d{14}(?:_\\d+)?\\.pdf$/');

    expect($mergedDocument->upload_mode)->toBe('scan_packet');
    expect($mergedDocument->mime_type)->toBe('application/pdf');
    expect($mergedDocument->source_filenames)->toBe(['one.jpg', 'two.jpg']);
    expect($mergedDocument->system_filename)->toMatch('/^DEPT-'.$department->id.'_PERMUP_\\d{14}(?:_\\d+)?\\.pdf$/');

    foreach ($documents as $document) {
        expect($document->file_path)->toStartWith('documents/departments/'.$department->id.'/');
        Storage::disk('local')->assertExists($document->file_path);
    }
});

it('uploads into selected current folder context', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Explorer Upload Dept',
        'code' => 'EUD',
        'folder_code' => 'CSC-EUD-0000',
        'description' => 'Explorer Upload Department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Explorer Type',
        'code' => 'EXPTYP',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $virtualFolder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Current Folder',
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'F',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'document_folder_id' => $virtualFolder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->toDateString(),
        'files' => [UploadedFile::fake()->create('in-folder.pdf', 100, 'application/pdf')],
    ]);

    $response->assertRedirect(route('department-documents.index', [
        'department_id' => $department->id,
        'document_folder_id' => $virtualFolder->id,
    ]));

    $stored = Document::query()->where('department_id', $department->id)->latest('id')->first();

    expect($stored)->not->toBeNull();
    expect((int) $stored->document_folder_id)->toBe((int) $virtualFolder->id);
});

it('accepts scan packet upload when file extension is valid but mime is inconsistent', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Upload Validation Dept',
        'code' => 'UVD',
        'folder_code' => 'CSC-UVD-0000',
        'description' => 'Upload Validation Department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Scan Packet Type',
        'code' => 'SCNPKT',
        'has_expiry' => false,
        'max_pages' => 3,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'E',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $mergeServiceMock = Mockery::mock(DocumentMergeService::class);
    $mergeServiceMock->shouldReceive('buildPdf')->once()->andReturnUsing(function ($files) {
        $tempPath = tempnam(sys_get_temp_dir(), 'scan_packet_');
        file_put_contents($tempPath, 'dummy pdf content');

        return [
            'temp_path' => $tempPath,
            'page_count' => 1,
            'source_names' => array_map(fn ($f) => $f->getClientOriginalName(), $files),
        ];
    });
    app()->instance(DocumentMergeService::class, $mergeServiceMock);

    $response = $this->actingAs($user)->post(route('department-documents.store'), [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'scan_packet',
        'date_received' => now()->toDateString(),
        'files' => [UploadedFile::fake()->create('scanner-output.jpg', 100, 'text/plain')],
    ]);

    $response->assertRedirect(route('department-documents.index', ['department_id' => $department->id]));
    $response->assertSessionHasNoErrors();

    $stored = Document::query()->where('department_id', $department->id)->latest('id')->first();

    expect($stored)->not->toBeNull();
    expect($stored->upload_mode)->toBe('scan_packet');
    expect($stored->source_filenames)->toBe(['scanner-output.jpg']);
});

it('renames duplicate original filenames with incremental suffix', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Duplicate Name Dept',
        'code' => 'DND',
        'folder_code' => 'CSC-DND-0000',
        'description' => 'Duplicate filename handling department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Duplicate Type',
        'code' => 'DUPTYP',
        'has_expiry' => false,
        'max_pages' => 3,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'Z',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $payload = [
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->toDateString(),
    ];

    $first = $this->actingAs($user)->post(route('department-documents.store'), [
        ...$payload,
        'files' => [UploadedFile::fake()->create('report.pdf', 120, 'application/pdf')],
    ]);

    $second = $this->actingAs($user)->post(route('department-documents.store'), [
        ...$payload,
        'files' => [UploadedFile::fake()->create('report.pdf', 121, 'application/pdf')],
    ]);

    $first->assertRedirect(route('department-documents.index', ['department_id' => $department->id]));
    $second->assertRedirect(route('department-documents.index', ['department_id' => $department->id]));

    $filenames = Document::query()
        ->where('department_id', $department->id)
        ->orderBy('id')
        ->pluck('original_filename')
        ->all();

    expect($filenames)->toBe(['report.pdf', 'report (1).pdf']);
});

it('requires expiry date when document type has expiry enabled', function () {
    Storage::fake('local');

    $department = Department::create([
        'name' => 'Expiry Required Dept',
        'code' => 'EXR',
        'folder_code' => 'CSC-EXR-0000',
        'description' => 'Expiry required validation department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Permit With Expiry',
        'code' => 'PWEXP',
        'has_expiry' => true,
        'max_pages' => 3,
    ]);

    $folder = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'X',
        'max_capacity' => 500,
    ]);

    $user = User::factory()->encoder()->create();
    $user->authorizedDepartments()->sync([$department->id]);

    $response = $this->actingAs($user)
        ->from(route('department-documents.index', ['department_id' => $department->id]))
        ->post(route('department-documents.store'), [
            'department_id' => $department->id,
            'document_type_id' => $documentType->id,
            'folder_location_id' => $folder->id,
            'upload_mode' => 'standard',
            'date_received' => now()->toDateString(),
            'files' => [UploadedFile::fake()->create('permit.pdf', 120, 'application/pdf')],
        ]);

    $response->assertRedirect(route('department-documents.index', ['department_id' => $department->id]));
    $response->assertSessionHasErrors(['expiry_date']);

    expect(Document::query()->where('department_id', $department->id)->count())->toBe(0);
});

it('renames restored archived file when active filename already exists in same location', function () {
    $department = Department::create([
        'name' => 'Restore Collision Dept',
        'code' => 'RCD',
        'folder_code' => 'CSC-RCD-0000',
        'description' => 'Restore collision department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Restore Type',
        'code' => 'RSTYP',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $folderLocation = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'R',
        'max_capacity' => 500,
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Root Files',
    ]);

    $admin = User::factory()->admin()->create();

    $archived = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folderLocation->id,
        'document_folder_id' => $folder->id,
        'uploaded_by' => $admin->id,
        'file_path' => 'documents/departments/'.$department->id.'/archived.pdf',
        'original_filename' => 'duplicate.pdf',
        'system_filename' => 'archived.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'archived',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['duplicate.pdf'],
    ]);

    $archived->delete();

    $active = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folderLocation->id,
        'document_folder_id' => $folder->id,
        'uploaded_by' => $admin->id,
        'file_path' => 'documents/departments/'.$department->id.'/active.pdf',
        'original_filename' => 'duplicate.pdf',
        'system_filename' => 'active.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['duplicate.pdf'],
    ]);

    $response = $this->actingAs($admin)->patch(route('department-documents.restore', $archived->id));

    $response->assertRedirect();

    $archived->refresh();
    $active->refresh();

    expect($archived->trashed())->toBeFalse();
    expect($archived->status)->toBe('active');
    expect($active->original_filename)->toBe('duplicate.pdf');
    expect($archived->original_filename)->toBe('duplicate (1).pdf');
});

it('renames document with incremental suffix when manual rename collides with active filename', function () {
    $department = Department::create([
        'name' => 'Rename Collision Dept',
        'code' => 'NCD',
        'folder_code' => 'CSC-NCD-0000',
        'description' => 'Rename collision department',
        'is_active' => true,
    ]);

    $documentType = DocumentType::create([
        'department_id' => $department->id,
        'name' => 'Rename Type',
        'code' => 'RNTYP',
        'has_expiry' => false,
        'max_pages' => 2,
    ]);

    $folderLocation = FolderLocation::query()->first() ?? FolderLocation::create([
        'row_name' => 'N',
        'max_capacity' => 500,
    ]);

    $folder = DocumentFolder::create([
        'department_id' => $department->id,
        'parent_id' => null,
        'name' => 'Root Files',
    ]);

    $encoder = User::factory()->encoder()->create();
    $encoder->authorizedDepartments()->sync([$department->id]);

    $existing = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folderLocation->id,
        'document_folder_id' => $folder->id,
        'uploaded_by' => $encoder->id,
        'file_path' => 'documents/departments/'.$department->id.'/existing.pdf',
        'original_filename' => 'report.pdf',
        'system_filename' => 'existing.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['report.pdf'],
    ]);

    $renameTarget = Document::create([
        'department_id' => $department->id,
        'document_type_id' => $documentType->id,
        'folder_location_id' => $folderLocation->id,
        'document_folder_id' => $folder->id,
        'uploaded_by' => $encoder->id,
        'file_path' => 'documents/departments/'.$department->id.'/rename-target.pdf',
        'original_filename' => 'draft.pdf',
        'system_filename' => 'rename-target.pdf',
        'page_count' => 1,
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'upload_mode' => 'standard',
        'status' => 'active',
        'date_received' => now()->toDateString(),
        'source_filenames' => ['draft.pdf'],
    ]);

    $response = $this->actingAs($encoder)->patch(route('department-documents.update', $renameTarget), [
        'original_filename' => 'report.pdf',
    ]);

    $response->assertRedirect();

    $existing->refresh();
    $renameTarget->refresh();

    expect($existing->original_filename)->toBe('report.pdf');
    expect($renameTarget->original_filename)->toBe('report (1).pdf');
});
