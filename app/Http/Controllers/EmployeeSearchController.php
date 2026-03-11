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
        $query   = $request->get('query');

        $employees = Employee::query()
            ->where(function ($q) use ($query) {
                $q->where('first_name',  'LIKE', $query . '%')
                  ->orWhere('middle_name', 'LIKE', $query . '%')
                  ->orWhere('last_name',   'LIKE', $query . '%')
                  ->orWhere('barcode_id',  'LIKE', $query . '%')
                  ->orWhere('system_id',   'LIKE', $query . '%')
                  ->orWhere('folder_code', 'LIKE', $query . '%');
            })
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'barcode_id', 'system_id', 'folder_code', 'status']);

        return response()->json($employees);
    }
}
