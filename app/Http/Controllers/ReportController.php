<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
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
        
        return view('reports.generate', compact('companies', 'departments'));
    }

    /**
     * Export Employee Master List to CSV.
     */
    public function exportEmployees(Request $request): StreamedResponse
    {
        $query = Employee::with(['company', 'folder', 'folderLocation']);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'archived') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->status);
            }
        }

        $employees = $query->orderBy('last_name')->orderBy('first_name')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_master_list_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'System ID',
                'Barcode ID',
                'Full Name',
                'Company',
                'Status',
                'Date Hired',
                'Folder Code',
                'Physical Location',
                'Archive Date'
            ]);

            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp->system_id,
                    $emp->barcode_id,
                    $emp->full_name,
                    $emp->company?->name ?? 'N/A',
                    ucfirst($emp->status),
                    $emp->date_hired?->format('Y-m-d') ?? '',
                    $emp->folder?->folder_code ?? '',
                    $emp->folderLocation ? ($emp->folderLocation->row_name . $emp->folderLocation->column_code) : '',
                    $emp->archive_date?->format('Y-m-d') ?? ($emp->deleted_at?->format('Y-m-d') ?? '')
                ]);
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
            }
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
                'Total Employees'
            ]);

            foreach ($companies as $company) {
                $total = $company->active_count + $company->resigned_count;
                fputcsv($file, [
                    $company->name,
                    $company->active_count,
                    $company->resigned_count,
                    $total
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Storage Utilization (Folders per Row/Column).
     */
    public function exportStorageUtilization(): StreamedResponse
    {
        $locations = \App\Models\FolderLocation::withCount('employees')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="storage_utilization_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($locations) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Location',
                'Status',
                'Occupied By (Employee)'
            ]);

            foreach ($locations as $loc) {
                $occupiedBy = $loc->employees->map(fn($e) => $e->full_name)->join('; ');
                fputcsv($file, [
                    $loc->row_name . $loc->column_code,
                    $loc->employees_count > 0 ? 'Occupied' : 'Available',
                    $occupiedBy ?: '—'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Available Folders (Physical slots not yet occupied).
     */
    public function exportAvailableFolders(): StreamedResponse
    {
        $locations = \App\Models\FolderLocation::available()->orderBy('row_name')->orderBy('column_code')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="available_folders_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($locations) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Location',
                'Status'
            ]);

            foreach ($locations as $loc) {
                fputcsv($file, [
                    $loc->row_name . $loc->column_code,
                    'Available'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Audit Logs to CSV.
     */
    public function exportAuditLogs(Request $request): StreamedResponse
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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
                'IP Address'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'System',
                    strtoupper($log->action),
                    $log->model_type ? class_basename($log->model_type) : 'N/A',
                    $log->description,
                    $log->ip_address
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
