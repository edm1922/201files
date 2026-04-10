<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Show the report generation interface.
     */
    public function index()
    {
        $companies = Company::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $documentTypes = \App\Models\DocumentType::orderBy('name')->get();
        $users = \App\Models\User::orderBy('last_name')->get();

        return view('reports.generate', compact('companies', 'departments', 'documentTypes', 'users'));
    }

    /**
     * Export Employee Master List to CSV.
     */
    public function exportEmployees(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['nullable', Rule::in(['active', 'awol', 'resigned'])],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'columns' => ['nullable', 'array'],
        ]);

        $query = Employee::with(['company', 'folder', 'folderLocation', 'bankType']);

        if (!empty($validated['company_id'])) {
            $query->where('company_id', (int) $validated['company_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
            if ($validated['status'] === 'resigned') {
                $query->withTrashed();
            }
        }

        if (!empty($validated['year'])) {
            $query->whereYear('date_hired', $validated['year']);
        }

        if (!empty($validated['month'])) {
            $query->whereMonth('date_hired', $validated['month']);
        }

        // If filtering by department, we might need a join or a whereHas if employees were linked to departments
        // However, in this system, employees are primarily linked to Companies and Physical Locations.
        // We will scope by Company if department is requested but not directly available on Employee.

        $employees = $query->orderBy('last_name')->orderBy('first_name')->get();

        // Define all possible columns and their headers
        $columnMap = [
            'system_id' => 'System ID',
            'barcode_id' => 'Barcode ID',
            'full_name' => 'Full Name',
            'company' => 'Company',
            'status' => 'Status',
            'date_hired' => 'Date Hired',
            'folder_code' => 'Folder Code',
            'location' => 'Physical Location',
            'atm_status' => 'ATM Status',
            'bank_type' => 'Bank Type',
            'archive_date' => 'Archive Date',
        ];

        // Determine which columns to export
        $selectedColumns = $validated['columns'] ?? array_keys($columnMap);
        $headersRow = array_values(array_intersect_key($columnMap, array_flip($selectedColumns)));

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_master_list_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($employees, $selectedColumns, $headersRow) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headersRow);

            foreach ($employees as $emp) {
                $row = [];
                foreach ($selectedColumns as $col) {
                    $row[] = match ($col) {
                        'system_id' => $emp->system_id,
                        'barcode_id' => $emp->barcode_id,
                        'full_name' => $emp->full_name,
                        'company' => $emp->company?->name ?? 'N/A',
                        'status' => ucfirst($emp->status),
                        'date_hired' => $emp->date_hired?->format('Y-m-d') ?? '',
                        'folder_code' => $emp->folder?->folder_code ?? '',
                        'location' => $emp->folderLocation ? $emp->folderLocation->full_location : '',
                        'atm_status' => $emp->atm_status,
                        'bank_type' => $emp->bankType?->name ?? 'N/A',
                        'archive_date' => $emp->archive_date?->format('Y-m-d') ?? ($emp->deleted_at?->format('Y-m-d') ?? ''),
                        default => '',
                    };
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Company Summary (Total Employees per Company).
     */
    public function exportCompanySummary(): StreamedResponse
    {
        $companies = Company::withCount([
            'employees as active_count' => function ($query) {
                $query->where('status', '!=', 'resigned');
            },
            'employees as resigned_count' => function ($query) {
                $query->where('status', 'resigned')->withTrashed();
            },
        ])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="company_summary_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($companies) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Company Name',
                'Active Employees',
                'Resigned Employees',
                'Total Employees',
            ]);

            foreach ($companies as $company) {
                $total = $company->active_count + $company->resigned_count;
                fputcsv($file, [
                    $company->name,
                    $company->active_count,
                    $company->resigned_count,
                    $total,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Storage Utilization (Folders per Row/Column).
     */
    public function exportStorageUtilization(Request $request): StreamedResponse
    {
        $type = $request->get('type', '201');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="storage_audit_' . $type . '_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($type) {
            $file = fopen('php://output', 'w');

            if ($type === '201') {
                fputcsv($file, ['Location', 'Total Slots', 'Occupied Slots', 'Available Slots']);
                $locations = \App\Models\FolderLocation::withCount(['employees' => function($query) {
                    $query->withTrashed();
                }])
                    ->orderByRaw('LENGTH(row_name) ASC')
                    ->orderBy('row_name', 'ASC')
                    ->get();

                foreach ($locations as $loc) {
                    $total = $loc->max_capacity ?? 500;
                    $occupied = $loc->employees_count;
                    $left = max(0, $total - $occupied);
                    fputcsv($file, [$loc->full_location, $total, $occupied, $left]);
                }
            } else {
                fputcsv($file, ['Storage Location', 'Unique Folders Count', 'Total Documents Count']);
                // Department Documents use DocumentLocation
                $locations = \App\Models\DocumentLocation::withCount([
                    'documents',
                    'documents as unique_folders_count' => function ($q) {
                        $q->select(\DB::raw('count(distinct(document_folder_id))'));
                    }
                ])->get();

                foreach ($locations as $loc) {
                    fputcsv($file, [
                        $loc->name,
                        $loc->unique_folders_count ?? 0,
                        $loc->documents_count ?? 0
                    ]);
                }
            }
            fclose($file);
        }, 200, $headers);
    }

    /**
     * Export Available Folders (Individual reassignable folder codes).
     */
    public function exportAvailableFolders(): StreamedResponse
    {
        $folders = \App\Models\Folder::where('is_available', true)->orderBy('folder_code', 'asc')->get();

        // Build a lookup map for locations based on row index
        $locations = \App\Models\FolderLocation::all();
        $locationMap = [];
        foreach ($locations as $loc) {
            $idx = $loc->getRowIndex();
            if ($idx > 0) {
                $locationMap[$idx] = $loc;
            }
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="available_folder_codes_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($folders, $locationMap) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Folder Code', 'Physical Location', 'Range Status']);

            foreach ($folders as $folder) {
                // Extract numeric part from code (e.g., CSC-HR-0001 -> 1)
                $codeString = $folder->folder_code;
                preg_match('/\d+$/', $codeString, $matches);
                $numericPart = isset($matches[0]) ? (int) $matches[0] : 0;

                // standard 500 capacity mapping
                $rowIdx = ($numericPart > 0) ? (int) ceil($numericPart / 500) : 0;
                $locGroup = $locationMap[$rowIdx] ?? null;

                fputcsv($file, [
                    $codeString,
                    $locGroup ? 'Row ' . $locGroup->row_name : 'Unknown Location',
                    $locGroup ? $locGroup->range : 'N/A'
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Export Audit Logs to CSV.
     */
    public function exportAuditLogs(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if (!empty($validated['user_id'])) {
            $query->where('user_id', (int) $validated['user_id']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        if (!empty($validated['year'])) {
            $query->whereYear('created_at', $validated['year']);
        }

        if (!empty($validated['month'])) {
            $query->whereMonth('created_at', $validated['month']);
        }

        $logs = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Timestamp',
                'User',
                'Action',
                'Entity Type',
                'Description',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    strtoupper($log->action),
                    $log->model_type ? class_basename($log->model_type) : 'N/A',
                    $log->description,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Document Expiry report to CSV.
     */
    public function exportExpiryReport(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'expiry_status' => ['nullable', 'string', 'in:all,expired,expiring,valid'],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $query = \App\Models\Document::with(['department', 'documentType', 'documentFolder'])
            ->whereNotNull('expiry_date')
            ->where('status', 'active');

        if (!empty($validated['department_id'])) {
            $query->where('department_id', (int) $validated['department_id']);
        }

        // Apply Status Logic
        $status = $validated['expiry_status'] ?? 'all';
        $now = now()->startOfDay();
        $thirtyDays = now()->addDays(30)->endOfDay();

        if ($status === 'expired') {
            $query->whereDate('expiry_date', '<', $now);
        } elseif ($status === 'expiring') {
            $query->whereBetween('expiry_date', [$now, $thirtyDays]);
        } elseif ($status === 'valid') {
            $query->whereDate('expiry_date', '>', $thirtyDays);
        }

        if (!empty($validated['year'])) {
            $query->whereYear('expiry_date', $validated['year']);
        }

        if (!empty($validated['month'])) {
            $query->whereMonth('expiry_date', $validated['month']);
        }

        $documents = $query->orderBy('expiry_date', 'asc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="expiry_report_' . $status . '_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($documents) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Expiry Status', 'Expiry Date', 'Filename', 'Document Type', 'Department']);

            foreach ($documents as $doc) {
                $status = 'Valid';
                if ($doc->expiry_date->isPast())
                    $status = 'EXPIRED';
                elseif ($doc->isExpiringSoon(30))
                    $status = 'Expiring Soon';

                fputcsv($file, [
                    $status,
                    $doc->expiry_date->format('Y-m-d'),
                    $doc->original_filename,
                    $doc->documentType?->name ?? 'N/A',
                    $doc->department?->name ?? 'N/A',
                ]);
            }
            fclose($file);
        }, 200, $headers);
    }
}
