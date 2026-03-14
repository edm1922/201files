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
            ->with('folderLocation')
            ->where(function ($q) use ($query) {
                $q->where('first_name',  'LIKE', $query . '%')
                  ->orWhere('middle_name', 'LIKE', $query . '%')
                  ->orWhere('last_name',   'LIKE', $query . '%')
                  ->orWhere('barcode_id',  'LIKE', $query . '%')
                  ->orWhere('system_id',   'LIKE', $query . '%')
                  ->orWhereHas('folderLocation', function ($sq) use ($query) {
                      $sq->where('folder_code', 'LIKE', $query . '%');
                  });
            })
            ->where('status', '!=', 'resigned')
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'barcode_id', 'system_id', 'status', 'folder_location_id']);

        // Append folder_code from folderLocation for the JSON response
        $employees->each(function ($emp) {
            $emp->folder_code = $emp->folderLocation?->folder_code;
        });

        return response()->json($employees);
    }
}
