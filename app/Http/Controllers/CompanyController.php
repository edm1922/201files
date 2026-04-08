<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a paginated list of companies with search & status filter.
     */
    public function index(Request $request)
    {
        $query = Company::withCount([
            'employees as active_employees_count' => function ($q) {
                $q->where('status', 'active');
            },
        ]);

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->input('status') !== 'all') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $companies = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created company.
     */
    public function store(CompanyRequest $request)
    {
        $company = Company::create([
            'name'      => $request->validated('name'),
            'code'      => strtoupper($request->validated('code')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditService::log('created', "Created new company", $company);

        return redirect()
            ->route('settings.companies.index')
            ->with('success', 'Company created successfully.');
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company.
     */
    public function update(CompanyRequest $request, Company $company)
    {
        $company->update([
            'name'      => $request->validated('name'),
            'code'      => strtoupper($request->validated('code')),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditService::log('updated', "Updated company", $company);

        return redirect()
            ->route('settings.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Toggle the is_active flag (deactivate / reactivate).
     */
    public function toggleActive(Company $company)
    {
        $company->update(['is_active' => !$company->is_active]);

        $status = $company->is_active ? 'activated' : 'deactivated';
        AuditService::log('updated', "Company status updated to {$status}", $company);

        $status = $company->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('settings.companies.index')
            ->with('success', "Company {$status} successfully.");
    }
    /**
     * Remove the specified company from storage (only if it has no employees).
     */
    public function destroy(Company $company)
    {
        if ($company->employees()->count() > 0) {
            return redirect()
                ->route('settings.companies.index')
                ->with('error', 'This company cannot be deleted because it currently has one or more employees assigned to it. Please deactivate the company instead.');
        }

        $name = $company->name;
        $company->delete();

        AuditService::log('deleted', "Deleted company");

        return redirect()
            ->route('settings.companies.index')
            ->with('success', 'Company removed successfully.');
    }
}
