<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DepartmentDocumentUploadService
{
    public function __construct(
        private readonly DocumentMergeService $mergeService
    ) {}

    public function upload(array $validatedData, User $user): Document
    {
        $departmentId = (int) $validatedData['department_id'];
        $docTypeId = (int) $validatedData['document_type_id'];
        $documentLocationId = (int) $validatedData['document_location_id'];
        $uploadMode = $validatedData['upload_mode'] ?? 'standard';
        $dateReceived = Carbon::parse($validatedData['date_received']);
        $expiryDate = isset($validatedData['expiry_date']) ? Carbon::parse($validatedData['expiry_date']) : null;
        $files = $validatedData['files'];

        $documentType = DocumentType::findOrFail($docTypeId);
        $documentFolderId = $this->resolveDocumentFolderId($validatedData['document_folder_id'] ?? null);

        if ($uploadMode === 'scan_packet') {
            return $this->storeScanPacket(
                files: $files,
                departmentId: $departmentId,
                documentType: $documentType,
                documentLocationId: $documentLocationId,
                documentFolderId: $documentFolderId,
                user: $user,
                dateReceived: $dateReceived,
                expiryDate: $expiryDate,
                validatedData: $validatedData
            );
        }

        /** @var UploadedFile $file */
        $file = $files[0];

        return $this->storeStandardFile(
            file: $file,
            departmentId: $departmentId,
            documentType: $documentType,
            documentLocationId: $documentLocationId,
            documentFolderId: $documentFolderId,
            user: $user,
            dateReceived: $dateReceived,
            expiryDate: $expiryDate,
            validatedData: $validatedData
        );
    }

    private function storeScanPacket(
        array $files,
        int $departmentId,
        DocumentType $documentType,
        int $documentLocationId,
        ?int $documentFolderId,
        User $user,
        Carbon $dateReceived,
        ?Carbon $expiryDate,
        array $validatedData
    ): Document {
        $mergedData = $this->mergeService->buildPdf($files);
        $tempPath = $mergedData['temp_path'];
        $pageCount = $mergedData['page_count'];
        $sourceNames = $mergedData['source_names'];

        $baseFilename = $this->buildBaseFilename($departmentId, $documentType->code, $dateReceived, 'pdf');
        $directory = "documents/departments/{$departmentId}";
        $resolvedFilename = $this->resolveUniqueFilename($directory, $baseFilename, 'pdf');
        $relativePath = "{$directory}/{$resolvedFilename}";

        try {
            return DB::transaction(function () use (
                $tempPath,
                $relativePath,
                $departmentId,
                $documentType,
                $documentLocationId,
                $documentFolderId,
                $user,
                $sourceNames,
                $resolvedFilename,
                $pageCount,
                $dateReceived,
                $expiryDate,
                $validatedData

            ) {
                $fileSize = filesize($tempPath);
                Storage::disk('local')->put($relativePath, file_get_contents($tempPath));

                $document = Document::create([
                    'department_id' => $departmentId,
                    'document_type_id' => $documentType->id,
                    'document_location_id' => $documentLocationId,
                    'document_folder_id' => $documentFolderId,
                    'uploaded_by' => $user->id,
                    'file_path' => $relativePath,
                    'original_filename' => substr($resolvedFilename, 0, 255),
                    'system_filename' => $resolvedFilename,
                    'page_count' => $pageCount,
                    'file_size_bytes' => $fileSize,
                    'mime_type' => 'application/pdf',
                    'upload_mode' => 'scan_packet',
                    'status' => 'active',
                    'date_received' => $dateReceived,
                    'expiry_date' => $expiryDate,
                    'source_filenames' => $sourceNames,
                ]);

                AuditService::logDepartmentDocumentLifecycle('uploaded', $document);

                if (! empty($validatedData['force_fail_after_store'])) {
                    throw new \RuntimeException('Forced failure for integrity test');
                }

                @unlink($tempPath);

                return $document;
            });
        } catch (\Throwable $e) {
            @unlink($tempPath);

            $deleted = Storage::disk('local')->delete($relativePath);

            if (! $deleted) {
                AuditService::log('cleanup_failed', 'Department upload cleanup failed', null, ['path' => $relativePath]);
            }

            throw $e;
        }
    }

    private function storeStandardFile(
        UploadedFile $file,
        int $departmentId,
        DocumentType $documentType,
        int $documentLocationId,
        ?int $documentFolderId,
        User $user,
        Carbon $dateReceived,
        ?Carbon $expiryDate,
        array $validatedData
    ): Document {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $baseFilename = $this->buildBaseFilename($departmentId, $documentType->code, $dateReceived, $extension);
        $directory = "documents/departments/{$departmentId}";
        $resolvedFilename = $this->resolveUniqueFilename($directory, $baseFilename, $extension);
        $resolvedOriginalFilename = $this->resolveUniqueOriginalFilename(
            departmentId: $departmentId,
            documentFolderId: $documentFolderId,
            originalFilename: $file->getClientOriginalName()
        );
        $relativePath = "{$directory}/{$resolvedFilename}";

        try {
            return DB::transaction(function () use (
                $file,
                $relativePath,
                $departmentId,
                $documentType,
                $documentLocationId,
                $documentFolderId,
                $user,
                $resolvedFilename,
                $resolvedOriginalFilename,
                $dateReceived,
                $expiryDate,
                $validatedData
            ) {
                $storageSuccess = Storage::disk('local')->put($relativePath, file_get_contents($file->getRealPath()));
                if (! $storageSuccess) {
                    throw new \RuntimeException('Unable to store uploaded file.');
                }

                $document = Document::create([
                    'department_id' => $departmentId,
                    'document_type_id' => $documentType->id,
                    'document_location_id' => $documentLocationId,
                    'document_folder_id' => $documentFolderId,
                    'uploaded_by' => $user->id,
                    'file_path' => $relativePath,
                    'original_filename' => $resolvedOriginalFilename,
                    'system_filename' => $resolvedFilename,
                    'page_count' => 1,
                    'file_size_bytes' => (int) $file->getSize(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'upload_mode' => 'standard',
                    'status' => 'active',
                    'date_received' => $dateReceived,
                    'expiry_date' => $expiryDate,
                    'source_filenames' => [$file->getClientOriginalName()],
                ]);

                AuditService::logDepartmentDocumentLifecycle('uploaded', $document);

                if (! empty($validatedData['force_fail_after_store'])) {
                    throw new \RuntimeException('Forced failure for integrity test');
                }

                return $document;
            });
        } catch (\Throwable $e) {
            $deleted = Storage::disk('local')->delete($relativePath);

            if (! $deleted) {
                AuditService::log('cleanup_failed', 'Department upload cleanup failed', null, ['path' => $relativePath]);
            }

            throw $e;
        }
    }

    private function buildBaseFilename(int $departmentId, string $docTypeCode, Carbon $date, string $extension): string
    {
        // Use the user-selected business date (Ymd) but inject the exact real-time (His) of the upload for uniqueness
        $timestamp = $date->format('Ymd').now()->format('His');
        $prefix = sprintf('DEPT-%d_%s_%s', $departmentId, strtoupper($docTypeCode), $timestamp);

        return Str::of($prefix)->append('.')->append($extension)->toString();
    }

    private function resolveUniqueFilename(string $directory, string $baseName, string $extension): string
    {
        if (! Storage::disk('local')->exists("{$directory}/{$baseName}")) {
            return $baseName;
        }

        $nameWithoutExtension = pathinfo($baseName, PATHINFO_FILENAME);
        $counter = 1;
        while (true) {
            $formattedCounter = sprintf('%02d', $counter);
            $newName = "{$nameWithoutExtension}_{$formattedCounter}.{$extension}";
            if (! Storage::disk('local')->exists("{$directory}/{$newName}")) {
                return $newName;
            }
            $counter++;
        }
    }

    private function resolveDocumentFolderId(mixed $folderIdInput): ?int
    {
        $folderId = (int) ($folderIdInput ?? 0);

        return $folderId > 0 ? $folderId : null;
    }

    private function resolveUniqueOriginalFilename(int $departmentId, ?int $documentFolderId, string $originalFilename): string
    {
        $trimmed = trim($originalFilename);
        $original = $trimmed !== '' ? $trimmed : 'file';

        $extension = pathinfo($original, PATHINFO_EXTENSION);
        $baseName = pathinfo($original, PATHINFO_FILENAME);
        $baseName = $baseName !== '' ? $baseName : 'file';
        $extensionWithDot = $extension !== '' ? ".{$extension}" : '';

        $candidate = substr($original, 0, 255);
        if (! $this->originalFilenameExists($departmentId, $documentFolderId, $candidate)) {
            return $candidate;
        }

        $counter = 1;
        while (true) {
            $suffix = " ({$counter})";
            $maxBaseLength = max(1, 255 - strlen($suffix) - strlen($extensionWithDot));
            $truncatedBase = mb_substr($baseName, 0, $maxBaseLength);
            $candidate = "{$truncatedBase}{$suffix}{$extensionWithDot}";

            if (! $this->originalFilenameExists($departmentId, $documentFolderId, $candidate)) {
                return $candidate;
            }

            $counter++;
        }
    }

    private function originalFilenameExists(int $departmentId, ?int $documentFolderId, string $filename): bool
    {
        return Document::query()
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->when(
                $documentFolderId === null,
                fn ($query) => $query->whereNull('document_folder_id'),
                fn ($query) => $query->where('document_folder_id', $documentFolderId)
            )
            ->where('original_filename', $filename)
            ->exists();
    }
}
