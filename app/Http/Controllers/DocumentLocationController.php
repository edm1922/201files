<?php

namespace App\Http\Controllers;

use App\Models\DocumentLocation;
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

        DocumentLocation::create([
            'name' => $name,
        ]);

        return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations'])
            ->with('success', "Document location '{$name}' created successfully.");
    }

    public function destroy(DocumentLocation $documentLocation): RedirectResponse
    {
        if ($documentLocation->documents()->exists()) {
            return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations'])
                ->with('error', "Cannot delete '{$documentLocation->name}' because it is assigned to existing documents.");
        }

        $name = $documentLocation->name;
        $documentLocation->delete();

        return redirect()->route('settings.folder-locations.index', ['tab' => 'document-locations'])
            ->with('success', "Document location '{$name}' deleted successfully.");
    }
}
