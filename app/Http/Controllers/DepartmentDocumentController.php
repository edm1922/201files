<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentDocumentRequest;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use App\Services\AuditService;
use App\Services\DepartmentDocumentUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepartmentDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $accessibleDepartmentIds = $user->isAdmin()
            ? Department::query()->where('is_active', true)->pluck('id')
            : $user->authorizedDepartments()->where('is_active', true)->pluck('departments.id');

        $departments = Department::query()
            ->whereIn('id', $accessibleDepartmentIds)
            ->orderBy('name')
            ->get();

        $selectedDepartmentId = (int) $request->integer('department_id');
        if ($selectedDepartmentId <= 0 || ! $accessibleDepartmentIds->contains($selectedDepartmentId)) {
            $selectedDepartmentId = (int) ($departments->first()?->id ?? 0);
        }

        $selectedDepartment = $departments->firstWhere('id', $selectedDepartmentId);
        $selectedDepartmentName = $selectedDepartment?->name;

        $documentTypes = $selectedDepartmentId > 0
            ? DocumentType::query()->where('department_id', $selectedDepartmentId)->orderBy('name')->get()
            : collect();

        $allFolders = $selectedDepartmentId > 0
            ? DocumentFolder::query()->where('department_id', $selectedDepartmentId)->orderBy('name')->get()
            : collect();

        $currentFolderId = (int) $request->integer('document_folder_id');
        $currentFolder = $currentFolderId > 0 ? $allFolders->firstWhere('id', $currentFolderId) : null;
        if ($currentFolderId > 0 && ! $currentFolder) {
            $currentFolderId = 0;
        }

        $folderLookup = $allFolders->keyBy('id');
        $foldersByParent = $allFolders->groupBy(fn (DocumentFolder $folder) => (int) ($folder->parent_id ?? 0));
        $folderPathMaps = $this->buildFolderPathMaps($allFolders);

        $folderBreadcrumbs = collect();
        if ($currentFolder) {
            $chain = [];
            $cursor = $currentFolder;
            $safety = 0;

            while ($cursor && $safety < 20) {
                array_unshift($chain, $cursor);
                $cursor = $cursor->parent_id ? $folderLookup->get($cursor->parent_id) : null;
                $safety++;
            }

            $folderBreadcrumbs = collect($chain);
        }

        $folderLocations = FolderLocation::query()->orderByRaw('LENGTH(row_name) ASC')->orderBy('row_name', 'ASC')->get();

        $query = Document::with(['department', 'documentType', 'folderLocation', 'documentFolder', 'uploader'])
            ->whereIn('department_id', $accessibleDepartmentIds);

        if ($selectedDepartmentId > 0) {
            $query->where('department_id', $selectedDepartmentId);
        }

        if ($currentFolderId > 0) {
            $query->where('document_folder_id', $currentFolderId);
        }

        $selectedDocumentId = (int) $request->integer('document_id');

        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', (int) $request->input('document_type_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $searchLower = mb_strtolower($search);

            $matchingFolderIds = collect($folderPathMaps)
                ->filter(function (array $map) use ($searchLower): bool {
                    return str_contains(mb_strtolower($map['name_path']), $searchLower)
                        || str_contains(mb_strtolower($map['code_path']), $searchLower)
                        || str_contains(mb_strtolower($map['display_path']), $searchLower);
                })
                ->keys()
                ->map(fn ($id) => (int) $id)
                ->values();

            $matchesDeptName = $selectedDepartmentName
                ? str_contains(mb_strtolower($selectedDepartmentName), $searchLower)
                : false;

            $query->where(function ($q) use ($search, $matchingFolderIds, $matchesDeptName) {
                $q->where('original_filename', 'like', '%'.$search.'%')
                    ->orWhereHas('documentFolder', function ($folderQuery) use ($search) {
                        $folderQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('folder_code', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('department', function ($departmentQuery) use ($search) {
                        $departmentQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('code', 'like', '%'.$search.'%');
                    });

                if ($matchingFolderIds->isNotEmpty()) {
                    $q->orWhereIn('document_folder_id', $matchingFolderIds->all());
                }

                if ($matchesDeptName) {
                    $q->orWhereNull('document_folder_id');
                }
            });
        }

        if ($selectedDocumentId > 0) {
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$selectedDocumentId]);
        }

        $query->latest();

        $documents = $query->paginate(20)->appends($request->except('document_id'));

        return view('department-documents.index', compact(
            'departments',
            'selectedDepartmentId',
            'selectedDepartmentName',
            'documentTypes',
            'allFolders',
            'foldersByParent',
            'folderPathMaps',
            'currentFolder',
            'currentFolderId',
            'folderBreadcrumbs',
            'folderLocations',
            'documents'
        ));
    }

    public function search(Request $request): JsonResponse
    {
        $user = $request->user();

        $accessibleDepartmentIds = $user->isAdmin()
            ? Department::query()->where('is_active', true)->pluck('id')
            : $user->authorizedDepartments()->where('is_active', true)->pluck('departments.id');

        $search = trim((string) $request->query('q', ''));
        if ($search === '') {
            return response()->json([
                'results' => [],
            ]);
        }

        $isGlobalSearch = $request->boolean('global_search');
        $selectedDepartmentId = (int) $request->integer('department_id');
        $hasScopedDepartment = $selectedDepartmentId > 0 && $accessibleDepartmentIds->contains($selectedDepartmentId);

        $query = Document::query()
            ->with([
                'department:id,name',
                'documentFolder:id,name,folder_code',
            ])
            ->whereIn('department_id', $accessibleDepartmentIds)
            ->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', '%'.$search.'%')
                    ->orWhereHas('documentFolder', function ($folderQuery) use ($search) {
                        $folderQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('folder_code', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('department', function ($departmentQuery) use ($search) {
                        $departmentQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('code', 'like', '%'.$search.'%');
                    });
            })
            ->latest();

        if (! $isGlobalSearch && $hasScopedDepartment) {
            $query->where('department_id', $selectedDepartmentId);
        }

        $documents = $query
            ->limit(8)
            ->get(['id', 'department_id', 'document_folder_id', 'original_filename', 'updated_at']);

        $results = $documents->map(function (Document $document) {
            $redirectParams = [
                'department_id' => (int) $document->department_id,
                'document_id' => (int) $document->id,
            ];

            if ($document->document_folder_id) {
                $redirectParams['document_folder_id'] = (int) $document->document_folder_id;
            }

            $folderLabel = $document->documentFolder?->name ?: 'Root';
            if ($document->documentFolder?->folder_code) {
                $folderLabel .= ' ('.$document->documentFolder->folder_code.')';
            }

            return [
                'id' => (int) $document->id,
                'title' => $document->original_filename,
                'department_name' => $document->department?->name,
                'folder_label' => $folderLabel,
                'updated_at' => $document->updated_at?->format('M j, Y g:i A'),
                'url' => route('department-documents.index', $redirectParams),
            ];
        })->values();

        return response()->json([
            'results' => $results,
        ]);
    }

    public function store(StoreDepartmentDocumentRequest $request, DepartmentDocumentUploadService $service)
    {
        $validated = $request->validated();
        $this->authorize('createForDepartment', [Document::class, (int) $validated['department_id']]);

        try {
            $document = $service->upload($validated, $request->user());
            $modeLabel = $document->upload_mode === 'scan_packet' ? 'scan packet merged to PDF' : 'file uploaded';

            $redirectParams = [
                'department_id' => (int) $validated['department_id'],
            ];

            if (! empty($validated['document_folder_id'])) {
                $redirectParams['document_folder_id'] = (int) $validated['document_folder_id'];
            }

            return redirect()
                ->route('department-documents.index', $redirectParams)
                ->with('success', "Department document uploaded successfully ({$modeLabel}).");
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return back()->withErrors(['files' => 'Unable to upload department documents.'])->withInput();
        }
    }

    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'parent_id' => ['nullable', 'integer', 'exists:document_folders,id'],
            'name' => ['required', 'string', 'max:120'],
            'folder_code' => ['nullable', 'string', 'max:20'],
        ]);

        $departmentId = (int) $validated['department_id'];
        $this->authorize('createForDepartment', [Document::class, $departmentId]);

        $parentId = isset($validated['parent_id']) ? (int) $validated['parent_id'] : null;
        if ($parentId) {
            $parentFolder = DocumentFolder::query()->find($parentId);

            if (! $parentFolder || (int) $parentFolder->department_id !== $departmentId) {
                return $this->folderValidationError($request, 'The selected parent folder is invalid for the selected department.');
            }
        }

        $folderName = trim((string) $validated['name']);
        if ($folderName === '') {
            return $this->folderValidationError($request, 'Folder name is required.');
        }

        $folderCode = isset($validated['folder_code']) ? trim((string) $validated['folder_code']) : null;
        if ($folderCode === '') {
            $folderCode = null;
        }

        // Check for duplicate name in the same parent
        $duplicateName = DocumentFolder::query()
            ->where('department_id', $departmentId)
            ->where('parent_id', $parentId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($folderName)])
            ->exists();

        if ($duplicateName) {
            return $this->folderValidationError($request, 'A folder with this name already exists in this location.');
        }

        $folder = DocumentFolder::create([
            'department_id' => $departmentId,
            'parent_id' => $parentId,
            'name' => $folderName,
            'folder_code' => $folderCode,
        ]);

        return $this->folderActionSuccess(
            $request,
            'Folder created successfully.',
            [
                'department_id' => $departmentId,
                'document_folder_id' => $folder->id,
            ],
            $folder,
            201
        );
    }

    public function updateFolder(Request $request, DocumentFolder $folder)
    {
        $departmentId = (int) $folder->department_id;
        $this->authorize('createForDepartment', [Document::class, $departmentId]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'folder_code' => ['nullable', 'string', 'max:40'],
        ]);

        $folderName = trim((string) $validated['name']);
        if ($folderName === '') {
            return $this->folderValidationError($request, 'Folder name is required.');
        }

        $folderCode = isset($validated['folder_code']) ? trim((string) $validated['folder_code']) : null;
        if ($folderCode === '') {
            $folderCode = null;
        }

        // Check for duplicate name in the same parent (excluding itself)
        $duplicate = DocumentFolder::query()
            ->where('department_id', $departmentId)
            ->where('parent_id', $folder->parent_id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($folderName)])
            ->whereKeyNot($folder->id)
            ->exists();

        if ($duplicate) {
            return $this->folderValidationError($request, 'A folder with this name already exists in this location.');
        }

        $oldName = $folder->name;
        $oldCode = $folder->folder_code;

        $folder->update([
            'name' => $folderName,
            'folder_code' => $folderCode,
        ]);

        AuditService::log('updated', "Department folder updated: {$oldName}.", $folder, [
            'old_name' => $oldName,
            'new_name' => $folderName,
            'old_code' => $oldCode,
            'new_code' => $folderCode,
        ]);

        return $this->folderActionSuccess(
            $request,
            'Folder renamed successfully.',
            [
                'department_id' => $departmentId,
                'document_folder_id' => $folder->id,
            ],
            $folder
        );
    }

    public function destroyFolder(Request $request, DocumentFolder $folder)
    {
        $departmentId = (int) $folder->department_id;
        $this->authorize('createForDepartment', [Document::class, $departmentId]);

        $hasChildren = DocumentFolder::query()->where('parent_id', $folder->id)->exists();
        $hasDocuments = Document::withTrashed()->where('document_folder_id', $folder->id)->exists();

        if ($hasChildren || $hasDocuments) {
            $message = $hasChildren && $hasDocuments
                ? 'Folder cannot be deleted because it has subfolders and documents.'
                : ($hasChildren
                    ? 'Folder cannot be deleted because it has subfolders.'
                    : 'Folder cannot be deleted because it contains documents.');

            return $this->folderValidationError($request, $message);
        }

        $parentFolderId = $folder->parent_id ? (int) $folder->parent_id : null;
        $folderName = $folder->name;

        $folder->delete();

        AuditService::log('deleted', "Department folder deleted: {$folderName}.", null, [
            'folder_name' => $folderName,
            'department_id' => $departmentId,
        ]);

        $redirectParams = ['department_id' => $departmentId];
        if ($parentFolderId) {
            $redirectParams['document_folder_id'] = $parentFolderId;
        }

        return $this->folderActionSuccess(
            $request,
            "Folder {$folderName} deleted successfully.",
            $redirectParams,
            null
        );
    }

    public function archive(Document $document)
    {
        $this->authorize('archive', $document);

        $document->update(['status' => 'archived']);
        $document->delete();

        AuditService::logDepartmentDocumentLifecycle('archived', $document);

        return back()->with('success', 'Document archived successfully.');
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('download', $document); // Use download permission as proxy for department access

        $validated = $request->validate([
            'original_filename' => ['required', 'string', 'max:255'],
        ]);

        $oldName = $document->original_filename;
        $newName = trim($validated['original_filename']);

        // Ensure extension remains same or handle it
        $oldExt = pathinfo($oldName, PATHINFO_EXTENSION);
        $newExt = pathinfo($newName, PATHINFO_EXTENSION);

        if (strtolower($oldExt) !== strtolower($newExt) && $oldExt !== '') {
            $newName .= '.'.$oldExt;
        }

        $newName = $this->resolveUniqueFilenameForDocument(
            document: $document,
            desiredFilename: $newName,
            ignoreDocumentId: (int) $document->id
        );

        $document->update([
            'original_filename' => $newName,
        ]);

        AuditService::log('updated', "Document renamed from '{$oldName}' to '{$newName}'.", $document, [
            'old_name' => $oldName,
            'new_name' => $newName,
        ]);

        return back()->with('success', 'Document renamed successfully.');
    }

    public function restore($id)
    {
        $document = Document::withTrashed()->findOrFail($id);
        $this->authorize('restore', $document);

        $restoredFilename = $this->resolveUniqueFilenameForDocument($document, $document->original_filename);

        $document->restore();
        $document->update([
            'status' => 'active',
            'original_filename' => $restoredFilename,
        ]);

        AuditService::logDepartmentDocumentLifecycle('restored', $document);

        return back()->with('success', 'Document restored successfully.');
    }

    private function resolveUniqueFilenameForDocument(Document $document, string $desiredFilename, ?int $ignoreDocumentId = null): string
    {
        $original = trim((string) $desiredFilename);
        $original = $original !== '' ? $original : 'file';

        if (! $this->activeFilenameExistsForLocation($document, $original, $ignoreDocumentId)) {
            return $original;
        }

        $extension = pathinfo($original, PATHINFO_EXTENSION);
        $baseName = pathinfo($original, PATHINFO_FILENAME);
        $baseName = $baseName !== '' ? $baseName : 'file';
        $extensionWithDot = $extension !== '' ? '.'.$extension : '';

        $counter = 1;
        while (true) {
            $suffix = " ({$counter})";
            $maxBaseLength = max(1, 255 - strlen($suffix) - strlen($extensionWithDot));
            $truncatedBase = mb_substr($baseName, 0, $maxBaseLength);
            $candidate = "{$truncatedBase}{$suffix}{$extensionWithDot}";

            if (! $this->activeFilenameExistsForLocation($document, $candidate, $ignoreDocumentId)) {
                return $candidate;
            }

            $counter++;
        }
    }

    private function activeFilenameExistsForLocation(Document $document, string $filename, ?int $ignoreDocumentId = null): bool
    {
        return Document::query()
            ->where('department_id', (int) $document->department_id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->when(
                $document->document_folder_id === null,
                fn ($query) => $query->whereNull('document_folder_id'),
                fn ($query) => $query->where('document_folder_id', (int) $document->document_folder_id)
            )
            ->when(
                $ignoreDocumentId !== null,
                fn ($query) => $query->whereKeyNot($ignoreDocumentId)
            )
            ->where('original_filename', $filename)
            ->exists();
    }

    public function forceDelete(Request $request, $id)
    {
        $document = Document::withTrashed()->findOrFail($id);

        // Only admins can permanently delete
        if (! $request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $this->authorize('restore', $document); // Use restore permission as baseline for department access

        $filename = $document->original_filename;

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->forceDelete();

        return back()->with('success', "Document '{$filename}' permanently deleted.");
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path, $document->original_filename);
    }

    public function preview(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        $mimeType = (string) ($document->mime_type ?: Storage::disk('local')->mimeType($document->file_path) ?: 'application/octet-stream');
        if (! $this->isPreviewableMime($mimeType)) {
            abort(415);
        }

        return response()->stream(function () use ($document) {
            $stream = Storage::disk('local')->readStream($document->file_path);
            if (! $stream) {
                return;
            }

            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.addslashes($document->original_filename).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    protected function folderValidationError(Request $request, string $message, string $field = 'name'): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'errors' => [
                    $field => [$message],
                ],
            ], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }

    protected function folderActionSuccess(
        Request $request,
        string $message,
        array $redirectParams,
        ?DocumentFolder $folder = null,
        int $statusCode = 200
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'folder' => $folder ? [
                    'id' => (int) $folder->id,
                    'name' => $folder->name,
                    'folder_code' => $folder->folder_code,
                    'department_id' => (int) $folder->department_id,
                    'parent_id' => $folder->parent_id ? (int) $folder->parent_id : null,
                ] : null,
                'redirect_url' => route('department-documents.index', $redirectParams),
            ], $statusCode);
        }

        return redirect()
            ->route('department-documents.index', $redirectParams)
            ->with('success', $message);
    }

    protected function isPreviewableMime(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/')
            || $mimeType === 'application/pdf';
    }

    protected function buildFolderPathMaps($allFolders): array
    {
        $folderLookup = $allFolders->keyBy('id');
        $maps = [];

        foreach ($allFolders as $folder) {
            $chain = [];
            $cursor = $folder;
            $safety = 0;

            while ($cursor && $safety < 20) {
                array_unshift($chain, $cursor);
                $cursor = $cursor->parent_id ? $folderLookup->get($cursor->parent_id) : null;
                $safety++;
            }

            $nameSegments = array_map(fn (DocumentFolder $segment) => (string) ($segment->name ?? ''), $chain);
            $codeSegments = array_values(array_filter(array_map(fn (DocumentFolder $segment) => (string) ($segment->folder_code ?? ''), $chain)));
            $displaySegments = array_map(function (DocumentFolder $segment) {
                return $segment->name ?: (string) $segment->folder_code;
            }, $chain);

            $maps[(int) $folder->id] = [
                'name_path' => implode(' / ', $nameSegments),
                'code_path' => implode(' / ', $codeSegments),
                'display_path' => implode(' / ', $displaySegments),
            ];
        }

        return $maps;
    }
}
