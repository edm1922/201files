<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeSearchController extends Controller
{
    /**
     * Milli-search: returns up to 10 employees matching the query.
     * Used by the 201 Files toolbar search bar.
     */
    public function milliSearch(Request $request)
    {
        $query = $request->get('query');

        $employees = Employee::query()
            ->with(['folderLocation', 'folder'])
            ->where(function ($q) use ($query) {
                $q->where('first_name',  'LIKE', $query . '%')
                  ->orWhere('middle_name', 'LIKE', $query . '%')
                  ->orWhere('last_name',   'LIKE', $query . '%')
                  ->orWhere('barcode_id',  'LIKE', $query . '%')
                  ->orWhere('system_id',   'LIKE', $query . '%')
                  ->orWhereHas('folder', function ($sq) use ($query) {
                      $sq->where('folder_code', 'LIKE', $query . '%');
                  });
            })
            ->where('status', '!=', 'resigned')
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'barcode_id', 'system_id', 'status', 'folder_location_id', 'folder_id']);

        // Explicitly map the results to include the folder_code for the frontend
        $mappedEmployees = $employees->map(function ($emp) {
            $data = $emp->toArray();
            $data['folder_code'] = $emp->folder?->folder_code;
            return $data;
        });

        return response()->json($mappedEmployees);
    }
}
