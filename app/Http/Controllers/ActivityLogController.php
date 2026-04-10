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

        if ($request->has('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = \App\Models\User::orderBy('last_name')->get();

        return view('reports.audit-log', compact('logs', 'users'));
    }
}
