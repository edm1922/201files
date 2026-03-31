<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentDocumentRequest;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $folderLocations = FolderLocation::query()->orderBy('row_name')->orderBy('column_code')->get();

        $query = Document::with(['department', 'documentType', 'folderLocation', 'documentFolder'])
            ->whereIn('department_id', $accessibleDepartmentIds)
            ->latest();

        if ($selectedDepartmentId > 0) {
            $query->where('department_id', $selectedDepartmentId);
        }

        if ($currentFolderId > 0) {
            $query->where('document_folder_id', $currentFolderId);
        }

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
                $q->where('original_filename', 'like', '%' . $search . '%')
                    ->orWhereHas('documentFolder', function ($folderQuery) use ($search) {
                        $folderQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('folder_code', 'like', '%' . $search . '%');
                    });

                if ($matchingFolderIds->isNotEmpty()) {
                    $q->orWhereIn('document_folder_id', $matchingFolderIds->all());
                }

                if ($matchesDeptName) {
                    $q->orWhereNull('document_folder_id');
                }
            });
        }

        $documents = $query->paginate(20)->withQueryString();

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

    public function store(StoreDepartmentDocumentRequest $request, \App\Services\DepartmentDocumentUploadService $service)
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
            \Illuminate\Support\Facades\Log::error($e->getMessage());

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

        if ($folderCode) {
            $duplicateCode = DocumentFolder::query()
                ->where('department_id', $departmentId)
                ->whereRaw('LOWER(folder_code) = ?', [mb_strtolower($folderCode)])
                ->exists();

            if ($duplicateCode) {
                return $this->folderValidationError($request, 'Folder code already exists in this department.', 'folder_code');
            }
        }

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

        if ($folderCode) {
            $duplicateCode = DocumentFolder::query()
                ->where('department_id', $departmentId)
                ->whereRaw('LOWER(folder_code) = ?', [mb_strtolower($folderCode)])
                ->whereKeyNot($folder->id)
                ->exists();

            if ($duplicateCode) {
                return $this->folderValidationError($request, 'Folder code already exists in this department.', 'folder_code');
            }
        }

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

        \App\Services\AuditService::log('updated', "Department folder updated: {$oldName}.", $folder, [
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

        \App\Services\AuditService::log('deleted', "Department folder deleted: {$folderName}.", null, [
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

        \App\Services\AuditService::logDepartmentDocumentLifecycle('archived', $document);

        return back()->with('success', 'Document archived successfully.');
    }

    public function restore($id)
    {
        $document = Document::withTrashed()->findOrFail($id);
        $this->authorize('restore', $document);

        $document->restore();
        $document->update(['status' => 'active']);

        \App\Services\AuditService::logDepartmentDocumentLifecycle('restored', $document);

        return back()->with('success', 'Document restored successfully.');
    }

    public function forceDelete(Request $request, $id)
    {
        $document = Document::withTrashed()->findOrFail($id);
        
        // Only admins can permanently delete
        if (!$request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $this->authorize('restore', $document); // Use restore permission as baseline for department access

        $filename = $document->original_filename;
        
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($document->file_path);
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
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    protected function folderValidationError(Request $request, string $message, string $field = 'name'): JsonResponse|\Illuminate\Http\RedirectResponse
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
    ): JsonResponse|\Illuminate\Http\RedirectResponse {
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

            $nameSegments = array_map(fn (DocumentFolder $segment) => $segment->name, $chain);
            $codeSegments = array_values(array_filter(array_map(fn (DocumentFolder $segment) => (string) $segment->folder_code, $chain)));
            $displaySegments = array_map(function (DocumentFolder $segment): string {
                return $segment->name;
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
