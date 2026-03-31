<?php

use App\Models\Department;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('renders department document explorer page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('department-documents.index'))
        ->assertOk()
        ->assertSee('Department Documents');
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

    $mergeServiceMock = \Mockery::mock(\App\Services\DocumentMergeService::class);
    $mergeServiceMock->shouldReceive('buildPdf')->andReturnUsing(function ($files) {
        $tempPath = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempPath, 'dummy pdf content');
        return [
            'temp_path' => $tempPath,
            'page_count' => 1,
            'source_names' => array_map(fn ($f) => $f->getClientOriginalName(), $files),
        ];
    });
    app()->instance(\App\Services\DocumentMergeService::class, $mergeServiceMock);

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

    $documents = \App\Models\Document::query()
        ->where('department_id', $department->id)
        ->orderBy('id')
        ->get();

    expect($documents)->toHaveCount(2);

    $singleDocument = $documents->first();
    $mergedDocument = $documents->last();

    expect($singleDocument->upload_mode)->toBe('standard');
    expect($singleDocument->mime_type)->toBe('application/pdf');
    expect($singleDocument->system_filename)->toMatch('/^DEPT-' . $department->id . '_PERMUP_\\d{14}(?:_\\d+)?\\.pdf$/');

    expect($mergedDocument->upload_mode)->toBe('scan_packet');
    expect($mergedDocument->mime_type)->toBe('application/pdf');
    expect($mergedDocument->source_filenames)->toBe(['one.jpg', 'two.jpg']);
    expect($mergedDocument->system_filename)->toMatch('/^DEPT-' . $department->id . '_PERMUP_\\d{14}(?:_\\d+)?\\.pdf$/');

    foreach ($documents as $document) {
        expect($document->file_path)->toStartWith('documents/departments/' . $department->id . '/');
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

    $virtualFolder = \App\Models\DocumentFolder::create([
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

    $stored = \App\Models\Document::query()->where('department_id', $department->id)->latest('id')->first();

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

    $mergeServiceMock = \Mockery::mock(\App\Services\DocumentMergeService::class);
    $mergeServiceMock->shouldReceive('buildPdf')->once()->andReturnUsing(function ($files) {
        $tempPath = tempnam(sys_get_temp_dir(), 'scan_packet_');
        file_put_contents($tempPath, 'dummy pdf content');

        return [
            'temp_path' => $tempPath,
            'page_count' => 1,
            'source_names' => array_map(fn ($f) => $f->getClientOriginalName(), $files),
        ];
    });
    app()->instance(\App\Services\DocumentMergeService::class, $mergeServiceMock);

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

    $stored = \App\Models\Document::query()->where('department_id', $department->id)->latest('id')->first();

    expect($stored)->not->toBeNull();
    expect($stored->upload_mode)->toBe('scan_packet');
    expect($stored->source_filenames)->toBe(['scanner-output.jpg']);
});
