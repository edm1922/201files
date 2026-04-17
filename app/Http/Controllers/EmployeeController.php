<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\AuditLog;
use App\Models\BankType;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Folder;
use App\Models\FolderLocation;
use App\Models\HiringEvent;
use App\Services\AuditService;
use App\Services\FolderCodeService;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Show the blank 201 File form (new employee).
     */
    public function create(FolderCodeService $folderCodeService)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyNextFolderCodes = $this->buildCompanyNextFolderCodes($companies, $folderCodeService);
        $companyLastFolderCodes = $this->buildCompanyLastFolderCodes($companies);
        $availableFoldersByCompany = $this->buildAvailableFoldersByCompany();
        $bankTypes = BankType::where('is_active', true)->orderBy('name')->get();
        $locations = FolderLocation::with('company:id,name,code')
            ->withCount(['employees' => function ($query) {
                $query->withTrashed();
            }])
            ->orderBy('company_id')
            ->orderByRaw('LENGTH(row_name) ASC')
            ->orderBy('row_name', 'ASC')
            ->get();

        return view('201files', [
            'employee' => null,
            'companies' => $companies,
            'companyNextFolderCodes' => $companyNextFolderCodes,
            'companyLastFolderCodes' => $companyLastFolderCodes,
            'availableFoldersByCompany' => $availableFoldersByCompany,
            'bankTypes' => $bankTypes,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request, FolderCodeService $folderCodeService)
    {
        $employee = DB::transaction(function () use ($request, $folderCodeService) {
            $data = $request->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'folder_location_id', 'atm_status', 'bank_type_id',
            ]);

            $employee = Employee::create($data);

            $company = Company::query()->findOrFail((int) $data['company_id']);
            $selectedFolderId = (int) ($request->input('folder_id') ?? 0);
            $folder = $selectedFolderId > 0
                ? $folderCodeService->assignSpecificAvailableForCompany($company, $selectedFolderId)
                : $folderCodeService->assignNextForCompany($company);

            $employee->folder_id = $folder->id;
            $employee->saveQuietly();

            // Create Hiring Event persistence record
            if ($employee->date_hired) {
                HiringEvent::updateOrCreate(
                    ['employee_id' => $employee->id],
                    ['event_date' => $employee->date_hired]
                );
            } else {
                HiringEvent::query()->where('employee_id', $employee->id)->delete();
            }

            $fresh = $employee->fresh()->load(['company', 'folder', 'folderLocation', 'bankType']);
            $after = $fresh->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'folder_id', 'folder_location_id', 'atm_status', 'bank_type_id',
            ]);

            // Resolve descriptive names for logging
            $after['bank_name'] = $fresh->bankType?->name ?? 'N/A';
            $after['company_name'] = $fresh->company?->name ?? 'N/A';
            $after['folder_code'] = $fresh->folder?->folder_code ?? 'N/A';
            $after['location_name'] = $fresh->folderLocation?->full_location ?? 'N/A';

            unset($after['bank_type_id'], $after['company_id'], $after['folder_id'], $after['folder_location_id']);

            AuditService::log(
                'created',
                'Added new employee: '.$employee->full_name,
                $employee,
                ['before' => [], 'after' => $after]
            );

            // If created as resigned, auto-archive
            if ($employee->status === 'resigned') {
                $employee->update(['archive_date' => now()]);
                $employee->delete(); // soft-delete = archive
                AuditService::log(
                    'archived',
                    'Auto-archived employee (status: resigned)',
                    $employee
                );
            }

            return $employee;
        });

        if ($employee->trashed()) {
            return redirect()
                ->route('archives.index', ['tab' => 'employees'])
                ->with('success', 'Employee created and automatically archived (resigned).');
        }

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee profile created successfully.');
    }

    /**
     * Show an employee's 201 profile hub.
     */
    public function show(Employee $employee, FolderCodeService $folderCodeService)
    {
        $employee->load(['company', 'folder', 'folderLocation', 'bankType']);

        $latestUpdate = AuditLog::where('model_type', Employee::class)
            ->where('model_id', $employee->id)
            ->whereIn('action', ['created', 'updated', 'restored'])
            ->with('user')
            ->latest()
            ->first();

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyNextFolderCodes = $this->buildCompanyNextFolderCodes($companies, $folderCodeService);
        $companyLastFolderCodes = $this->buildCompanyLastFolderCodes($companies);
        $availableFoldersByCompany = $this->buildAvailableFoldersByCompany();
        $bankTypes = BankType::where('is_active', true)->orderBy('name')->get();
        $locations = FolderLocation::with('company:id,name,code')
            ->withCount(['employees' => function ($query) {
                $query->withTrashed();
            }])
            ->orderBy('company_id')
            ->orderByRaw('LENGTH(row_name) ASC')
            ->orderBy('row_name', 'ASC')
            ->get();

        return view('201files', [
            'employee' => $employee,
            'latestUpdate' => $latestUpdate,
            'companies' => $companies,
            'companyNextFolderCodes' => $companyNextFolderCodes,
            'companyLastFolderCodes' => $companyLastFolderCodes,
            'availableFoldersByCompany' => $availableFoldersByCompany,
            'bankTypes' => $bankTypes,
            'locations' => $locations,
        ]);
    }

    private function buildCompanyNextFolderCodes($companies, FolderCodeService $folderCodeService): array
    {
        $codes = [];

        foreach ($companies as $company) {
            $codes[(int) $company->id] = $folderCodeService->previewNextCodeForCompany($company);
        }

        return $codes;
    }

    private function buildCompanyLastFolderCodes($companies): array
    {
        $codes = [];

        foreach ($companies as $company) {
            $lastFolder = Folder::query()
                ->where('company_id', $company->id)
                ->orderBy('sequence_number', 'desc')
                ->first();

            $codes[(int) $company->id] = $lastFolder ? $lastFolder->folder_code : 'None';
        }

        return $codes;
    }

    private function buildAvailableFoldersByCompany(): array
    {
        $folders = Folder::query()
            ->select('id', 'company_id', 'folder_code')
            ->where('is_available', true)
            ->orderBy('company_id')
            ->orderBy('sequence_number')
            ->orderBy('folder_code')
            ->get();

        $mapped = [];

        foreach ($folders as $folder) {
            $companyId = (int) $folder->company_id;

            if (! isset($mapped[$companyId])) {
                $mapped[$companyId] = [];
            }

            $mapped[$companyId][] = [
                'id' => (int) $folder->id,
                'folder_code' => (string) $folder->folder_code,
            ];
        }

        return $mapped;
    }

    /**
     * Update an existing employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee, FolderCodeService $folderCodeService)
    {
        DB::transaction(function () use ($request, $employee, $folderCodeService) {
            $employee->loadMissing(['company', 'folder', 'folderLocation', 'bankType']);

            $oldStatus = $employee->status;
            $oldFolderId = $employee->folder_id;

            $old = $employee->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'folder_id', 'folder_location_id', 'atm_status', 'bank_type_id',
            ]);

            // Resolve descriptive names for logging (Old state)
            $old['bank_name'] = $employee->bankType?->name ?? 'N/A';
            $old['company_name'] = $employee->company?->name ?? 'N/A';
            $old['folder_code'] = $employee->folder?->folder_code ?? 'N/A';
            $old['location_name'] = $employee->folderLocation?->full_location ?? 'N/A';

            unset($old['bank_type_id'], $old['company_id'], $old['folder_id'], $old['folder_location_id']);

            $employee->update($request->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'folder_location_id', 'atm_status', 'bank_type_id',
            ]));

            // Sync Hiring Event if date_hired is present
            if ($employee->date_hired) {
                HiringEvent::updateOrCreate(
                    ['employee_id' => $employee->id],
                    ['event_date' => $employee->date_hired]
                );
            }

            $targetCompanyId = (int) $employee->company_id;
            $currentFolder = $employee->folder()->first();
            $selectedFolderId = (int) ($request->input('folder_id') ?? 0);

            $needsNewFolder = ! $currentFolder || (int) $currentFolder->company_id !== $targetCompanyId;

            if ($selectedFolderId > 0) {
                $isKeepingCurrentFolder = $currentFolder && (int) $currentFolder->id === $selectedFolderId;

                if ($isKeepingCurrentFolder) {
                    if ($currentFolder->is_available) {
                        $currentFolder->update(['is_available' => false]);
                    }
                } else {
                    $company = Company::query()->findOrFail($targetCompanyId);
                    $newFolder = $folderCodeService->assignSpecificAvailableForCompany($company, $selectedFolderId);

                    if ($oldFolderId && $oldFolderId !== $newFolder->id) {
                        Folder::query()->whereKey($oldFolderId)->update(['is_available' => true]);
                    }

                    $employee->folder_id = $newFolder->id;
                    $employee->saveQuietly();
                }
            } elseif ($needsNewFolder) {
                $company = Company::query()->findOrFail($targetCompanyId);
                $newFolder = $folderCodeService->assignNextForCompany($company);

                if ($oldFolderId && $oldFolderId !== $newFolder->id) {
                    Folder::query()->whereKey($oldFolderId)->update(['is_available' => true]);
                }

                $employee->folder_id = $newFolder->id;
                $employee->saveQuietly();
            } elseif ($currentFolder->is_available) {
                $currentFolder->update(['is_available' => false]);
            }

            $fresh = $employee->fresh()->load(['company', 'folder', 'folderLocation', 'bankType']);
            $after = $fresh->only([
                'system_id', 'first_name', 'middle_name', 'last_name',
                'suffix', 'date_hired', 'status', 'barcode_id',
                'company_id', 'folder_id', 'folder_location_id', 'atm_status', 'bank_type_id',
            ]);

            // Resolve descriptive names for logging (New state)
            $after['bank_name'] = $fresh->bankType?->name ?? 'N/A';
            $after['company_name'] = $fresh->company?->name ?? 'N/A';
            $after['folder_code'] = $fresh->folder?->folder_code ?? 'N/A';
            $after['location_name'] = $fresh->folderLocation?->full_location ?? 'N/A';

            unset($after['bank_type_id'], $after['company_id'], $after['folder_id'], $after['folder_location_id']);

            AuditService::log(
                'updated',
                'Updated employee profile',
                $employee,
                ['before' => $old, 'after' => $after]
            );

            // Auto-archive if status changed to resigned
            if ($oldStatus !== 'resigned' && $employee->status === 'resigned') {
                $employee->update(['archive_date' => now()]);

                $employee->delete(); // soft-delete = archive
                AuditService::log(
                    'archived',
                    'Archived employee (status: resigned)',
                    $employee
                );
            }
        });

        if ($employee->trashed()) {
            return redirect()
                ->route('archives.index', ['tab' => 'employees'])
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
            ->with(['folder', 'folderLocation'])
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

            // Folder remains occupied (is_available stays false) during archive/restore cycle.

            AuditService::log(
                'restored',
                'Restored employee from archive',
                $employee
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
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($employee) {
            // Free the folder if assigned
            if ($employee->folder_id) {
                Folder::where('id', $employee->folder_id)->update(['is_available' => true]);
            }

            $name = $employee->full_name;
            $systemId = $employee->system_id;

            AuditService::log(
                'deleted',
                "Permanently deleted employee (System ID: {$systemId})",
                $employee
            );

            $employee->forceDelete();
        });

        return redirect()
            ->route('archives.index', ['tab' => 'employees'])
            ->with('success', 'Employee permanently deleted. Folder is now available.');
    }

    /**
     * Get employee details as JSON (for Archive modal).
     */
    public function details(int $id)
    {
        $employee = Employee::withTrashed()
            ->with(['company', 'folder', 'folderLocation'])
            ->findOrFail($id);

        return response()->json([
            'name' => $employee->full_name,
            'system_id' => $employee->system_id,
            'barcode_id' => $employee->barcode_id ?: '—',
            'folder_code' => $employee->folder?->folder_code ?: '—',
            'location' => $employee->folderLocation?->full_location ?: '—',
            'company' => $employee->company?->name ?: '— Not Assigned —',
            'date_hired' => $employee->date_hired ? $employee->date_hired->format('F d, Y') : '—',
            'archive_date' => $employee->archive_date ? $employee->archive_date->format('F d, Y') : '—',
            'archived_at' => $employee->deleted_at?->format('F d, Y h:i A'),
            'status' => ucfirst($employee->status),
        ]);
    }

    /**
     * Get employee update history as JSON (for History modal).
     */
    public function updateHistory(int $id)
    {
        $logs = AuditLog::where('model_type', Employee::class)
            ->where('model_id', $id)
            ->whereIn('action', ['created', 'updated', 'restored'])
            ->with('user')
            ->latest('created_at')
            ->get();

        return response()->json($logs->map(function ($log) {
            return [
                'user_name' => $log->user?->name ?: 'System',
                'user_role' => $log->user?->role ?: 'System',
                'date' => $log->created_at->format('M d, Y'),
                'time' => $log->created_at->format('h:i A'),
                'description' => $log->description,
                'changes' => $log->changes,
                'action' => $log->action,
            ];
        }));
    }
}
