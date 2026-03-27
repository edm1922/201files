<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a paginated list of activity logs.
     */
    public function index(Request $request)
    {
        $query = \App\Models\AuditLog::with(['user', 'model'])->orderBy('created_at', 'desc');

        if ($request->has('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->has('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('reports.audit-log', compact('logs'));
    }
}
