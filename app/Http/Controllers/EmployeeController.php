<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Company;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\PhysicalLocation;
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

        $physicalLocations = PhysicalLocation::orderBy('cabinet_id')->orderBy('rack_id')->get();

        return view('201files', [
            'employee'      => null,
            'companies'     => $companies,

            'physicalLocations' => $physicalLocations,
        ]);
    }

    /**
     * Store a newly created employee and optionally assign them to a company.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = DB::transaction(function () use ($request) {
            $data = $request->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'physical_location_id',
            ]);
            
            $data['folder_code'] = $this->generateFolderCode();

            $employee = Employee::create($data);

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

        $physicalLocations = PhysicalLocation::orderBy('cabinet_id')->orderBy('rack_id')->get();

        return view('201files', [
            'employee'      => $employee,
            'companies'     => $companies,

            'physicalLocations' => $physicalLocations,
        ]);
    }

    /**
     * Update an existing employee and optionally change their company assignment.
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
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'physical_location_id',
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

    /**
     * Auto-generates the next folder code in the sequence (e.g., 201HR-0001).
     */
    protected function generateFolderCode(): string
    {
        $lastEmployee = Employee::where('folder_code', 'like', '201HR-%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastEmployee || !preg_match('/^201HR-(\d+)$/', $lastEmployee->folder_code, $matches)) {
            return '201HR-0001';
        }

        $nextNumber = ((int) $matches[1]) + 1;
        return '201HR-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
