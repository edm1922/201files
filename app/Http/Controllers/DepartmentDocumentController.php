<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentDocumentRequest;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentType;
use App\Models\FolderLocation;
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
            $query->where('original_filename', 'like', '%' . $request->search . '%');
        }

        $documents = $query->paginate(20)->withQueryString();

        return view('department-documents.index', compact(
            'departments',
            'selectedDepartmentId',
            'documentTypes',
            'allFolders',
            'foldersByParent',
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
        ]);

        $departmentId = (int) $validated['department_id'];
        $this->authorize('createForDepartment', [Document::class, $departmentId]);

        $parentId = isset($validated['parent_id']) ? (int) $validated['parent_id'] : null;
        if ($parentId) {
            $parentFolder = DocumentFolder::query()->find($parentId);

            if (! $parentFolder || (int) $parentFolder->department_id !== $departmentId) {
                return back()->withErrors([
                    'name' => 'The selected parent folder is invalid for the selected department.',
                ])->withInput();
            }
        }

        $folderName = trim((string) $validated['name']);
        if ($folderName === '') {
            return back()->withErrors([
                'name' => 'Folder name is required.',
            ])->withInput();
        }

        $duplicate = DocumentFolder::query()
            ->where('department_id', $departmentId)
            ->where('parent_id', $parentId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($folderName)])
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'name' => 'A folder with this name already exists in this location.',
            ])->withInput();
        }

        $folder = DocumentFolder::create([
            'department_id' => $departmentId,
            'parent_id' => $parentId,
            'name' => $folderName,
        ]);

        return redirect()
            ->route('department-documents.index', [
                'department_id' => $departmentId,
                'document_folder_id' => $folder->id,
            ])
            ->with('success', 'Folder created successfully.');
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

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path, $document->original_filename);
    }
}
