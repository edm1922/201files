<x-app-layout>
    <div x-data="locationManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Folder Locations</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage your physical storage rows and their employee capacity.</p>
            </div>
            <form action="{{ route('settings.folder-locations.store-row') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
                    <i class="fas fa-plus"></i> Add Row
                </button>
            </form>
        </div>

        {{-- ── Flash Messages ── --}}
        @if (session('success'))
            <div class="alert-flash alert-flash--success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert-flash alert-flash--error">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- ── Rows Table ── --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background-color: #f9fafb;">
                        <tr>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em;">Row</th>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em;">Employee Range</th>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em;">Occupancy</th>
                            <th class="px-4 py-3 text-muted fw-bold text-center" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em; width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $location)
                            @php
                                $occupancy = $location->employees_count;
                                $capacity = $location->max_capacity ?? 500;
                                $percent = ($occupancy / $capacity) * 100;
                                
                                // Color logic for occupancy
                                if ($percent >= 100) {
                                    $statusColor = '#dc2626'; // Red
                                    $statusBg = '#fef2f2';
                                } elseif ($percent >= 80) {
                                    $statusColor = '#f59e0b'; // Amber
                                    $statusBg = '#fffbeb';
                                } else {
                                    $statusColor = '#10b981'; // Green
                                    $statusBg = '#f0fdf4';
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #dd270d 0%, #a81d0a 100%); color: #fff;">
                                            <i class="fas fa-layer-group" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <span class="fw-bold" style="font-size: 1rem; color: #111827;">Row {{ $location->row_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2" style="font-size: 0.85rem; font-family: monospace;">
                                        {{ $location->range }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-grow-1" style="height: 8px; background-color: #e5e7eb; border-radius: 10px; overflow: hidden; max-width: 200px;">
                                            <div style="height: 100%; width: {{ min($percent, 100) }}%; background-color: {{ $statusColor }}; border-radius: 10px; transition: width 0.5s ease-out;"></div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold" style="font-size: 0.9rem; color: {{ $statusColor }};">
                                                {{ $occupancy }}
                                            </span>
                                            <span class="text-muted mx-1" style="font-size: 0.8rem;">/</span>
                                            <span class="text-muted" style="font-size: 0.8rem;">
                                                {{ $capacity }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                            @if($occupancy > 0) disabled title="Cannot delete row with assigned employees" @else @click="openConfirmModal('{{ $location->id }}', '{{ $location->row_name }}')" @endif
                                            style="padding: 6px 10px; border-radius: 6px; transition: all 0.2s;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-box-open mb-3 d-block" style="font-size: 3rem; opacity: 0.15; color: #dd270d;"></i>
                                    <h5 class="text-muted fw-bold">No Folder Locations</h5>
                                    <p class="text-muted mb-0">Click "Add Row" to start organizing your physical documents.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fee2e2; color: #dc2626; border-radius: 50%;">
                            <i class="fas fa-exclamation-triangle fa-2z"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Delete Row?</h4>
                        <p class="text-muted mb-4" x-html="confirmMessage"></p>
                        
                        <div class="d-grid gap-2">
                            <form :action="confirmActionUrl" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold" style="border-radius: 10px;">
                                    Yes, Delete Row
                                </button>
                            </form>
                            <button type="button" class="btn btn-light w-100 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationManager', () => ({
                confirmActionUrl: '',
                confirmMessage: '',

                openConfirmModal(id, rowName) {
                    this.confirmActionUrl = `{{ url('settings/folder-locations') }}/${id}`;
                    this.confirmMessage = `Are you sure you want to delete <strong>Row ${rowName}</strong>? This action cannot be undone.`;

                    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    modal.show();
                }
            }));
        });
    </script>
</x-app-layout>
