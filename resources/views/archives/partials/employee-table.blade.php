<div x-data="archiveManager()">
    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
    @if($employees->count() > 0)
        <div class="p-0 table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size: 0.9rem;">
                <thead style="background-color: #f9fafb;">
                    <tr>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Folder Code</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Full Name</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">System ID</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Date Hired</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Location</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Date Archived</th>
                        <th class="border-0 text-uppercase text-muted text-center" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px; width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        <tr>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                <span class="fw-semibold font-monospace" style="color: #dd270d;">
                                    {{ $employee->folder?->folder_code ?? '—' }}
                                </span>
                            </td>
                            <td class="border-bottom-0 text-uppercase" style="padding: 16px 24px;">
                                {{ $employee->full_name }}
                            </td>
                            <td class="border-bottom-0 font-monospace" style="padding: 16px 24px;">
                                {{ $employee->system_id }}
                            </td>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                {{ $employee->date_hired ? $employee->date_hired->format('M d, Y') : '—' }}
                            </td>
                            <td class="fw-semibold font-monospace" style="padding: 16px 24px; color: #dd270d;">
                                {{ $employee->folderLocation?->full_location ?? '—' }}
                            </td>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                {{ $employee->deleted_at?->format('M d, Y h:i A') }}
                            </td>
                            <td class="border-bottom-0 text-center" style="padding: 16px 24px;">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- See Details Button --}}
                                    <button type="button" class="btn btn-sm"
                                            title="See Details"
                                            style="border-radius: 6px; padding: 6px 12px; background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; font-weight: 500; transition: all 0.2s; border: none;"
                                            onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.2)'"
                                            onmouseout="this.style.backgroundColor='rgba(59, 130, 246, 0.1)'"
                                            @click="fetchDetails({{ $employee->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Restore Button --}}
                                    <form action="{{ route('employees.restore', $employee->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm"
                                                title="Restore Employee"
                                                style="border-radius: 6px; padding: 6px 12px; background-color: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 500; transition: all 0.2s; border: none;"
                                                onmouseover="this.style.backgroundColor='rgba(16, 185, 129, 0.2)'"
                                                onmouseout="this.style.backgroundColor='rgba(16, 185, 129, 0.1)'"
                                                onclick="return confirm('Restore this employee to active status?')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>

                                    {{-- Permanently Delete Button (Admin only) --}}
                                    @if(Auth::user()->isAdmin())
                                        <button type="button" class="btn btn-sm"
                                                title="Permanently Delete"
                                                style="border-radius: 6px; padding: 6px 12px; background-color: #fef2f2; color: #ef4444; font-weight: 500; transition: all 0.2s; border: none;"
                                                onmouseover="this.style.backgroundColor='#fee2e2'"
                                                onmouseout="this.style.backgroundColor='#fef2f2'"
                                                @click="openConfirmModal({{ $employee->id }}, '{{ addslashes($employee->full_name) }}', '{{ $employee->folder?->folder_code }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card-body text-center py-5">
            <i class="fas fa-archive mb-3" style="font-size: 2.5rem; color: #cbd5e1;"></i>
            <h4 class="h5 fw-bold text-dark mb-1">No Archived Employees</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Resigned employees will appear here automatically.</p>
        </div>
    @endif
    </div>

    {{-- Employee Details Modal --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
                <div class="modal-header text-white" style="background-color: #1e293b;">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-id-card me-2"></i>Employee Archive Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-3" style="background-color: #f8fafc; border-left: 4px solid #dd270d;">
                                <label class="text-muted small text-uppercase fw-bold mb-1">Full Name</label>
                                <div class="h5 mb-0 text-dark fw-bold text-uppercase" x-text="selectedEmployee.name"></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 rounded-3" style="background-color: #f8fafc;">
                                        <label class="text-muted small text-uppercase fw-bold mb-1">System ID</label>
                                        <div class="mb-0 fw-semibold font-monospace" x-text="selectedEmployee.system_id"></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-3" style="background-color: #f8fafc;">
                                        <label class="text-muted small text-uppercase fw-bold mb-1">Barcode ID</label>
                                        <div class="mb-0 fw-semibold font-monospace" x-text="selectedEmployee.barcode_id"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-3" style="background-color: #f8fafc;">
                                <label class="text-muted small text-uppercase fw-bold mb-1">Company</label>
                                <div class="h6 mb-0 text-dark fw-semibold" x-text="selectedEmployee.company"></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3">
                                        <label class="text-muted small text-uppercase fw-bold mb-1">Folder Code</label>
                                        <div class="mb-0 fw-bold font-monospace" style="color: #dd270d;" x-text="selectedEmployee.folder_code"></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3">
                                        <label class="text-muted small text-uppercase fw-bold mb-1">Location</label>
                                        <div class="mb-0 fw-semibold font-monospace" style="color: #dd270d;" x-text="selectedEmployee.location"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-1 opacity-10" style="border-top: 2px dashed #e2e8f0;">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background-color: #f8fafc;">
                                <label class="text-muted small text-uppercase fw-bold mb-1">Date Hired</label>
                                <div class="mb-0 fw-semibold" x-text="selectedEmployee.date_hired"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background-color: #f8fafc;">
                                <label class="text-muted small text-uppercase fw-bold mb-1">Status</label>
                                <div class="mb-0 fw-semibold">
                                    <span class="badge" :class="'bg-' + (selectedEmployee.status === 'Active' ? 'success' : 'secondary')" x-text="selectedEmployee.status"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background-color: #fef2f2; border: 1px dashed #fecaca;">
                                <label class="text-muted small text-uppercase fw-bold mb-1" style="color: #991b1b;">Date Resigned</label>
                                <div class="mb-0 fw-semibold" style="color: #dc2626;" x-text="selectedEmployee.archive_date"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary px-4 fw-medium" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm Delete Modal --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: #0f172a;">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Permanently Delete Employee
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="color: #475569;">
                    <p class="mb-2">Are you sure you want to <strong class="text-danger">permanently delete</strong> this employee?</p>
                    <div class="p-3 rounded-3" style="background-color: #fef2f2; border: 1px solid #fee2e2;">
                        <div class="fw-semibold" style="color: #991b1b;" x-text="confirmName"></div>
                        <div class="text-muted" style="font-size: 0.85rem;">Folder Code: <span x-text="confirmFolderCode" class="font-monospace fw-bold" style="color: #dd270d;"></span></div>
                    </div>
                    <p class="mt-3 mb-0 text-muted" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        This will free the folder slot for reuse. This action <strong>cannot be undone</strong>.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light fw-medium hover-bg-light" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <form :action="confirmActionUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger fw-medium shadow-sm transition-colors duration-200" style="border-radius: 8px;">
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
        if (!Alpine.data('archiveManager')) {
            Alpine.data('archiveManager', () => ({
                confirmActionUrl: '',
                confirmName: '',
                confirmFolderCode: '',
                selectedEmployee: {},
                isLoading: false,

                async fetchDetails(id) {
                    this.isLoading = true;
                    try {
                        const response = await fetch(`/employees/${id}/details`);
                        this.selectedEmployee = await response.json();
                        var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                        modal.show();
                    } catch (error) {
                        console.error('Error fetching employee details:', error);
                        alert('Could not load employee details.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                openConfirmModal(id, name, folderCode) {
                    this.confirmActionUrl = `/employees/${id}/force-delete`;
                    this.confirmName = name;
                    this.confirmFolderCode = folderCode || '—';
                    var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                    modal.show();
                }
            }));
        }
    });
</script>
