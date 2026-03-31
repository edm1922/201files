<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Storage::fake('local');

    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->encoder = User::factory()->create(['role' => 'encoder']);
    
    $this->department = Department::factory()->create(['is_active' => true]);
    $this->otherDepartment = Department::factory()->create(['is_active' => true]);

    // Give encoder access only to $this->department
    $this->encoder->authorizedDepartments()->attach($this->department->id);

    $this->docType = DocumentType::factory()->create([
        'department_id' => $this->department->id,
        'name' => 'Test Document',
        'code' => 'TEST',
        'max_pages' => 10
    ]);
    
    $this->location = FolderLocation::factory()->create();
});

test('admin can upload a standard PDF document', function () {
    $file = UploadedFile::fake()->create('test.pdf', 500, 'application/pdf');

    $response = $this->actingAs($this->admin)->post(route('department-documents.store'), [
        'department_id' => $this->department->id,
        'document_type_id' => $this->docType->id,
        'folder_location_id' => $this->location->id,
        'upload_mode' => 'standard',
        'date_received' => now()->format('Y-m-d'),
        'files' => [$file],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    
    $document = Document::first();
    expect($document)->not->toBeNull()
        ->and($document->original_filename)->toBe('test.pdf')
        ->and($document->mime_type)->toBe('application/pdf');
    
    Storage::disk('local')->assertExists($document->file_path);
});

test('encoder can upload to authorized department', function () {
    $file = UploadedFile::fake()->create('encoder.pdf');

    $response = $this->actingAs($this->encoder)->post(route('department-documents.store'), [
        'department_id' => $this->department->id,
        'document_type_id' => $this->docType->id,
        'folder_location_id' => $this->location->id,
        'upload_mode' => 'standard',
        'date_received' => now()->format('Y-m-d'),
        'files' => [$file],
    ]);

    $response->assertSessionHasNoErrors();
    expect(Document::count())->toBe(1);
});

test('encoder cannot upload to unauthorized department', function () {
    $otherDocType = DocumentType::factory()->create(['department_id' => $this->otherDepartment->id]);
    $file = UploadedFile::fake()->create('fail.pdf');

    $response = $this->actingAs($this->encoder)->post(route('department-documents.store'), [
        'department_id' => $this->otherDepartment->id,
        'document_type_id' => $otherDocType->id,
        'folder_location_id' => $this->location->id,
        'upload_mode' => 'standard',
        'date_received' => now()->format('Y-m-d'),
        'files' => [$file],
    ]);

    $response->assertStatus(403);
});

test('admin can upload and merge scan packet (PDF + Images)', function () {
    $pdf = UploadedFile::fake()->create('page1.pdf', 100, 'application/pdf');
    $img = UploadedFile::fake()->image('page2.jpg');

    $response = $this->actingAs($this->admin)->post(route('department-documents.store'), [
        'department_id' => $this->department->id,
        'document_type_id' => $this->docType->id,
        'folder_location_id' => $this->location->id,
        'upload_mode' => 'scan_packet',
        'date_received' => now()->format('Y-m-d'),
        'files' => [$pdf, $img],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $document = Document::first();
    expect($document->upload_mode)->toBe('scan_packet')
        ->and($document->mime_type)->toBe('application/pdf')
        ->and($document->source_filenames)->toHaveCount(2);

    Storage::disk('local')->assertExists($document->file_path);
});

test('it rejects unsupported file formats in standard mode', function () {
    $file = UploadedFile::fake()->create('test.exe', 500, 'application/x-msdownload');

    $response = $this->actingAs($this->admin)->post(route('department-documents.store'), [
        'department_id' => $this->department->id,
        'document_type_id' => $this->docType->id,
        'folder_location_id' => $this->location->id,
        'upload_mode' => 'standard',
        'date_received' => now()->format('Y-m-d'),
        'files' => [$file],
    ]);

    $response->assertSessionHasErrors(['files']);
});

test('it can create a virtual folder and upload into it', function () {
    $folder = DocumentFolder::create([
        'department_id' => $this->department->id,
        'name' => 'Sub Folder'
    ]);

    $file = UploadedFile::fake()->create('inner.pdf');

    $response = $this->actingAs($this->admin)->post(route('department-documents.store'), [
        'department_id' => $this->department->id,
        'document_type_id' => $this->docType->id,
        'folder_location_id' => $this->location->id,
        'document_folder_id' => $folder->id,
        'upload_mode' => 'standard',
        'date_received' => now()->format('Y-m-d'),
        'files' => [$file],
    ]);

    $response->assertSessionHasNoErrors();
    $document = Document::first();
    expect($document->document_folder_id)->toBe($folder->id);
});

test('it rejects folder creation if name exists in same parent', function () {
    DocumentFolder::create([
        'department_id' => $this->department->id,
        'name' => 'DUPLICATE',
        'folder_code' => 'TST-DUP-001',
    ]);

    $response = $this->actingAs($this->admin)->post(route('department-documents.folders.store'), [
        'department_id' => $this->department->id,
        'name' => 'duplicate',
        'folder_code' => 'TST-DUP-002',
    ]);

    $response->assertSessionHasErrors(['name']);
});
