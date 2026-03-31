<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Services\AuditService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a paginated list of departments.
     */
    public function index(Request $request)
    {
        $query = Department::withCount(['documentTypes', 'documents']);

        $departments = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(DepartmentRequest $request)
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);
        
        $department = Department::create($data);

        AuditService::log('created', "Created new department", $department);

        return redirect()
            ->route('settings.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);
        
        $department->update($data);

        AuditService::log('updated', "Updated department", $department);

        return redirect()
            ->route('settings.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Toggle the active status of the specified department.
     */
    public function toggleActive(Department $department)
    {
        $department->update([
            'is_active' => !$department->is_active
        ]);

        $status = $department->is_active ? 'reactivated' : 'deactivated';
        
        AuditService::log('updated', "Department status updated to {$status}", $department);
        return redirect()->route('settings.departments.index')
            ->with('success', "Department has been {$status}.");
    }

    /**
     * Remove the specified department from storage.
     * Prevents deletion if the department has active documents or document types.
     */
    public function destroy(Department $department)
    {
        // First check if it has associated document types
        if ($department->documentTypes()->count() > 0) {
            return redirect()
                ->route('settings.departments.index')
                ->with('error', 'Cannot delete a department that has active document types attached. Deactivate it instead.');
        }

        // Second check if it has any associated documents (morphMany relationship)
        if ($department->documents()->count() > 0) {
            return redirect()
                ->route('settings.departments.index')
                ->with('error', 'Cannot delete a department that has documents attached. Deactivate it instead.');
        }

        $name = $department->name;
        $department->delete();

        AuditService::log('deleted', "Deleted department");

        return redirect()
            ->route('settings.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
