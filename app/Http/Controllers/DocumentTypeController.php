<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentTypeRequest;
use App\Models\Department;
use App\Models\DocumentType;
use App\Services\AuditService;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    /**
     * Display a paginated list of document types.
     */
    public function index(Request $request)
    {
        $query = DocumentType::with('department')->withCount('documents');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        $documentTypes = $query->orderBy('name')->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('document-types.index', compact('documentTypes', 'departments'));
    }

    /**
     * Store a newly created document type in storage.
     */
    public function store(DocumentTypeRequest $request)
    {
        $documentType = DocumentType::create($request->validated());

        AuditService::log('created', 'Created document type.', $documentType, [
            'before' => [],
            'after' => [
                'name' => $documentType->name,
                'code' => $documentType->code,
                'department_id' => $documentType->department_id,
            ],
        ]);

        return redirect()
            ->route('settings.document-types.index')
            ->with('success', 'Document type created successfully.');
    }

    /**
     * Update the specified document type in storage.
     */
    public function update(DocumentTypeRequest $request, DocumentType $documentType)
    {
        $before = $documentType->only(['name', 'code', 'department_id']);

        $documentType->update($request->validated());

        $after = $documentType->fresh()->only(['name', 'code', 'department_id']);

        AuditService::log('updated', 'Updated document type.', $documentType, [
            'before' => $before,
            'after' => $after,
        ]);

        return redirect()
            ->route('settings.document-types.index')
            ->with('success', 'Document type updated successfully.');
    }

    /**
     * Remove the specified document type from storage.
     */
    public function destroy(DocumentType $documentType)
    {
        // Prevent deletion if the document type is already in use
        $documentsCount = $documentType->documents()->count();

        if ($documentsCount > 0) {
            return back()->with('error', 'This document type cannot be deleted because it is currently used by one or more uploaded documents.');
        }

        $snapshot = [
            'name' => $documentType->name,
            'code' => $documentType->code,
            'department_id' => $documentType->department_id,
            'documents_count_at_delete' => $documentsCount,
        ];

        $documentType->delete();

        AuditService::log('deleted', 'Deleted document type.', $documentType, [
            'before' => $snapshot,
            'after' => [],
        ]);

        return redirect()
            ->route('settings.document-types.index')
            ->with('success', 'Document type deleted successfully.');
    }
}
