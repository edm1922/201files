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
            ->orderBy('row_name')
            ->orderBy('column_code')
            ->get()
            ->groupBy('row_name');

        return view('folder_locations.index', compact('rows'));
    }

    /**
     * Store a newly created row with its first column.
     */
    public function storeRow()
    {
        $lastLocation = FolderLocation::select('row_name')
            ->distinct()
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
            'column_code' => '1',
        ]);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Row {$nextRow} created successfully.");
    }

    /**
     * Store a newly created column in a specific row.
     */
    public function storeColumn($rowName)
    {
        $maxColumn = FolderLocation::where('row_name', $rowName)
            ->get()
            ->max(function ($loc) {
                return (int) $loc->column_code;
            });

        $nextColumnCode = ($maxColumn ?? 0) + 1;

        FolderLocation::create([
            'row_name' => $rowName,
            'column_code' => (string) $nextColumnCode,
        ]);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Column {$nextColumnCode} added to Row {$rowName}.");
    }

    /**
     * Remove an entire row.
     */
    public function destroyRow($rowName)
    {
        $locations = FolderLocation::where('row_name', $rowName)->get();

        if ($locations->isEmpty()) {
            return redirect()->back()->with('error', 'Row not found.');
        }

        $hasOccupied = $locations->contains(function ($loc) {
            return $loc->employees()->exists() || $loc->departments()->exists();
        });

        if ($hasOccupied) {
            return redirect()->back()
                ->with('error', "Cannot delete Row {$rowName} because it contains occupied folders.");
        }

        FolderLocation::where('row_name', $rowName)->delete();

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Row {$rowName} deleted successfully.");
    }

    /**
     * Remove a specific column from a row.
     */
    public function destroyColumn($rowName, $columnCode)
    {
        $locations = FolderLocation::where('row_name', $rowName)
            ->where('column_code', $columnCode)
            ->get();

        if ($locations->isEmpty()) {
            return redirect()->back()->with('error', 'Column not found.');
        }

        $hasOccupied = $locations->contains(function ($loc) {
            return $loc->employees()->exists() || $loc->departments()->exists();
        });

        if ($hasOccupied) {
            return redirect()->back()
                ->with('error', "Cannot delete Column {$columnCode} because it contains occupied folders.");
        }

        FolderLocation::where('row_name', $rowName)
            ->where('column_code', $columnCode)
            ->delete();

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Column {$columnCode} deleted successfully from Row {$rowName}.");
    }

    /**
     * Update the specified folder location in storage.
     */
    public function update(Request $request, FolderLocation $folderLocation)
    {
        $validated = $request->validate([
            'row_name'    => 'required|string|max:10',
            'column_code' => 'required|string|max:10',
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
        if ($folderLocation->employees()->exists() || $folderLocation->departments()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete a location that is currently occupied.');
        }

        $folderLocation->delete();

        return redirect()->route('settings.folder-locations.index')
            ->with('success', 'Folder location deleted successfully.');
    }
}
