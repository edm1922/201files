<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Company;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Show the blank 201 File form (new employee).
     */
    public function create()
    {
        $companies     = Company::where('is_active', true)->orderBy('name')->get();
        $documentTypes = DocumentType::whereHas('department', fn($q) => $q->where('name', 'Human Resource'))
            ->orderBy('name')
            ->get();

        return view('201files', [
            'employee'      => null,
            'companies'     => $companies,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Store a newly created employee and optionally deploy them to a company.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = DB::transaction(function () use ($request) {
            $employee = Employee::create($request->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_of_birth', 'status', 'barcode_id', 'folder_code',
                'company_id',
            ]));

            AuditService::log(
                'employee_created',
                "Created employee: {$employee->full_name} (System ID: {$employee->system_id})"
            );

            return $employee;
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee profile created successfully.');
    }

    /**
     * Show an employee's 201 profile hub.
     */
    public function show(Employee $employee)
    {
        $employee->load('company');

        $companies     = Company::where('is_active', true)->orderBy('name')->get();
        $documentTypes = DocumentType::whereHas('department', fn($q) => $q->where('name', 'Human Resource'))
            ->orderBy('name')
            ->get();

        return view('201files', [
            'employee'      => $employee,
            'companies'     => $companies,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Update an existing employee and optionally change their deployment.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $old = $employee->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'status', 'barcode_id', 'folder_code',
            ]);

            $employee->update($request->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_of_birth', 'status', 'barcode_id', 'folder_code',
                'company_id',
            ]));

            AuditService::log(
                'employee_updated',
                "Updated employee: {$employee->full_name}",
                null,
                ['before' => $old, 'after' => $employee->fresh()->toArray()]
            );
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee profile updated successfully.');
    }

}
