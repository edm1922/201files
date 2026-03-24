<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- ── Page Header ── --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 fw-bold">Audit & Activity Logs</h2>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Track system activities and administrative actions.</p>
                </div>
                <form id="filterForm" action="{{ route('reports.audit-log') }}" method="GET" class="d-flex align-items-center">
                    <input type="hidden" name="action" id="actionInput" value="{{ request('action', 'all') }}">
                    <div class="dropdown">
                        <button class="btn btn-sm text-white dropdown-toggle shadow-none" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: rgb(221, 39, 13); width: 160px; border-radius: 6px; font-weight: 500;">
                            <i class="fas fa-filter me-2 opacity-75"></i>
                            {{ request('action') ? ucfirst(request('action')) : 'All Actions' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="dropdownMenuButton" style="border-radius: 8px; padding: 5px; min-width: 160px;">
                            <li><a class="dropdown-item rounded {{ request('action', 'all') == 'all' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('all')">All Actions</a></li>
                            <li><hr class="dropdown-divider opacity-50"></li>
                            <li><a class="dropdown-item rounded {{ request('action') == 'created' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('created')">Created</a></li>
                            <li><a class="dropdown-item rounded {{ request('action') == 'updated' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('updated')">Updated</a></li>
                            <li><a class="dropdown-item rounded {{ request('action') == 'deleted' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('deleted')">Deleted</a></li>
                            <li><a class="dropdown-item rounded {{ request('action') == 'archived' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('archived')">Archived</a></li>
                            <li><a class="dropdown-item rounded {{ request('action') == 'restored' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('restored')">Restored</a></li>
                            <li><a class="dropdown-item rounded {{ request('action') == 'login' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('login')">Login</a></li>
                            <li><a class="dropdown-item rounded {{ request('action') == 'logout' ? 'active-filter' : '' }}" href="#" onclick="applyFilter('logout')">Logout</a></li>
                        </ul>
                    </div>
                </form>

                <script>
                    function applyFilter(action) {
                        document.getElementById('actionInput').value = action;
                        document.getElementById('filterForm').submit();
                    }
                </script>
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

        .dropdown-item {
            font-size: 0.85rem;
            padding: 8px 12px;
            color: #4b5563;
            transition: all 0.2s;
        }

        .dropdown-item:hover, .active-filter {
            background-color: rgb(221, 39, 13) !important;
            color: white !important;
        }
    </style>
</x-app-layout>
