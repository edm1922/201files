<x-app-layout>

    <div x-data="cabinetManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Physical Locations</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage the physical cabinets and racks where documents are stored.</p>
            </div>
            <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createRackModal">
                <i class="fas fa-plus"></i> Add Rack
            </button>
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

        {{-- ── Accordion List ── --}}
        <div class="d-flex flex-column gap-3 mb-5">
            @forelse($cabinets as $cabinet)
                @php
                    $totalSlots = $cabinet->racks->sum('slots_count');
                    $rackCount = $cabinet->racks->count();
                @endphp
                {{-- Cabinet Block --}}
                <div class="card shadow-sm border-0"
                     style="border-radius: 12px; overflow: hidden;"
                     x-data="{ expanded: false }">
                    
                    {{-- Cabinet Header (Clickable) --}}
                    <div class="card-header bg-white border-bottom-0 p-4 d-flex justify-content-between align-items-center"
                         style="cursor: pointer; transition: background 0.2s;"
                         @click="expanded = !expanded"
                         onmouseover="this.style.backgroundColor='#f9fafb'"
                         onmouseout="this.style.backgroundColor='#ffffff'">
                        
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(221, 39, 13, 0.08);">
                                <i class="fas fa-archive" style="color: #dd270d; font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h3 class="h5 mb-1 fw-bold" style="color: #111827;">{{ $cabinet->name }}</h3>
                                <div class="d-flex gap-3 text-muted" style="font-size: 0.85rem;">
                                    <span><i class="fas fa-layer-group me-1 opacity-50"></i> {{ $rackCount }} {{ Str::plural('Rack', $rackCount) }}</span>
                                    <span><i class="fas fa-folder me-1 opacity-50"></i> {{ $totalSlots }} {{ Str::plural('Folder', $totalSlots) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="btn btn-light rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; color: #6b7280; transition: transform 0.3s;" :style="expanded ? 'transform: rotate(180deg);' : ''">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Rack List (Collapsible) --}}
                    <div x-show="expanded"
                         x-collapse
                         style="display: none;"
                         class="border-top">
                        <div class="p-0">
                            <table class="table table-hover mb-0 align-middle" style="font-size: 0.9rem;">
                                <thead style="background-color: #f9fafb;">
                                    <tr>
                                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Rack Code</th>
                                        <th class="border-0 text-uppercase text-muted text-center" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px; width: 150px;">Folders</th>
                                        <th class="border-0 text-uppercase text-muted text-center" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px; width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cabinet->racks as $rack)
                                        <tr>
                                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 6px; height: 6px; border-radius: 50%; background-color: #10b981;"></div>
                                                    <span class="fw-semibold" style="color: #374151;">{{ $rack->rack_code }}</span>
                                                </div>
                                            </td>
                                            <td class="border-bottom-0 text-center" style="padding: 16px 24px;">
                                                <span class="badge rounded-pill" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 5px 12px; font-weight: 600; font-size: 0.8rem;">
                                                    <i class="fas fa-folder me-1" style="font-size: 0.6rem;"></i> {{ $rack->slots_count }}
                                                </span>
                                            </td>
                                            <td class="border-bottom-0 text-center" style="padding: 16px 24px;">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button"
                                                            class="btn btn-sm btn-light text-secondary hover-primary"
                                                            title="Edit Rack"
                                                            style="border-radius: 6px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;"
                                                            onmouseover="this.style.backgroundColor='rgba(221, 39, 13, 0.1)'; this.style.color='#dd270d !important'"
                                                            onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.color='#6c757d !important'"
                                                            @click="openEditModal({{ json_encode([
                                                                'id' => $rack->id,
                                                                'cabinet_name' => $cabinet->name,
                                                                'rack_code' => $rack->rack_code,
                                                                'updated_at' => $rack->updated_at->format('M d, Y h:i A')
                                                            ]) }})">
                                                        <i class="fas fa-pen" style="font-size: 0.8rem;"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm"
                                                            title="{{ $rack->slots_count > 0 ? 'Cannot delete: Slots exist' : 'Delete Rack' }}"
                                                            style="border-radius: 6px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; {{ $rack->slots_count > 0 ? 'background-color:#f3f4f6; color:#9ca3af; cursor:not-allowed;' : 'background-color:#fef2f2; color:#ef4444;' }}"
                                                            {{ $rack->slots_count > 0 ? 'disabled' : '' }}
                                                            @if($rack->slots_count == 0)
                                                                onmouseover="this.style.backgroundColor='#fee2e2'; this.style.transform='scale(1.05)'"
                                                                onmouseout="this.style.backgroundColor='#fef2f2'; this.style.transform='scale(1)'"
                                                                @click="openConfirmModal({{ $rack->id }}, '{{ $cabinet->name }} › {{ $rack->rack_code }}')"
                                                            @endif>
                                                        <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #9ca3af !important;">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-archive mb-3" style="font-size: 2.5rem; opacity: 0.2;"></i>
                        <h4 class="h5 fw-bold text-dark mb-1">No Cabinets Found</h4>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Start by adding a new rack to create a cabinet.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @include('cabinets.create')
        @include('cabinets.edit')
        @include('companies.confirm_modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cabinetManager', () => ({
                editUrl: '',
                editData: {
                    id: '',
                    cabinet_name: '',
                    rack_code: '',
                    updated_at: ''
                },

                confirmActionUrl: '',
                confirmMethod: 'DELETE',
                confirmTitle: '',
                confirmMessage: '',
                confirmButtonText: '',
                confirmTheme: '',
                confirmIcon: '',

                init() {
                    @if($errors->any() && old('_method') === 'PUT')
                        this.editUrl = '{{ url("settings/cabinets/racks") }}/{{ old("id") }}';
                        this.editData = {
                            id: '{{ old("id") }}',
                            cabinet_name: '{!! addslashes(old("cabinet_name")) !!}',
                            rack_code: '{!! addslashes(old("rack_code")) !!}',
                            updated_at: ''
                        };
                    @endif
                },

                openEditModal(rack) {
                    this.editData = { ...rack };
                    this.editUrl = `{{ url('settings/cabinets/racks') }}/${rack.id}`;
                    var modal = new bootstrap.Modal(document.getElementById('editRackModal'));
                    modal.show();
                },

                openConfirmModal(id, name) {
                    this.confirmActionUrl = `{{ url('settings/cabinets/racks') }}/${id}`;
                    this.confirmMethod = 'DELETE';
                    this.confirmTitle = 'Delete Rack';
                    this.confirmMessage = `Are you sure you want to permanently delete the rack <strong>${name}</strong>? This action cannot be undone.`;
                    this.confirmButtonText = 'Yes, Delete Rack';
                    this.confirmTheme = 'danger';
                    this.confirmIcon = 'fas fa-trash-alt';
                    
                    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    modal.show();
                }
            }));
        });
    </script>
</x-app-layout>
