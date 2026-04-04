<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'employees');
        $user = $request->user();

        if ($tab === 'documents') {
            $accessibleDepartmentIds = $user->isAdmin()
                ? Department::query()->where('is_active', true)->pluck('id')
                : $user->authorizedDepartments()->where('is_active', true)->pluck('departments.id');

            $documents = Document::onlyTrashed()
                ->with(['department', 'documentType', 'documentLocation', 'documentFolder'])
                ->whereIn('department_id', $accessibleDepartmentIds)
                ->orderBy('deleted_at', 'desc')
                ->paginate(20)
                ->withQueryString();

            return view('archives.index', compact('tab', 'documents'));
        }

        // Default to Employees - only Admin/Encoder
        if (! $user->isAdmin() && ! $user->isEncoder()) {
            abort(403);
        }

        $employees = Employee::archived()
            ->with(['folder', 'folderLocation'])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('archives.index', compact('tab', 'employees'));
    }
}
