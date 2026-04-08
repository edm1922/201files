<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankTypeRequest;
use App\Models\BankType;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BankTypeController extends Controller
{
    /**
     * Display a paginated list of bank types with search & status filter.
     */
    public function index(Request $request)
    {
        $query = BankType::query();

        // Search filter
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->has('status') && $request->input('status') !== 'all') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $bankTypes = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('bank_types.index', compact('bankTypes'));
    }

    /**
     * Show the form for creating a new bank type.
     */
    public function create()
    {
        return view('bank_types.create');
    }

    /**
     * Store a newly created bank type.
     */
    public function store(BankTypeRequest $request)
    {
        $bankType = BankType::create([
            'name'      => $request->validated('name'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditService::log('created', "Created new bank type", $bankType);

        return redirect()
            ->route('settings.bank-types.index')
            ->with('success', 'Bank Type created successfully.');
    }

    /**
     * Show the form for editing the specified bank type.
     */
    public function edit(BankType $bankType)
    {
        return view('bank_types.edit', compact('bankType'));
    }

    /**
     * Update the specified bank type.
     */
    public function update(BankTypeRequest $request, BankType $bankType)
    {
        $bankType->update([
            'name'      => $request->validated('name'),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditService::log('updated', "Updated bank type", $bankType);

        return redirect()
            ->route('settings.bank-types.index')
            ->with('success', 'Bank Type updated successfully.');
    }

    /**
     * Toggle the is_active flag (deactivate / reactivate).
     */
    public function toggleActive(BankType $bankType)
    {
        $bankType->update(['is_active' => !$bankType->is_active]);

        $status = $bankType->is_active ? 'activated' : 'deactivated';
        AuditService::log('updated', "Bank Type status updated to {$status}", $bankType);

        return redirect()
            ->route('settings.bank-types.index')
            ->with('success', "Bank Type {$status} successfully.");
    }

    /**
     * Remove the specified bank type from storage.
     */
    public function destroy(BankType $bankType)
    {
        // Add check if bank type is assigned to employees
        if (\App\Models\Employee::where('bank_type_id', $bankType->id)->exists()) {
            return redirect()
                ->route('settings.bank-types.index')
                ->with('error', 'This bank type cannot be deleted because it is currently assigned to one or more employees. Please deactivate it instead.');
        }

        $name = $bankType->name;
        $bankType->delete();

        AuditService::log('deleted', "Deleted bank type");

        return redirect()
            ->route('settings.bank-types.index')
            ->with('success', 'Bank Type removed successfully.');
    }
}
