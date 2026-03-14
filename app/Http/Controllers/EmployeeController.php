<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Company;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\Slot;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Show the blank 201 File form (new employee).
     */
    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $slots     = Slot::available()->with('rack.cabinet')->get();
        $lastFolderCode = Slot::where('folder_code', 'like', 'CSC-HR-%')->max('folder_code');

        return view('201files', [
            'employee'       => null,
            'companies'      => $companies,
            'slots'          => $slots,
            'lastFolderCode' => $lastFolderCode,
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = DB::transaction(function () use ($request) {
            $data = $request->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'slot_id',
            ]);

            $employee = Employee::create($data);

            // Update slot folder code
            if ($employee->slot_id && $request->has('folder_code')) {
                Slot::where('id', $employee->slot_id)->update(['folder_code' => $request->folder_code]);
            }

            AuditService::log(
                'employee_created',
                "Created employee: {$employee->full_name} (System ID: {$employee->system_id})"
            );

            // If created as resigned, auto-archive
            if ($employee->status === 'resigned') {
                $employee->update(['archive_date' => now()]);
                $employee->delete(); // soft-delete = archive
                AuditService::log(
                    'employee_archived',
                    "Auto-archived employee: {$employee->full_name} (status: resigned)"
                );
            }

            return $employee;
        });

        if ($employee->trashed()) {
            return redirect()
                ->route('201files')
                ->with('success', 'Employee created and automatically archived (resigned).');
        }

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee profile created successfully.');
    }

    /**
     * Show an employee's 201 profile hub.
     */
    public function show(Employee $employee)
    {
        $employee->load(['company', 'slot.rack.cabinet']);

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $slots     = Slot::available()->with('rack.cabinet')->get();
        $lastFolderCode = Slot::where('folder_code', 'like', 'CSC-HR-%')->max('folder_code');

        return view('201files', [
            'employee'       => $employee,
            'companies'      => $companies,
            'slots'          => $slots,
            'lastFolderCode' => $lastFolderCode,
        ]);
    }

    /**
     * Update an existing employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $oldStatus = $employee->status;

            $old = $employee->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'status', 'barcode_id',
            ]);

            $employee->update($request->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'slot_id',
            ]));

            // Update slot folder code
            if ($employee->slot_id && $request->has('folder_code')) {
                Slot::where('id', $employee->slot_id)->update(['folder_code' => $request->folder_code]);
            }

            AuditService::log(
                'employee_updated',
                "Updated employee: {$employee->full_name}",
                null,
                ['before' => $old, 'after' => $employee->fresh()->toArray()]
            );

            // Auto-archive if status changed to resigned
            if ($oldStatus !== 'resigned' && $employee->status === 'resigned') {
                $employee->update(['archive_date' => now()]);
                $employee->delete(); // soft-delete = archive
                AuditService::log(
                    'employee_archived',
                    "Auto-archived employee: {$employee->full_name} (status changed to resigned)"
                );
            }
        });

        if ($employee->trashed()) {
            return redirect()
                ->route('201files')
                ->with('success', 'Employee has been resigned and moved to the archive.');
        }

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee profile updated successfully.');
    }

    /**
     * List archived (soft-deleted resigned) employees. Admin only.
     */
    public function archiveIndex()
    {
        $employees = Employee::archived()
            ->with('slot.rack.cabinet')
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('employees.archive', compact('employees'));
    }

    /**
     * Restore an archived employee. Admin only.
     */
    public function restore(int $id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($employee) {
            if ($employee->trashed()) {
                $employee->restore();
            }
            $employee->update([
                'status' => 'active',
                'archive_date' => null,
            ]);

            AuditService::log(
                'employee_restored',
                "Restored employee: {$employee->full_name} from archive (status set to active)"
            );
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee restored successfully.');
    }

    /**
     * Permanently delete an archived employee. Admin only.
     * This frees the slot (is_available = true).
     */
    public function forceDestroy(int $id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($employee) {
            // Free the slot if assigned
            if ($employee->slot_id) {
                Slot::where('id', $employee->slot_id)->update(['is_available' => true]);
            }

            $name = $employee->full_name;
            $systemId = $employee->system_id;

            $employee->forceDelete();

            AuditService::log(
                'employee_deleted',
                "Permanently deleted employee: {$name} (System ID: {$systemId}). Slot freed."
            );
        });

        return redirect()
            ->route('employees.archive')
            ->with('success', 'Employee permanently deleted. Folder slot is now available.');
    }
    /**
     * Get employee details as JSON (for Archive modal).
     */
    public function details(int $id)
    {
        $employee = Employee::withTrashed()
            ->with(['company', 'slot.rack.cabinet'])
            ->findOrFail($id);

        return response()->json([
            'name'        => $employee->full_name,
            'system_id'   => $employee->system_id,
            'barcode_id'  => $employee->barcode_id ?: '—',
            'folder_code' => $employee->slot?->folder_code ?: '—',
            'location'    => $employee->slot?->full_location ?: '—',
            'company'     => $employee->company?->name ?: '— Not Assigned —',
            'date_hired'  => $employee->date_hired ? $employee->date_hired->format('F d, Y') : '—',
            'archive_date' => $employee->archive_date ? $employee->archive_date->format('F d, Y') : '—',
            'archived_at' => $employee->deleted_at?->format('F d, Y h:i A'),
            'status'      => ucfirst($employee->status)
        ]);
    }
}
