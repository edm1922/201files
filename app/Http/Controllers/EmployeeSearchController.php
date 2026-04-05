<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EmployeeSearchController extends Controller
{
    /**
     * Meili-search: returns up to 10 employees matching the query.
     * Used by the 201 Files toolbar search bar.
     */
    public function meiliSearch(Request $request)
    {
        $query = trim((string) $request->get('query', ''));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $employees = $this->searchEmployeesForToolbar($query);

        // Explicitly map the results to include the folder_code for the frontend
        $mappedEmployees = $employees->map(function ($emp) {
            $data = $emp->toArray();
            $data['folder_code'] = $emp->folder?->folder_code;

            return $data;
        });

        return response()->json($mappedEmployees);
    }

    protected function searchEmployeesForToolbar(string $query): Collection
    {
        if ($this->shouldUseScoutSearch()) {
            try {
                return $this->searchWithScout($query);
            } catch (\Throwable $exception) {
                Log::warning('Scout employee meili-search failed. Falling back to SQL.', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $this->searchWithDatabase($query);
    }

    protected function shouldUseScoutSearch(): bool
    {
        $driver = (string) config('scout.driver', 'collection');

        return ! in_array($driver, ['null', 'collection', 'database'], true);
    }

    protected function searchWithScout(string $query): Collection
    {
        return Employee::search($query)
            ->where('status', '!=', 'resigned')
            ->take(10)
            ->query(function ($query) {
                $query
                    ->with(['folderLocation', 'folder'])
                    ->select([
                        'id',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'barcode_id',
                        'system_id',
                        'status',
                        'folder_location_id',
                        'folder_id',
                    ]);
            })
            ->get();
    }

    protected function searchWithDatabase(string $query): Collection
    {
        return Employee::query()
            ->with(['folderLocation', 'folder'])
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', $query.'%')
                    ->orWhere('middle_name', 'LIKE', $query.'%')
                    ->orWhere('last_name', 'LIKE', $query.'%')
                    ->orWhere('barcode_id', 'LIKE', $query.'%')
                    ->orWhere('system_id', 'LIKE', $query.'%')
                    ->orWhereHas('folder', function ($sq) use ($query) {
                        $sq->where('folder_code', 'LIKE', $query.'%');
                    });
            })
            ->where('status', '!=', 'resigned')
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'barcode_id', 'system_id', 'status', 'folder_location_id', 'folder_id']);
    }
}
