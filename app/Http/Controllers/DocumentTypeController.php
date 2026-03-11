<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentTypeRequest;
use App\Models\Department;
use App\Models\DocumentType;
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
        $departments   = Department::orderBy('name')->get();

        return view('document-types.index', compact('documentTypes', 'departments'));
    }

    /**
     * Show the form for creating a new document type.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('document-types.create', compact('departments'));
    }

    /**
     * Store a newly created document type in storage.
     */
    public function store(DocumentTypeRequest $request)
    {
        DocumentType::create($request->validated());

        return redirect()
            ->route('settings.document-types.index')
            ->with('success', 'Document type created successfully.');
    }

    /**
     * Show the form for editing the specified document type.
     */
    public function edit(DocumentType $documentType)
    {
        $departments = Department::orderBy('name')->get();

        return view('document-types.edit', compact('documentType', 'departments'));
    }

    /**
     * Update the specified document type in storage.
     */
    public function update(DocumentTypeRequest $request, DocumentType $documentType)
    {
        $documentType->update($request->validated());

        return redirect()
            ->route('settings.document-types.index')
            ->with('success', 'Document type updated successfully.');
    }
}
