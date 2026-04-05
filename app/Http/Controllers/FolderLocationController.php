<?php

namespace App\Http\Controllers;

use App\Models\DocumentLocation;
use App\Models\FolderLocation;
use App\Services\AuditService;
use Illuminate\Http\Request;

class FolderLocationController extends Controller
{
    /**
     * Display a listing of folder locations.
     */
    public function index()
    {
        $rows = FolderLocation::withCount('employees')
            ->orderByRaw('LENGTH(row_name) ASC')
            ->orderBy('row_name', 'ASC')
            ->get();

        $documentLocations = DocumentLocation::query()
            ->withCount('documents')
            ->orderBy('name')
            ->get();

        return view('folder_locations.index', compact('rows', 'documentLocations'));
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

        $folderLocation = FolderLocation::create([
            'row_name' => $nextRow,
            'max_capacity' => 500, // Default fixed capacity
        ]);

        AuditService::log('created', 'Created folder location row.', $folderLocation, [
            'before' => [],
            'after' => [
                'row_name' => $folderLocation->row_name,
                'max_capacity' => $folderLocation->max_capacity,
            ],
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
            'row_name' => 'required|string|max:10',
            'max_capacity' => 'required|integer|min:1',
        ]);

        $before = [
            'row_name' => $folderLocation->row_name,
            'max_capacity' => $folderLocation->max_capacity,
        ];

        $folderLocation->update($validated);

        AuditService::log('updated', 'Updated folder location row.', $folderLocation, [
            'before' => $before,
            'after' => [
                'row_name' => $folderLocation->row_name,
                'max_capacity' => $folderLocation->max_capacity,
            ],
        ]);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', 'Folder location updated successfully.');
    }

    /**
     * Remove the specified folder location from storage.
     */
    public function destroy(FolderLocation $folderLocation)
    {
        $employeesCount = $folderLocation->employees()->count();

        if ($employeesCount > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete Row {$folderLocation->row_name} because it contains occupied folders.");
        }

        $rowName = $folderLocation->row_name;
        $snapshot = [
            'row_name' => $rowName,
            'max_capacity' => $folderLocation->max_capacity,
            'employees_count_at_delete' => $employeesCount,
        ];

        $folderLocation->delete();

        AuditService::log('deleted', 'Deleted folder location row.', $folderLocation, [
            'before' => $snapshot,
            'after' => [],
        ]);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Row {$rowName} deleted successfully.");
    }
}
