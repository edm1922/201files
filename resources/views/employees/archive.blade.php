<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Employee Archive</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Resigned employees that have been archived. Only admins can restore or permanently delete.</p>
        </div>
        <a href="{{ route('201files') }}" class="btn btn-secondary d-inline-flex align-items-center gap-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Back to 201 Files
        </a>
    </div>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-left: 4px solid #27ae60; border-radius: 8px;">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-left: 4px solid #e74c3c; border-radius: 8px;">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ── Archive Table ── --}}
    <div x-data="archiveManager()" class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        @if($employees->count() > 0)
            <div class="p-0">
                <table class="table table-hover mb-0 align-middle" style="font-size: 0.9rem;">
                    <thead style="background-color: #f9fafb;">
                        <tr>
                            <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Folder Code</th>
                            <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Full Name</th>
                            <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">System ID</th>
                            <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Location</th>
                            <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Archived Date</th>
                            <th class="border-0 text-uppercase text-muted text-center" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px; width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td class="border-bottom-0" style="padding: 16px 24px;">
                                    <span class="fw-semibold" style="color: #dd270d; font-family: 'Courier New', monospace;">
                                        {{ $employee->slot?->folder_code ?? '—' }}
                                    </span>
                                </td>
                                <td class="border-bottom-0 text-uppercase" style="padding: 16px 24px;">
                                    {{ $employee->full_name }}
                                </td>
                                <td class="border-bottom-0" style="padding: 16px 24px; font-family: 'Courier New', monospace;">
                                    {{ $employee->system_id }}
                                </td>
                                <td class="border-bottom-0" style="padding: 16px 24px;">
                                    {{ $employee->slot?->rack?->display_name ?? '—' }}
                                </td>
                                <td class="border-bottom-0" style="padding: 16px 24px;">
                                    {{ $employee->deleted_at?->format('M d, Y h:i A') }}
                                </td>
                                <td class="border-bottom-0 text-center" style="padding: 16px 24px;">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Restore Button --}}
                                        <form action="{{ route('employees.restore', $employee->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm"
                                                    title="Restore Employee"
                                                    style="border-radius: 6px; padding: 6px 12px; background-color: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 500; transition: all 0.2s;"
                                                    onmouseover="this.style.backgroundColor='rgba(16, 185, 129, 0.2)'"
                                                    onmouseout="this.style.backgroundColor='rgba(16, 185, 129, 0.1)'"
                                                    onclick="return confirm('Restore this employee to active status?')">
                                                <i class="fas fa-undo me-1" style="font-size: 0.8rem;"></i> Restore
                                            </button>
                                        </form>

                                        {{-- Permanently Delete Button --}}
                                        <button type="button" class="btn btn-sm"
                                                title="Permanently Delete"
                                                style="border-radius: 6px; padding: 6px 12px; background-color: #fef2f2; color: #ef4444; font-weight: 500; transition: all 0.2s;"
                                                onmouseover="this.style.backgroundColor='#fee2e2'"
                                                onmouseout="this.style.backgroundColor='#fef2f2'"
                                                @click="openConfirmModal({{ $employee->id }}, '{{ addslashes($employee->full_name) }}', '{{ $employee->slot?->folder_code }}')">
                                            <i class="fas fa-trash-alt me-1" style="font-size: 0.8rem;"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body text-center py-5">
                <i class="fas fa-archive mb-3" style="font-size: 2.5rem; opacity: 0.2;"></i>
                <h4 class="h5 fw-bold text-dark mb-1">No Archived Employees</h4>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Resigned employees will appear here automatically.</p>
            </div>
        @endif

        {{-- ── Confirm Delete Modal ── --}}
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 12px; border: none;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" style="color: #111827;">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Permanently Delete Employee
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="color: #4b5563;">
                        <p class="mb-2">Are you sure you want to <strong class="text-danger">permanently delete</strong> this employee?</p>
                        <div class="p-3 rounded" style="background: #fef2f2;">
                            <div class="fw-semibold" style="color: #991b1b;" x-text="confirmName"></div>
                            <div class="text-muted" style="font-size: 0.85rem;">Folder Code: <span x-text="confirmFolderCode" style="font-family: 'Courier New', monospace; color: #dd270d;"></span></div>
                        </div>
                        <p class="mt-3 mb-0 text-muted" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            This will free the folder slot for reuse. This action <strong>cannot be undone</strong>.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                        <form :action="confirmActionUrl" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="border-radius: 8px;">
                                <i class="fas fa-trash-alt me-1"></i> Yes, Delete Permanently
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('archiveManager', () => ({
                confirmActionUrl: '',
                confirmName: '',
                confirmFolderCode: '',

                openConfirmModal(id, name, folderCode) {
                    this.confirmActionUrl = `/employees/${id}/force-delete`;
                    this.confirmName = name;
                    this.confirmFolderCode = folderCode || '—';
                    var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                    modal.show();
                }
            }));
        });
    </script>

</x-app-layout>
