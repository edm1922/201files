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
        $rows = FolderLocation::withCount('employees')
            ->with(['departments'])
            ->orderByRaw('LENGTH(row_name) ASC')
            ->orderBy('row_name', 'ASC')
            ->get();

        return view('folder_locations.index', compact('rows'));
    }

    /**
     * Store a newly created row.
     */
    public function storeRow()
    {
        $lastLocation = FolderLocation::select('row_name')
            ->orderByRaw('LENGTH(row_name) DESC')
            ->orderBy('row_name', 'desc')
            ->first();

        if ($lastLocation) {
            $nextRow = $lastLocation->row_name;
            $nextRow++;
        } else {
            $nextRow = 'A';
        }

        FolderLocation::create([
            'row_name' => $nextRow,
            'max_capacity' => 500, // Default fixed capacity
        ]);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Row {$nextRow} created successfully.");
    }

    /**
     * Update the specified folder location in storage.
     */
    public function update(Request $request, FolderLocation $folderLocation)
    {
        $validated = $request->validate([
            'row_name'     => 'required|string|max:10',
            'max_capacity' => 'required|integer|min:1',
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
        if ($folderLocation->employees()->exists() || $folderLocation->departments()->exists()) {
            return redirect()->back()
                ->with('error', "Cannot delete Row {$folderLocation->row_name} because it contains occupied folders.");
        }

        $rowName = $folderLocation->row_name;
        $folderLocation->delete();

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Row {$rowName} deleted successfully.");
    }
}
