<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentDocumentRequest;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentLocation;
use App\Models\DocumentType;
use App\Services\AuditService;
use App\Services\DepartmentDocumentUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

        $documentLocations = DocumentLocation::query()->orderBy('name')->get();

        $query = Document::with(['department', 'documentType', 'documentLocation', 'documentFolder', 'uploader'])
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

        $defaultPerPage = 15;
        $perPage = $request->integer('per_page', $defaultPerPage);
        $perPage = max($defaultPerPage, min($perPage, 200));

        $documents = $query->paginate($perPage)->appends($request->except('document_id', 'page'));

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
            'documentLocations',
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
        if (mb_strlen($search) < 2) {
            return response()->json([
                'results' => [],
            ]);
        }

        $isGlobalSearch = $request->boolean('global_search');
        $selectedDepartmentId = (int) $request->integer('department_id');
        $hasScopedDepartment = $selectedDepartmentId > 0 && $accessibleDepartmentIds->contains($selectedDepartmentId);

        $documents = $this->searchDocumentsForSuggestions(
            $search,
            $accessibleDepartmentIds,
            $isGlobalSearch,
            $hasScopedDepartment ? $selectedDepartmentId : null
        );

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

    protected function searchDocumentsForSuggestions(
        string $search,
        Collection $accessibleDepartmentIds,
        bool $isGlobalSearch,
        ?int $scopedDepartmentId
    ): Collection {
        if ($this->shouldUseScoutSearch()) {
            try {
                return $this->searchWithScout($search, $accessibleDepartmentIds, $isGlobalSearch, $scopedDepartmentId);
            } catch (\Throwable $exception) {
                Log::warning('Scout document search failed. Falling back to SQL.', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $this->searchWithDatabase($search, $accessibleDepartmentIds, $isGlobalSearch, $scopedDepartmentId);
    }

    protected function shouldUseScoutSearch(): bool
    {
        $driver = (string) config('scout.driver', 'collection');

        return ! in_array($driver, ['null', 'collection', 'database'], true);
    }

    protected function searchWithScout(
        string $search,
        Collection $accessibleDepartmentIds,
        bool $isGlobalSearch,
        ?int $scopedDepartmentId
    ): Collection {
        return Document::search($search)
            ->whereIn('department_id', $accessibleDepartmentIds->all())
            ->when(! $isGlobalSearch && $scopedDepartmentId, function ($builder) use ($scopedDepartmentId) {
                return $builder->where('department_id', '=', $scopedDepartmentId);
            })
            ->take(8)
            ->query(function ($query) {
                $query
                    ->with([
                        'department:id,name',
                        'documentFolder:id,name,folder_code',
                    ])
                    ->select([
                        'id',
                        'department_id',
                        'document_folder_id',
                        'original_filename',
                        'updated_at',
                    ]);
            })
            ->get();
    }

    protected function searchWithDatabase(
        string $search,
        Collection $accessibleDepartmentIds,
        bool $isGlobalSearch,
        ?int $scopedDepartmentId
    ): Collection {
        $query = Document::query()
            ->with([
                'department:id,name',
                'documentFolder:id,name,folder_code',
            ])
            ->whereIn('department_id', $accessibleDepartmentIds->all())
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

        if (! $isGlobalSearch && $scopedDepartmentId) {
            $query->where('department_id', $scopedDepartmentId);
        }

        return $query
            ->limit(8)
            ->get(['id', 'department_id', 'document_folder_id', 'original_filename', 'updated_at']);
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

        AuditService::log('created', 'Department folder created.', $folder, [
            'before' => [],
            'after' => [
                'department_id' => $departmentId,
                'parent_id' => $parentId,
                'name' => $folder->name,
                'folder_code' => $folder->folder_code,
            ],
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
            'before' => [
                'name' => $oldName,
                'folder_code' => $oldCode,
            ],
            'after' => [
                'name' => $folderName,
                'folder_code' => $folderCode,
            ],
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

        AuditService::log('deleted', "Department folder deleted: {$folderName}.", $folder, [
            'before' => [
                'name' => $folderName,
                'folder_code' => $folder->folder_code,
                'department_id' => $departmentId,
                'parent_id' => $folder->parent_id,
            ],
            'after' => [],
        ]);

        $folder->delete();

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

    public function folderUpdateHistory(Request $request, DocumentFolder $folder): JsonResponse
    {
        $departmentId = (int) $folder->department_id;
        $this->authorize('createForDepartment', [Document::class, $departmentId]);

        $folderLogs = AuditLog::query()
            ->where('model_type', DocumentFolder::class)
            ->where('model_id', (int) $folder->id)
            ->whereIn('action', ['created', 'updated', 'deleted'])
            ->with('user')
            ->latest('created_at')
            ->get();

        $documentLogs = AuditLog::query()
            ->where('model_type', Document::class)
            ->whereIn('action', ['uploaded', 'updated', 'archived', 'restored', 'deleted'])
            ->with('user')
            ->latest('created_at')
            ->limit(1000)
            ->get();

        $documentIds = $documentLogs
            ->pluck('model_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $documentsById = Document::withTrashed()
            ->whereIn('id', $documentIds)
            ->get(['id', 'department_id', 'document_folder_id', 'original_filename'])
            ->keyBy('id');

        $relevantDocumentLogs = $documentLogs
            ->filter(fn (AuditLog $log) => $this->isDocumentAuditRelevantToFolder($log, $folder, $documentsById))
            ->values();

        $logs = $folderLogs
            ->map(function (AuditLog $log) {
                return [
                    'logged_at' => $log->created_at,
                    'user_name' => $log->user?->name ?: 'System',
                    'user_role' => $log->user?->role ?: 'System',
                    'date' => optional($log->created_at)->format('M d, Y') ?: '-',
                    'time' => optional($log->created_at)->format('h:i A') ?: '-',
                    'description' => 'Folder: '.($log->clean_description ?: ($log->description ?: ucfirst((string) $log->action))),
                    'changes' => $log->changes,
                ];
            })
            ->concat($relevantDocumentLogs->map(function (AuditLog $log) use ($documentsById) {
                $document = $documentsById->get((int) $log->model_id);
                $documentName = $document?->original_filename ?: ($log->target_name ?: 'Document');

                return [
                    'logged_at' => $log->created_at,
                    'user_name' => $log->user?->name ?: 'System',
                    'user_role' => $log->user?->role ?: 'System',
                    'date' => optional($log->created_at)->format('M d, Y') ?: '-',
                    'time' => optional($log->created_at)->format('h:i A') ?: '-',
                    'description' => "Document ({$documentName}): ".($log->clean_description ?: ($log->description ?: ucfirst((string) $log->action))),
                    'changes' => $log->changes,
                ];
            }))
            ->sortByDesc(fn (array $entry) => $entry['logged_at'])
            ->values()
            ->map(function (array $entry) {
                unset($entry['logged_at']);

                return $entry;
            });

        return response()->json($logs);
    }

    private function isDocumentAuditRelevantToFolder(AuditLog $log, DocumentFolder $folder, Collection $documentsById): bool
    {
        $folderId = (int) $folder->id;
        $folderName = mb_strtolower(trim((string) $folder->name));
        $document = $documentsById->get((int) $log->model_id);

        if ($document && (int) $document->department_id === (int) $folder->department_id && (int) ($document->document_folder_id ?? 0) === $folderId) {
            return true;
        }

        $changes = $log->changes;
        if (! is_array($changes)) {
            return false;
        }

        $before = is_array($changes['before'] ?? null) ? $changes['before'] : [];
        $after = is_array($changes['after'] ?? null) ? $changes['after'] : [];

        $beforeFolderId = isset($before['document_folder_id']) ? (int) $before['document_folder_id'] : null;
        $afterFolderId = isset($after['document_folder_id']) ? (int) $after['document_folder_id'] : null;
        if ($beforeFolderId === $folderId || $afterFolderId === $folderId) {
            return true;
        }

        $beforeFolderName = mb_strtolower(trim((string) ($before['document_folder'] ?? '')));
        $afterFolderName = mb_strtolower(trim((string) ($after['document_folder'] ?? '')));

        return ($beforeFolderName !== '' && $beforeFolderName === $folderName)
            || ($afterFolderName !== '' && $afterFolderName === $folderName);
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
            'document_type_id' => [
                'nullable',
                'integer',
                Rule::exists('document_types', 'id')->where(fn ($query) => $query->where('department_id', (int) $document->department_id)),
            ],
            'document_location_id' => ['nullable', 'integer', 'exists:document_locations,id'],
            'document_folder_id' => ['nullable', 'integer', 'exists:document_folders,id'],
            'date_received' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'expiry_change_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldName = $document->original_filename;
        $newName = trim($validated['original_filename']);

        // Ensure extension remains same or handle it
        $oldExt = pathinfo($oldName, PATHINFO_EXTENSION);
        $newExt = pathinfo($newName, PATHINFO_EXTENSION);

        if (strtolower($oldExt) !== strtolower($newExt) && $oldExt !== '') {
            $newName .= '.'.$oldExt;
        }

        $document->loadMissing(['documentType:id,name,has_expiry', 'documentLocation:id,name', 'documentFolder:id,name,folder_code']);

        $nextTypeId = array_key_exists('document_type_id', $validated)
            ? (int) $validated['document_type_id']
            : (int) $document->document_type_id;

        $nextLocationId = array_key_exists('document_location_id', $validated)
            ? (int) $validated['document_location_id']
            : (int) $document->document_location_id;

        $nextFolderId = $document->document_folder_id;
        if (array_key_exists('document_folder_id', $validated)) {
            $folderInput = $validated['document_folder_id'];
            $nextFolderId = ($folderInput === null || (int) $folderInput <= 0) ? null : (int) $folderInput;
        }

        $nextDateReceived = array_key_exists('date_received', $validated)
            ? $validated['date_received']
            : optional($document->date_received)->toDateString();

        $nextExpiryDate = array_key_exists('expiry_date', $validated)
            ? ($validated['expiry_date'] ?: null)
            : optional($document->expiry_date)->toDateString();

        $expiryChangeReason = isset($validated['expiry_change_reason'])
            ? trim((string) $validated['expiry_change_reason'])
            : '';

        if ($nextDateReceived && $nextExpiryDate && strtotime((string) $nextDateReceived) > strtotime((string) $nextExpiryDate)) {
            return back()->withErrors([
                'date_received' => 'Date received must be on or before the expiry date.',
            ])->withInput();
        }

        $nextType = DocumentType::query()->find($nextTypeId);
        if ($nextType && (bool) $nextType->has_expiry && ! $nextExpiryDate) {
            return back()->withErrors([
                'expiry_date' => 'Expiry date is required for the selected document type.',
            ])->withInput();
        }

        $currentExpiryDate = optional($document->expiry_date)->toDateString();
        if ($currentExpiryDate !== $nextExpiryDate && $expiryChangeReason === '') {
            return back()->withErrors([
                'expiry_change_reason' => 'Please provide a reason when changing expiry date.',
            ])->withInput();
        }

        if ($nextFolderId) {
            $folderBelongsToDepartment = DocumentFolder::query()
                ->whereKey($nextFolderId)
                ->where('department_id', (int) $document->department_id)
                ->exists();

            if (! $folderBelongsToDepartment) {
                return back()->withErrors([
                    'document_folder_id' => 'The selected virtual folder does not belong to this document department.',
                ])->withInput();
            }
        }

        $newName = $this->resolveUniqueFilenameForDocument(
            document: $document,
            desiredFilename: $newName,
            ignoreDocumentId: (int) $document->id,
            folderIdOverride: $nextFolderId
        );

        $nextLocation = DocumentLocation::query()->find($nextLocationId);
        $nextFolder = $nextFolderId ? DocumentFolder::query()->find($nextFolderId) : null;

        $before = [];
        $after = [];

        $track = static function (array &$beforeMap, array &$afterMap, string $key, mixed $oldValue, mixed $newValue): void {
            $old = $oldValue === null ? null : (string) $oldValue;
            $new = $newValue === null ? null : (string) $newValue;

            if ($old !== $new) {
                $beforeMap[$key] = $oldValue;
                $afterMap[$key] = $newValue;
            }
        };

        $track($before, $after, 'original_filename', $document->original_filename, $newName);
        $track($before, $after, 'document_type', $document->documentType?->name, $nextType?->name);
        $track($before, $after, 'document_location', $document->documentLocation?->name, $nextLocation?->name);
        $track($before, $after, 'document_folder', $document->documentFolder?->name, $nextFolder?->name);
        $track(
            $before,
            $after,
            'date_received',
            optional($document->date_received)->format('Y-m-d'),
            $nextDateReceived
        );
        $track(
            $before,
            $after,
            'expiry_date',
            optional($document->expiry_date)->format('Y-m-d'),
            $nextExpiryDate
        );

        if ($currentExpiryDate !== $nextExpiryDate && $expiryChangeReason !== '') {
            $after['expiry_change_reason'] = $expiryChangeReason;
        }

        if (empty($after)) {
            return back()->with('success', 'No changes were made.');
        }

        Document::withoutSyncingToSearch(function () use (
            $document,
            $newName,
            $nextTypeId,
            $nextLocationId,
            $nextFolderId,
            $nextDateReceived,
            $nextExpiryDate
        ): void {
            $document->update([
                'original_filename' => $newName,
                'document_type_id' => $nextTypeId,
                'document_location_id' => $nextLocationId,
                'document_folder_id' => $nextFolderId,
                'date_received' => $nextDateReceived,
                'expiry_date' => $nextExpiryDate,
            ]);
        });

        $isRenameOnly = count($after) === 1 && array_key_exists('original_filename', $after);

        AuditService::log(
            'updated',
            $isRenameOnly ? "Document renamed from '{$oldName}' to '{$newName}'." : 'Document details updated.',
            $document,
            [
                'before' => $before,
                'after' => $after,
                'document_folder_id' => $nextFolderId,
            ]
        );

        return back()->with('success', $isRenameOnly ? 'Document renamed successfully.' : 'Document details updated successfully.');
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

    public function updateHistory(Document $document): JsonResponse
    {
        $this->authorize('download', $document);

        $logs = AuditLog::query()
            ->where('model_type', Document::class)
            ->where('model_id', $document->id)
            ->whereIn('action', ['uploaded', 'updated', 'archived', 'restored'])
            ->with('user')
            ->latest('created_at')
            ->get();

        return response()->json($logs->map(function ($log) {
            return [
                'user_name' => $log->user?->name ?: 'System',
                'user_role' => $log->user?->role ?: 'System',
                'date' => optional($log->created_at)->format('M d, Y') ?: '-',
                'time' => optional($log->created_at)->format('h:i A') ?: '-',
                'description' => $log->clean_description ?: ($log->description ?: ucfirst((string) $log->action)),
                'changes' => $log->changes,
            ];
        })->values());
    }

    private function resolveUniqueFilenameForDocument(
        Document $document,
        string $desiredFilename,
        ?int $ignoreDocumentId = null,
        ?int $folderIdOverride = null
    ): string {
        $original = trim((string) $desiredFilename);
        $original = $original !== '' ? $original : 'file';

        if (! $this->activeFilenameExistsForLocation($document, $original, $ignoreDocumentId, $folderIdOverride)) {
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

            if (! $this->activeFilenameExistsForLocation($document, $candidate, $ignoreDocumentId, $folderIdOverride)) {
                return $candidate;
            }

            $counter++;
        }
    }

    private function activeFilenameExistsForLocation(
        Document $document,
        string $filename,
        ?int $ignoreDocumentId = null,
        ?int $folderIdOverride = null
    ): bool {
        $targetFolderId = func_num_args() >= 4 ? $folderIdOverride : $document->document_folder_id;

        return Document::query()
            ->where('department_id', (int) $document->department_id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->when(
                $targetFolderId === null,
                fn ($query) => $query->whereNull('document_folder_id'),
                fn ($query) => $query->where('document_folder_id', (int) $targetFolderId)
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

        AuditService::log('deleted', "Department document permanently deleted: {$filename}.", $document, [
            'before' => [
                'original_filename' => $document->original_filename,
                'department_id' => $document->department_id,
                'document_type_id' => $document->document_type_id,
                'document_folder_id' => $document->document_folder_id,
                'document_location_id' => $document->document_location_id,
                'status' => $document->status,
                'file_path' => $document->file_path,
            ],
            'after' => [],
        ]);

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
