<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- ── Page Header ── --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 fw-bold">Audit & Activity Logs</h2>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Track system activities and administrative actions.</p>
                </div>
                <form action="{{ route('reports.audit-log') }}" method="GET" class="d-flex">
                    <select name="action" class="form-select form-select-sm me-2" onchange="this.form.submit()" style="width: 150px;">
                        <option value="all">All Actions</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="archived" {{ request('action') == 'archived' ? 'selected' : '' }}>Archived</option>
                        <option value="restored" {{ request('action') == 'restored' ? 'selected' : '' }}>Restored</option>
                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                    </select>
                </form>
            </div>

            <div class="card shadow-sm">
                <div class="doc-table-wrapper" style="border: none;">
                    <table class="doc-table">
                        <thead>
                                <tr>
                                    <th class="ps-4">User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Target</th>
                                    <th>IP Address</th>
                                    <th class="pe-4">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-secondary" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $log->user?->name ?: 'System' }}</div>
                                                    <div class="small text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $log->user?->role ?: 'System' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ 
                                                $log->action === 'created' ? 'bg-success-subtle text-success' : 
                                                ($log->action === 'updated' ? 'bg-info-subtle text-info' : 
                                                ($log->action === 'deleted' ? 'bg-danger-subtle text-danger' : 
                                                ($log->action === 'archived' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary'))) 
                                            }}" style="font-size: 0.7rem; padding: 4px 10px; border: 1px solid currentColor;">
                                                <i class="fas {{ 
                                                    $log->action === 'created' ? 'fa-plus-circle' : 
                                                    ($log->action === 'updated' ? 'fa-edit' : 
                                                    ($log->action === 'deleted' ? 'fa-trash-alt' : 
                                                    ($log->action === 'archived' ? 'fa-archive' : 'fa-history'))) 
                                                }} me-1"></i>
                                                {{ strtoupper($log->action) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-dark fw-medium">{{ $log->description }}</div>
                                            @if($log->model_type)
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <i class="fas fa-tag me-1 text-secondary opacity-50"></i>
                                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                                </div>
                                            @endif
                                        <td>
                                            <div class="fw-bold text-dark">{{ $log->target_name }}</div>
                                            @if($log->model_type)
                                                <div class="small text-muted" style="font-size: 0.7rem;">{{ class_basename($log->model_type) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="small text-muted bg-light px-2 py-1 rounded">{{ $log->ip_address }}</code>
                                        </td>
                                        <td class="pe-4">
                                            <div class="text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                                            <div class="small text-muted">{{ $log->created_at->format('h:i A') }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-history d-block mb-3 opacity-25" style="font-size: 3rem;"></i>
                                            No activity logs found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @if($logs->hasPages())
                    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4">
                        <div class="text-muted" style="font-size: 0.8rem;">
                            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
                        </div>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .bg-success-subtle { background-color: #e8f5e9 !important; }
        .bg-info-subtle { background-color: #e3f2fd !important; }
        .bg-danger-subtle { background-color: #ffebee !important; }
        .bg-warning-subtle { background-color: #fff3e0 !important; }
        .bg-primary-subtle { background-color: #f3e5f5 !important; }
        
        .text-success { color: #2e7d32 !important; }
        .text-info { color: #0288d1 !important; }
        .text-danger { color: #d32f2f !important; }
        .text-warning { color: #ef6c00 !important; }
        .text-primary { color: #7b1fa2 !important; }

        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
            border-bottom-color: #f3f4f6;
        }
        
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }
    </style>
</x-app-layout>
