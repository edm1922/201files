<x-app-layout>
    @php
        $activeTab = request('tab', 'file-locations');
        if (! in_array($activeTab, ['file-locations', 'document-locations'], true)) {
            $activeTab = 'file-locations';
        }
    @endphp

    <div x-data="locationManager('{{ $activeTab }}', @js($nextRangeStartByCompany ?? []))" class="px-4 py-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h2 class="h4 mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-folder-tree text-muted"></i> Folder Locations
                </h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage physical locations for 201 files and department documents.</p>
            </div>

            <div>
            <template x-if="activeTab === 'file-locations'">
                <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createFileLocationModal">
                    <i class="fas fa-plus"></i> Add Location
                </button>
            </template>

            <template x-if="activeTab === 'document-locations'">
                <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createDocumentLocationModal">
                    <i class="fas fa-plus"></i> Add Location
                </button>
            </template>
            </div>
        </div>

        <div class="mb-4 border-bottom">
            <ul class="nav nav-tabs border-0" style="margin-bottom: -1px;">
                <li class="nav-item">
                    <button type="button" class="nav-link border-0 text-decoration-none fw-semibold transition-colors duration-200"
                        @click="setTab('file-locations')"
                        style="font-size: 0.95rem; padding: 10px 20px;"
                        :style="`border-bottom: 3px solid ${activeTab === 'file-locations' ? '{{ config('brand.primary_color') }}' : 'transparent'} !important; color: ${activeTab === 'file-locations' ? '{{ config('brand.primary_color') }}' : '#64748b'};`">
                        <i class="fas fa-users me-2"></i>201 File Locations
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link border-0 text-decoration-none fw-semibold transition-colors duration-200"
                        @click="setTab('document-locations')"
                        style="font-size: 0.95rem; padding: 10px 20px;"
                        :style="`border-bottom: 3px solid ${activeTab === 'document-locations' ? '{{ config('brand.primary_color') }}' : 'transparent'} !important; color: ${activeTab === 'document-locations' ? '{{ config('brand.primary_color') }}' : '#64748b'};`">
                        <i class="fas fa-file-contract me-2"></i>Document Locations
                    </button>
                </li>
            </ul>
        </div>

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
        @if ($errors->any())
            <div class="alert-flash alert-flash--error">
                <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        {{-- Filter Section --}}
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: #fff;" x-show="activeTab === 'file-locations'" x-cloak>
            <div class="card-body py-2 px-3">
                <form action="{{ route('settings.folder-locations.index') }}" method="GET" class="d-flex align-items-center gap-3 flex-wrap">
                    <input type="hidden" name="tab" value="file-locations">
                    
                    <div class="d-flex align-items-center gap-2 me-2">
                        <div class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 6px; background-color: var(--company-primary-light); color: var(--company-primary);">
                            <i class="fas fa-filter" style="font-size: 0.75rem;"></i>
                        </div>
                        <span class="toolbar-label" style="font-size: 0.7rem;">Filter by Company</span>
                    </div>

                    <div style="min-width: 350px;">
                        <select name="company_id" id="companyFilter" class="form-select basic-select" data-placeholder="All Companies" onchange="this.form.submit()">
                            <option value=""></option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }} ({{ $company->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request('company_id'))
                        <a href="{{ route('settings.folder-locations.index', ['tab' => 'file-locations']) }}" 
                           class="btn btn-sm btn-light text-muted d-flex align-items-center gap-2 border-0" 
                           style="background: #f3f4f6; border-radius: 6px; padding: 6px 14px; font-size: 0.8rem; font-weight: 600;">
                            <i class="fas fa-undo-alt"></i> Reset Filter
                        </a>
                    @endif
                </form>
            </div>
        </div>


        <!-- // Script to handle select2 clear event and submit the form -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle Select2 clear event to submit the form
                $('#companyFilter').on('select2:clear', function() {
                    setTimeout(() => {
                        this.form.submit();
                    }, 50);
                });
            });
        </script>

        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;" x-show="activeTab === 'file-locations'" x-cloak>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background-color: #f9fafb;">
                        <tr>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em;">Company</th>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em;">Location</th>
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

                                if ($percent >= 100) {
                                    $statusColor = '#dc2626';
                                } elseif ($percent >= 80) {
                                    $statusColor = '#f59e0b';
                                } else {
                                    $statusColor = '#10b981';
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark">{{ $location->company?->name ?? 'Unassigned' }}</div>
                                    <small class="text-muted">{{ $location->company?->code ?? 'N/A' }}</small>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, {{ config('brand.primary_color') }} 0%, var(--company-primary-hover) 100%); color: #fff;">
                                            <i class="fas fa-layer-group" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <span class="fw-bold" style="font-size: 1rem; color: #111827;">{{ $location->row_name }}</span>
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
                                            <span class="fw-bold" style="font-size: 0.9rem; color: {{ $statusColor }};">{{ $occupancy }}</span>
                                            <span class="text-muted mx-1" style="font-size: 0.8rem;">/</span>
                                            <span class="text-muted" style="font-size: 0.8rem;">{{ $capacity }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="d-inline-flex align-items-center gap-1">
                                    @php
                                        $fileLocationPayload = json_encode([
                                            'id' => $location->id,
                                            'company_id' => $location->company_id,
                                            'row_name' => $location->row_name,
                                            'range_start' => $location->range_start,
                                            'range_end' => $location->range_end,
                                            'max_capacity' => $location->max_capacity,
                                        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                                    @endphp
                                    <button type="button" class="btn btn-sm btn-outline-secondary border-0 me-1"
                                        @click='openFileEditModal({!! $fileLocationPayload !!})'
                                        style="padding: 6px 10px; border-radius: 6px; transition: all 0.2s;"
                                        title="Edit location">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                        @if($occupancy > 0) disabled title="Cannot delete location with assigned employees" @else @click="openFileConfirmModal('{{ $location->id }}', '{{ $location->row_name }}')" @endif
                                        style="padding: 6px 10px; border-radius: 6px; transition: all 0.2s;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-box-open mb-3 d-block" style="font-size: 3rem; opacity: 0.15; color: {{ config('brand.primary_color') }};"></i>
                                    <h5 class="text-muted fw-bold">No 201 File Locations</h5>
                                    <p class="text-muted mb-0">Click "Add Location" to start organizing employee folders.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="createFileLocationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" action="{{ route('settings.folder-locations.store-row') }}">
                        @csrf
                        <input type="hidden" name="_action" value="create_file_location">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Add 201 File Location</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Company</label>
                                <select class="form-select field-input bg-light" name="company_id" x-model="newFileCompanyId" @change="applySuggestedRangeStart()" required>
                                    <option value="">Select company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }} ({{ $company->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Location Code</label>
                                <input type="text" class="form-control field-input bg-light text-uppercase" name="row_name" value="{{ old('row_name') }}" maxlength="20" required placeholder="e.g. A or A1">
                                <small class="text-muted">Use short canonical code for physical bins/rows.</small>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Range Start</label>
                                    <input type="number" class="form-control field-input bg-light" name="range_start" x-model="newFileRangeStart" @input="newFileRangeStartTouched = true" value="{{ old('range_start') }}" min="1" required>
                                    <small class="text-muted" x-show="newSuggestedRangeStart > 0" x-cloak>
                                        Suggested: <span x-text="newSuggestedRangeStart"></span>
                                    </small>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Range End</label>
                                    <input type="number" class="form-control field-input bg-light" name="range_end" value="{{ old('range_end') }}" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger fw-semibold">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editFileLocationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" :action="fileEditActionUrl">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" x-model="fileEditId">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Edit 201 File Location</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Company</label>
                                <select class="form-select field-input bg-light" name="company_id" x-model="fileEditCompanyId" required>
                                    <option value="">Select company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Location Code</label>
                                <input type="text" class="form-control field-input bg-light" name="row_name" x-model="fileEditRowName" maxlength="20" required>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Range Start</label>
                                    <input type="number" class="form-control field-input bg-light" name="range_start" x-model="fileEditRangeStart" min="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Range End</label>
                                    <input type="number" class="form-control field-input bg-light" name="range_end" x-model="fileEditRangeEnd" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger fw-semibold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;" x-show="activeTab === 'document-locations'" x-cloak>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background-color: #f9fafb;">
                        <tr>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em;">Location</th>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em; width: 110px; white-space: nowrap;">Documents</th>
                            <th class="px-4 py-3 text-muted fw-bold text-center" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.025em; width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentLocations as $location)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark">{{ $location->name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2" style="font-size: 0.85rem;">
                                        {{ $location->documents_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary border-0 me-1"
                                        @click="openDocumentEditModal('{{ $location->id }}', '{{ addslashes($location->name) }}')"
                                        style="padding: 6px 10px; border-radius: 6px; transition: all 0.2s;"
                                        title="Edit location name">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                        @if($location->documents_count > 0) disabled title="Cannot delete location with assigned documents" @else @click="openDocumentConfirmModal('{{ $location->id }}', '{{ addslashes($location->name) }}')" @endif
                                        style="padding: 6px 10px; border-radius: 6px; transition: all 0.2s;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <i class="fas fa-map-marker-alt mb-3 d-block" style="font-size: 3rem; opacity: 0.15; color: {{ config('brand.primary_color') }};"></i>
                                    <h5 class="text-muted fw-bold">No Document Locations</h5>
                                    <p class="text-muted mb-0">Add your first location label for department documents.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="confirmFileLocationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fee2e2; color: #dc2626; border-radius: 50%;">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Delete Location?</h4>
                        <p class="text-muted mb-4" x-html="fileConfirmMessage"></p>

                        <div class="d-grid gap-2">
                            <form :action="fileConfirmActionUrl" method="POST">
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

        <div class="modal fade" id="createDocumentLocationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" action="{{ route('settings.document-locations.store') }}">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Add Document Location</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <label for="document_location_name" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Location Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="document_location_name" name="name" class="form-control field-input bg-light" maxlength="120" required placeholder="e.g. Finance Cabinet 2" value="{{ old('name') }}">
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger fw-semibold">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="confirmDocumentLocationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fee2e2; color: #dc2626; border-radius: 50%;">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Delete Location?</h4>
                        <p class="text-muted mb-4" x-html="documentConfirmMessage"></p>

                        <div class="d-grid gap-2">
                            <form :action="documentConfirmActionUrl" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold" style="border-radius: 10px;">
                                    Yes, Delete
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

        <div class="modal fade" id="editDocumentLocationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <form method="POST" :action="documentEditActionUrl">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" x-model="documentEditId">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Edit Document Location</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <label for="edit_document_location_name" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Location Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="edit_document_location_name" name="name" class="form-control field-input bg-light" maxlength="120" required x-model="documentEditName">
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger fw-semibold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationManager', (initialTab, nextRangeStartByCompany = {}) => ({
                activeTab: initialTab,
                nextRangeStartByCompany,
                fileConfirmActionUrl: '',
                fileConfirmMessage: '',
                fileEditActionUrl: '',
                fileEditId: '',
                fileEditCompanyId: '',
                fileEditRowName: '',
                fileEditRangeStart: '',
                fileEditRangeEnd: '',
                newFileCompanyId: '{{ old('company_id') }}',
                newFileRangeStart: '{{ old('range_start') }}',
                newFileRangeStartTouched: {{ old('range_start') ? 'true' : 'false' }},
                newSuggestedRangeStart: 0,
                documentConfirmActionUrl: '',
                documentConfirmMessage: '',
                documentEditId: '{{ old('id') }}',
                documentEditActionUrl: '{{ old('id') ? url("settings/document-locations") . '/' . old('id') : '' }}',
                documentEditName: '{!! addslashes(old('name', '')) !!}',

                init() {
                    this.applySuggestedRangeStart();

                    @if($errors->any() && old('_action') === 'create_file_location' && request('tab', 'file-locations') === 'file-locations')
                        this.$nextTick(() => {
                            var modal = new bootstrap.Modal(document.getElementById('createFileLocationModal'));
                            modal.show();
                        });
                    @endif

                    @if($errors->any() && old('_method') === 'PUT' && request('tab', 'file-locations') === 'file-locations')
                        this.fileEditActionUrl = '{{ old('id') ? url("settings/folder-locations") . '/' . old('id') : '' }}';
                        this.fileEditId = '{{ old('id') }}';
                        this.fileEditCompanyId = '{{ old('company_id') }}';
                        this.fileEditRowName = '{!! addslashes(old('row_name', '')) !!}';
                        this.fileEditRangeStart = '{{ old('range_start') }}';
                        this.fileEditRangeEnd = '{{ old('range_end') }}';

                        this.$nextTick(() => {
                            var modal = new bootstrap.Modal(document.getElementById('editFileLocationModal'));
                            modal.show();
                        });
                    @endif
                },

                setTab(tab) {
                    this.activeTab = tab;
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.replaceState({}, '', url.toString());
                },

                applySuggestedRangeStart() {
                    const companyId = String(this.newFileCompanyId || '');
                    const suggestedRaw = companyId ? this.nextRangeStartByCompany[companyId] : null;
                    const suggested = Number.isFinite(Number(suggestedRaw)) ? Number(suggestedRaw) : 1;
                    this.newSuggestedRangeStart = suggested > 0 ? suggested : 1;

                    if (!this.newFileRangeStartTouched || !String(this.newFileRangeStart || '').trim()) {
                        this.newFileRangeStart = String(this.newSuggestedRangeStart);
                        this.newFileRangeStartTouched = false;
                    }
                },

                openFileConfirmModal(id, rowName) {
                    this.fileConfirmActionUrl = `{{ url('settings/folder-locations') }}/${id}`;
                    this.fileConfirmMessage = `Are you sure you want to delete <strong>${rowName}</strong>? This action cannot be undone.`;

                    var modal = new bootstrap.Modal(document.getElementById('confirmFileLocationModal'));
                    modal.show();
                },

                openFileEditModal(location) {
                    this.fileEditActionUrl = `{{ url('settings/folder-locations') }}/${location.id}`;
                    this.fileEditId = String(location.id ?? '');
                    this.fileEditCompanyId = String(location.company_id ?? '');
                    this.fileEditRowName = location.row_name ?? '';
                    this.fileEditRangeStart = location.range_start ?? '';
                    this.fileEditRangeEnd = location.range_end ?? '';

                    var modal = new bootstrap.Modal(document.getElementById('editFileLocationModal'));
                    modal.show();
                },

                openDocumentConfirmModal(id, name) {
                    this.documentConfirmActionUrl = `{{ url('settings/document-locations') }}/${id}`;
                    this.documentConfirmMessage = `Are you sure you want to delete <strong>${name}</strong>? This action cannot be undone.`;

                    var modal = new bootstrap.Modal(document.getElementById('confirmDocumentLocationModal'));
                    modal.show();
                },

                openDocumentEditModal(id, name) {
                    this.documentEditId = id;
                    this.documentEditActionUrl = `{{ url('settings/document-locations') }}/${id}`;
                    this.documentEditName = name;

                    var modal = new bootstrap.Modal(document.getElementById('editDocumentLocationModal'));
                    modal.show();
                }
            }));
        });

        @if($errors->any() && old('_method') === 'PUT' && request('tab') === 'document-locations')
            document.addEventListener('DOMContentLoaded', function () {
                var modal = new bootstrap.Modal(document.getElementById('editDocumentLocationModal'));
                modal.show();
            });
        @endif
    </script>

    @push('styles')
        <style>
            .transition-colors {
                transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            }

            .duration-200 {
                transition-duration: 200ms;
            }

            .nav-tabs .nav-link:hover {
                color: {{ config('brand.primary_color') }} !important;
                background-color: transparent !important;
                border-color: transparent !important;
                border-bottom: 3px solid var(--company-primary-border) !important;
            }
        </style>
    @endpush
</x-app-layout>
