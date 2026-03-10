<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a paginated list of departments.
     */
    public function index(Request $request)
    {
        $query = Department::withCount('documentTypes');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

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
        Department::create($request->validated());

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
        $department->update($request->validated());

        return redirect()
            ->route('settings.departments.index')
            ->with('success', 'Department updated successfully.');
    }
}
