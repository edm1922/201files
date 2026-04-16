<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DocumentLocation;
use App\Models\FolderLocation;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FolderLocationController extends Controller
{
    /**
     * Display a listing of folder locations.
     */
    public function index()
    {
        $rows = FolderLocation::with('company:id,name,code')
            ->withCount(['employees' => function ($query) {
                $query->withTrashed();
            }])
            ->orderBy('company_id')
            ->orderByRaw('LENGTH(row_name) ASC')
            ->orderBy('row_name', 'ASC')
            ->get();

        $companies = Company::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $documentLocations = DocumentLocation::query()
            ->withCount('documents')
            ->orderBy('name')
            ->get();

        $nextRangeStartByCompany = FolderLocation::query()
            ->selectRaw('company_id, MAX(range_end) as last_range_end')
            ->groupBy('company_id')
            ->pluck('last_range_end', 'company_id')
            ->map(fn ($value) => ((int) $value) + 1)
            ->all();

        return view('folder_locations.index', compact('rows', 'documentLocations', 'companies', 'nextRangeStartByCompany'));
    }

    /**
     * Store a newly created location.
     */
    public function storeRow(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'row_name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('folder_locations', 'row_name')->where(fn ($query) => $query->where('company_id', (int) $request->input('company_id'))),
            ],
            'range_start' => ['required', 'integer', 'min:1'],
            'range_end' => ['required', 'integer', 'gte:range_start'],
        ]);

        $overlapExists = FolderLocation::query()
            ->where('company_id', (int) $validated['company_id'])
            ->where(function ($query) use ($validated) {
                $query
                    ->whereBetween('range_start', [(int) $validated['range_start'], (int) $validated['range_end']])
                    ->orWhereBetween('range_end', [(int) $validated['range_start'], (int) $validated['range_end']])
                    ->orWhere(function ($inside) use ($validated) {
                        $inside
                            ->where('range_start', '<=', (int) $validated['range_start'])
                            ->where('range_end', '>=', (int) $validated['range_end']);
                    });
            })
            ->exists();

        if ($overlapExists) {
            return back()->withErrors(['range_start' => 'The range overlaps an existing row for this company.'])->withInput();
        }

        $folderLocation = FolderLocation::create([
            'company_id' => (int) $validated['company_id'],
            'row_name' => strtoupper(trim((string) $validated['row_name'])),
            'range_start' => (int) $validated['range_start'],
            'range_end' => (int) $validated['range_end'],
            'max_capacity' => ((int) $validated['range_end'] - (int) $validated['range_start'] + 1),
        ]);

        AuditService::log('created', 'Created folder location row.', $folderLocation, [
            'before' => [],
            'after' => [
                'company_id' => $folderLocation->company_id,
                'row_name' => $folderLocation->row_name,
                'range_start' => $folderLocation->range_start,
                'range_end' => $folderLocation->range_end,
                'max_capacity' => $folderLocation->max_capacity,
            ],
        ]);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Location {$folderLocation->row_name} created successfully.");
    }

    /**
     * Update the specified folder location in storage.
     */
    public function update(Request $request, FolderLocation $folderLocation)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'row_name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('folder_locations', 'row_name')
                    ->where(fn ($query) => $query->where('company_id', (int) $request->input('company_id')))
                    ->ignore($folderLocation->id),
            ],
            'range_start' => ['required', 'integer', 'min:1'],
            'range_end' => ['required', 'integer', 'gte:range_start'],
        ]);

        $overlapExists = FolderLocation::query()
            ->where('company_id', (int) $validated['company_id'])
            ->whereKeyNot($folderLocation->id)
            ->where(function ($query) use ($validated) {
                $query
                    ->whereBetween('range_start', [(int) $validated['range_start'], (int) $validated['range_end']])
                    ->orWhereBetween('range_end', [(int) $validated['range_start'], (int) $validated['range_end']])
                    ->orWhere(function ($inside) use ($validated) {
                        $inside
                            ->where('range_start', '<=', (int) $validated['range_start'])
                            ->where('range_end', '>=', (int) $validated['range_end']);
                    });
            })
            ->exists();

        if ($overlapExists) {
            return back()->withErrors(['range_start' => 'The range overlaps an existing row for this company.'])->withInput();
        }

        if ($request->filled('id') && (int) $request->input('id') !== (int) $folderLocation->id) {
            return back()->withErrors(['id' => 'Invalid location identifier.'])->withInput();
        }

        $before = [
            'company_id' => $folderLocation->company_id,
            'row_name' => $folderLocation->row_name,
            'range_start' => $folderLocation->range_start,
            'range_end' => $folderLocation->range_end,
            'max_capacity' => $folderLocation->max_capacity,
        ];

        $folderLocation->update([
            'company_id' => (int) $validated['company_id'],
            'row_name' => strtoupper(trim((string) $validated['row_name'])),
            'range_start' => (int) $validated['range_start'],
            'range_end' => (int) $validated['range_end'],
            'max_capacity' => ((int) $validated['range_end'] - (int) $validated['range_start'] + 1),
        ]);

        AuditService::log('updated', 'Updated folder location.', $folderLocation, [
            'before' => $before,
            'after' => [
                'company_id' => $folderLocation->company_id,
                'row_name' => $folderLocation->row_name,
                'range_start' => $folderLocation->range_start,
                'range_end' => $folderLocation->range_end,
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
        $employeesCount = $folderLocation->employees()->withTrashed()->count();

        if ($employeesCount > 0) {
            return redirect()->back()
                ->with('error', 'This location cannot be deleted because it is currently assigned to one or more employee folders. Please ensure the location is empty before deleting.');
        }

        $rowName = $folderLocation->row_name;
        $snapshot = [
            'company_id' => $folderLocation->company_id,
            'row_name' => $rowName,
            'range_start' => $folderLocation->range_start,
            'range_end' => $folderLocation->range_end,
            'max_capacity' => $folderLocation->max_capacity,
            'employees_count_at_delete' => $employeesCount,
        ];

        $folderLocation->delete();

        AuditService::log('deleted', 'Deleted folder location.', $folderLocation, [
            'before' => $snapshot,
            'after' => [],
        ]);

        return redirect()->route('settings.folder-locations.index')
            ->with('success', "Location {$rowName} deleted successfully.");
    }
}
