<x-app-layout>
    <div x-data="locationManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Folder Locations</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage your physical storage structure (Row & Column).</p>
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
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert"
                style="border-left: 4px solid #27ae60; border-radius: 8px;">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert"
                style="border-left: 4px solid #e74c3c; border-radius: 8px;">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ── Row Accordions ── --}}
        <div class="accordion accordion-flush shadow-sm border-0" id="rowAccordion" style="border-radius: 12px; overflow: hidden;">
            @forelse($rows as $rowName => $locations)
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="heading{{ $loop->index }}">
                        <div class="accordion-button collapsed py-3 px-4 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#collapse{{ $loop->index }}" aria-expanded="false" aria-controls="collapse{{ $loop->index }}"
                            style="background-color: #ffffff; color: #111827; font-weight: 600; cursor: pointer;">
                            
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #dd270d 0%, #a81d0a 100%); color: #fff;">
                                    <i class="fas fa-layer-group" style="font-size: 0.9rem;"></i>
                                </div>
                                <span style="font-size: 1.05rem;">Row {{ $rowName }}</span>
                            </div>
                            
                            <div class="d-flex gap-3 align-items-center me-4">
                                <span class="badge rounded-pill bg-light text-dark border" style="font-size: 0.75rem; padding: 6px 12px;">
                                    Total: {{ count($locations) }}
                                </span>
                                <div class="d-flex gap-2">
                                    <form action="{{ route('settings.folder-locations.store-column', $rowName) }}" method="POST" @click.stop>
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary bg-white" style="padding: 2px 8px; font-size: 0.75rem;" title="Add Column">
                                            <i class="fas fa-plus"></i> Column
                                        </button>
                                    </form>
                                    
                                    @php
                                        $isRowOccupied = $locations->contains(function($loc) {
                                            return $loc->employees_count > 0 || $loc->departments->isNotEmpty();
                                        });
                                    @endphp
                                    <button type="button" class="btn btn-sm btn-outline-danger bg-white" 
                                        @if($isRowOccupied) disabled title="Cannot delete row with occupied folders" @else @click.stop.prevent="openConfirmRowModal('{{ $rowName }}')" @endif
                                        style="padding: 2px 8px; font-size: 0.75rem;">
                                        <i class="fas fa-trash-alt"></i> Row
                                    </button>
                                </div>
                            </div>
                        </div>
                    </h2>
                    <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#rowAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead style="background-color: #f9fafb;">
                                        <tr>
                                            <th class="px-4 py-2 text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.025em;">Column</th>
                                            <th class="px-4 py-2 text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.025em;">Total</th>
                                            <th class="px-4 py-2 text-muted fw-bold text-center" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.025em; width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $columnCounts = $locations->groupBy('column_code')->sortKeys();
                                        @endphp
                                        @foreach($columnCounts as $columnCode => $colLocations)
                                            @php
                                                $totalEmployees = $colLocations->sum('employees_count');
                                                $isColOccupied = $colLocations->contains(function($loc) {
                                                    return $loc->employees_count > 0 || $loc->departments->isNotEmpty();
                                                });
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <span class="fw-bold" style="color: #4b5563;">{{ $columnCode }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="fw-bold" style="color: {{ $totalEmployees > 0 ? '#10b981' : '#dd270d' }};">
                                                        {{ $totalEmployees }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                        @if($isColOccupied) disabled title="Cannot delete occupied column" @else @click="openConfirmColumnModal('{{ $rowName }}', '{{ $columnCode }}')" @endif 
                                                        style="padding: 4px 8px;">
                                                        <i class="fas fa-trash-alt fa-sm"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card shadow-sm border-0 text-center py-5" style="border-radius: 12px;">
                    <div class="card-body">
                        <i class="fas fa-box-open mb-3 d-block" style="font-size: 3rem; opacity: 0.15; color: #dd270d;"></i>
                        <h5 class="text-muted fw-bold">No Folder Locations</h5>
                        <p class="text-muted mb-0">Start by adding a new folding location to your rows.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Modals --}}
        @include('companies.confirm_modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationManager', () => ({
                confirmActionUrl: '',
                confirmMethod: 'DELETE',
                confirmTitle: '',
                confirmMessage: '',
                confirmButtonText: '',
                confirmTheme: '',
                confirmIcon: '',

                openConfirmRowModal(rowName) {
                    this.confirmActionUrl = `{{ url('settings/folder-locations/row') }}/${rowName}`;
                    this.confirmMethod = 'DELETE';
                    this.confirmTitle = 'Delete Row';
                    this.confirmMessage = `Are you sure you want to permanently delete <strong>Row ${rowName}</strong> and all its empty columns? This action cannot be undone.`;
                    this.confirmButtonText = 'Yes, Delete Row';
                    this.confirmTheme = 'danger';
                    this.confirmIcon = 'fas fa-trash-alt';

                    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    modal.show();
                },

                openConfirmColumnModal(rowName, columnCode) {
                    this.confirmActionUrl = `{{ url('settings/folder-locations/row') }}/${rowName}/column/${columnCode}`;
                    this.confirmMethod = 'DELETE';
                    this.confirmTitle = 'Delete Column';
                    this.confirmMessage = `Are you sure you want to permanently delete <strong>Column ${columnCode}</strong> from Row ${rowName}? This action cannot be undone.`;
                    this.confirmButtonText = 'Yes, Delete Column';
                    this.confirmTheme = 'danger';
                    this.confirmIcon = 'fas fa-trash-alt';

                    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    modal.show();
                }
            }));
        });
    </script>
</x-app-layout>
