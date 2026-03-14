<?php

namespace App\Http\Controllers;

use App\Models\FolderLocation;
use Illuminate\Http\Request;

class FolderLocationController extends Controller
{
    /**
     * Display a listing of folder locations.
     */
    public function index()
    {
        $rows = FolderLocation::orderBy('row_name')
            ->orderBy('column_code')
            ->get()
            ->groupBy('row_name');

        return view('folder_locations.index', compact('rows'));
    }

    /**
     * Store a newly created folder location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'row_name'    => 'required|string|max:10',
            'column_code' => 'required|string|max:10',
            'folder_code' => 'required|string|unique:folder_locations,folder_code',
        ]);

        FolderLocation::create($validated);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', 'Folder location created successfully.');
    }

    /**
     * Update the specified folder location in storage.
     */
    public function update(Request $request, FolderLocation $folderLocation)
    {
        $validated = $request->validate([
            'row_name'    => 'required|string|max:10',
            'column_code' => 'required|string|max:10',
            'folder_code' => 'required|string|unique:folder_locations,folder_code,' . $folderLocation->id,
            'is_available'=> 'required|boolean',
        ]);

        $folderLocation->update($validated);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', 'Folder location updated successfully.');
    }

    /**
     * Remove the specified folder location from storage.
     */
    public function destroy(FolderLocation $folderLocation)
    {
        // Check if occupied
        if ($folderLocation->employee()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete a location that is currently occupied by an employee.');
        }

        $folderLocation->delete();

        return redirect()->route('settings.folder-locations.index')
            ->with('success', 'Folder location deleted successfully.');
    }
}
