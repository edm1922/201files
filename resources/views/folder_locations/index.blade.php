<x-app-layout>
    <div x-data="locationManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Folder Locations</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage your physical storage structure (Row & Column).</p>
            </div>
            <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal"
                data-bs-target="#createLocationModal">
                <i class="fas fa-plus"></i> Add Folder Location
            </button>
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
                        <button class="accordion-button collapsed py-3 px-4" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#collapse{{ $loop->index }}" aria-expanded="false" aria-controls="collapse{{ $loop->index }}"
                            style="background-color: #ffffff; color: #111827; font-weight: 600;">
                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #dd270d 0%, #a81d0a 100%); color: #fff;">
                                        <i class="fas fa-layer-group" style="font-size: 0.9rem;"></i>
                                    </div>
                                    <span style="font-size: 1.05rem;">Row {{ $rowName }}</span>
                                </div>
                                <span class="badge rounded-pill bg-light text-dark border" style="font-size: 0.75rem; padding: 6px 12px;">
                                    Total: {{ count($locations) }}
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#rowAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead style="background-color: #f9fafb;">
                                        <tr>
                                            <th class="px-4 py-2 text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.025em;">Column</th>
                                            <th class="px-4 py-2 text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.025em;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $columnCounts = $locations->groupBy('column_code')->map->count()->sortKeys();
                                        @endphp
                                        @foreach($columnCounts as $columnCode => $total)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <span class="fw-bold" style="color: #4b5563;">{{ $columnCode }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="fw-semibold">{{ $total }}</span>
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
        @include('folder_locations.create')
        @include('folder_locations.edit')
        @include('companies.confirm_modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationManager', () => ({
                editUrl: '',
                editData: {
                    id: '',
                    row_name: '',
                    column_code: '',
                    folder_code: '',
                    is_available: true
                },

                confirmActionUrl: '',
                confirmMethod: 'DELETE',
                confirmTitle: '',
                confirmMessage: '',
                confirmButtonText: '',
                confirmTheme: '',
                confirmIcon: '',

                init() {
                    @if ($errors->any() && old('_method') === 'PUT')
                        this.editUrl = '{{ url('settings/folder-locations') }}/{{ old('id') }}';
                        this.editData = {
                            id: '{{ old('id') }}',
                            row_name: '{!! addslashes(old('row_name')) !!}',
                            column_code: '{!! addslashes(old('column_code')) !!}',
                            folder_code: '{!! addslashes(old('folder_code')) !!}',
                            is_available: {{ old('is_available') ? 'true' : 'false' }}
                        };
                        var modal = new bootstrap.Modal(document.getElementById('editLocationModal'));
                        modal.show();
                    @endif
                },

                openEditModal(location) {
                    this.editData = { ...location };
                    this.editUrl = `{{ url('settings/folder-locations') }}/${location.id}`;
                    var modal = new bootstrap.Modal(document.getElementById('editLocationModal'));
                    modal.show();
                },

                openConfirmModal(id, name) {
                    this.confirmActionUrl = `{{ url('settings/folder-locations') }}/${id}`;
                    this.confirmMethod = 'DELETE';
                    this.confirmTitle = 'Delete Location';
                    this.confirmMessage = `Are you sure you want to permanently delete the location <strong>${name}</strong>? This action cannot be undone.`;
                    this.confirmButtonText = 'Yes, Delete Location';
                    this.confirmTheme = 'danger';
                    this.confirmIcon = 'fas fa-trash-alt';

                    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    modal.show();
                }
            }));
        });
    </script>
</x-app-layout>
