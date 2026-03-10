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
        $company = $request->get('company'); // company_id

        $employees = Employee::query()
            ->when($company, function ($q) use ($company) {
                // Filter by employees assigned to the selected company
                $q->where('company_id', $company);
            })
            ->where(function ($q) use ($query) {
                $q->where('first_name',  'LIKE', $query . '%')
                  ->orWhere('middle_name', 'LIKE', $query . '%')
                  ->orWhere('last_name',   'LIKE', $query . '%')
                  ->orWhere('barcode_id',  'LIKE', $query . '%')
                  ->orWhere('system_id',   'LIKE', $query . '%');
            })
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'barcode_id', 'system_id', 'folder_code', 'status']);

        return response()->json($employees);
    }
}
