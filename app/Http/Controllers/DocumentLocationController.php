<?php

namespace App\Http\Controllers;

use App\Models\DocumentLocation;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentLocationController extends Controller
{
    public function index()
    {
        return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:document_locations,name'],
        ]);

        $name = trim((string) $validated['name']);

        $documentLocation = DocumentLocation::create([
            'name' => $name,
        ]);

        AuditService::log('created', 'Created document location.', $documentLocation, [
            'before' => [],
            'after' => [
                'name' => $documentLocation->name,
            ],
        ]);

        return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations'])
            ->with('success', "Document location '{$name}' created successfully.");
    }

    public function update(Request $request, DocumentLocation $documentLocation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:document_locations,name,'.$documentLocation->id],
        ]);

        $name = trim((string) $validated['name']);
        $before = [
            'name' => $documentLocation->name,
        ];

        $documentLocation->update([
            'name' => $name,
        ]);

        AuditService::log('updated', 'Updated document location.', $documentLocation, [
            'before' => $before,
            'after' => [
                'name' => $name,
            ],
        ]);

        return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations'])
            ->with('success', "Document location updated to '{$name}' successfully.");
    }

    public function destroy(DocumentLocation $documentLocation): RedirectResponse
    {
        $documentsCount = $documentLocation->documents()->count();

        if ($documentsCount > 0) {
            return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations'])
                ->with('error', "This location cannot be deleted because it is currently assigned to one or more documents. Please move the documents to another location first.");
        }

        $name = $documentLocation->name;
        $snapshot = [
            'name' => $name,
            'documents_count_at_delete' => $documentsCount,
        ];

        $documentLocation->delete();

        AuditService::log('deleted', 'Deleted document location.', $documentLocation, [
            'before' => $snapshot,
            'after' => [],
        ]);

        return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations'])
            ->with('success', "Document location '{$name}' deleted successfully.");
    }
}
