<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @endpush

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="alert-flash alert-flash--success">
            <i class="fas fa-check-circle me-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert-flash alert-flash--error">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ── Toolbar Grid ── --}}
    <div class="parent mb-3">

        {{-- (1) Search Bar ── --}}
        <div class="div1">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    id="employeeSearch"
                    class="search-input text-uppercase"
                    placeholder="Search employees..."
                    autocomplete="off"
                    value="{{ $employee ? $employee->last_name . ', ' . $employee->first_name : '' }}"
                >
                <div id="meiliResults" class="meili-results-container" style="display:none;"></div>
            </div>
        </div>
    </div>

    {{-- ── 201 File Tab Panel ── --}}

    @php
        $isNew      = $employee === null;
        $isEncoderOrAdmin = Auth::user()->hasRole('admin', 'encoder');
        $hasPageQuery = request()->has('page');
        $isEditMode = request()->has('edit');
        $initialTab = (($isNew || $isEditMode) && $isEncoderOrAdmin && !$hasPageQuery) ? 'personal' : 'employee';
        
        $formAction = $isNew ? route('employees.store') : route('employees.update', $employee);
        $formMethod = $isNew ? 'POST' : 'PUT';
    @endphp

    <form id="employeeForm" action="{{ $formAction }}" method="POST" 
          x-data="statusManager('{{ old('status', $employee?->status ?? '') }}', '{{ $initialTab }}')">
        @csrf
        @if(!$isNew)
            @method('PUT')
        @endif

        <div class="file-panel">

            {{-- Tab Bar + Save/New buttons --}}
            <div class="file-panel__tabbar">
                <ul class="nav file-tabs" id="fileTabs" role="tablist">

                    {{-- Employee (summary) tab — shown when a record is loaded --}}
                    <li class="nav-item" role="presentation">
                        <button class="file-tab {{ $initialTab === 'employee' ? 'active' : '' }}" id="tab-employee"
                                data-bs-toggle="tab" data-bs-target="#panel-employee"
                                type="button" role="tab"
                                @click="activeTab = 'employee'"
                                aria-controls="panel-employee"
                                aria-selected="{{ $initialTab === 'employee' ? 'true' : 'false' }}">
                            <i class="fas fa-id-card me-1"></i>Employee
                        </button>
                    </li>

                    {{-- Personal tab (Admin/Encoder only) --}}
                    @if($isEncoderOrAdmin)
                        <li class="nav-item" role="presentation">
                            <button class="file-tab {{ $initialTab === 'personal' ? 'active' : '' }}" id="tab-personal"
                                    data-bs-toggle="tab" data-bs-target="#panel-personal"
                                    type="button" role="tab"
                                    @click="activeTab = 'personal'"
                                    aria-controls="panel-personal"
                                    aria-selected="{{ $initialTab === 'personal' ? 'true' : 'false' }}">
                                <i class="fas fa-user me-1"></i>Personal
                            </button>
                        </li>
                    @endif
                </ul>

                <div class="file-panel__actions">
                    <button type="submit" class="btn-file-save" x-show="activeTab !== 'employee'">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                    @if($isEncoderOrAdmin)
                        <button type="button" class="btn btn-secondary ms-2" style="border-radius:4px; font-weight:500;" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                            <i class="fas fa-file-import me-1"></i>Import Excel
                        </button>
                    @endif
                    <a href="{{ route('201files') }}" id="close201Btn" class="btn btn-secondary ms-2" style="border-radius:4px; font-weight:500;">
                        <i class="fas fa-times me-1"></i>Close
                    </a>
                </div>
            </div>

            {{-- Tab Content --}}
            <div class="tab-content file-panel__body" id="fileTabsContent">

                {{-- ══ EMPLOYEE TAB (display-only summary) ══ --}}
                <div class="tab-pane fade {{ $initialTab === 'employee' ? 'show active' : '' }}" id="panel-employee"
                     role="tabpanel" aria-labelledby="tab-employee">

                    @if($employee)
                        <div class="profile-header">
                            @php
                                $initials = collect(explode(' ', $employee->full_name))
                                    ->map(fn($n) => mb_substr($n, 0, 1))
                                    ->take(2)
                                    ->join('');
                            @endphp
                            <div class="profile-avatar">{{ strtoupper($initials) }}</div>
                            <div class="profile-info">
                                <h2 class="text-uppercase">{{ $employee->full_name }}</h2>
                                <div class="profile-meta">
                                    <span class="text-gray-500 text-sm font-medium">Folder code:</span>
                                    <span class="profile-field__value profile-field__value--red profile-field__value--mono">
                                    {{ $employee->folder?->folder_code ?? '—' }}
                                    </span>
                                    <span class="emp-status-badge emp-status-badge--{{ $employee->status }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-grid">
                            {{-- IDENTIFICATION CARD --}}
                            <div class="profile-card">
                                <div class="profile-card__header">Identification</div>
                                <div class="profile-card__body">
                                    <div class="profile-field">
                                        <span class="profile-field__label">System No.</span>
                                        <span class="profile-field__value profile-field__value--mono">
                                            {{ $employee->system_id }}
                                        </span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-field__label">Barcode ID</span>
                                        <span class="profile-field__value profile-field__value--mono">
                                            {{ $employee->barcode_id ?? '—' }}
                                        </span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-field__label">Folder Location</span>
                                        <span class="profile-field__value profile-field__value--red profile-field__value--mono">
                                            {{ $employee->folderLocation?->full_location ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- ASSIGNMENT CARD --}}
                            <div class="profile-card">
                                <div class="profile-card__header">Assignment</div>
                                <div class="profile-card__body">
                                    <div class="profile-field">
                                        <span class="profile-field__label">Company</span>
                                        <span class="profile-field__value">
                                            {{ $employee->company?->name ?? '— Not Assigned —' }}
                                        </span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-field__label">Date Hired</span>
                                        <span class="profile-field__value">
                                            {{ $employee->date_hired ? $employee->date_hired->format('F d, Y') : '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- BANK INFORMATION CARD --}}
                            <div class="profile-card">
                                <div class="profile-card__header">Bank Information</div>
                                <div class="profile-card__body">
                                    <div class="profile-field">
                                        <span class="profile-field__label">ATM Status</span>
                                        <span class="profile-field__value atm-status--{{ str_replace('_', '-', $employee->atm_status ?: 'none') }}">
                                            @if($employee->atm_status === 'on_process')
                                                On Process
                                            @elseif($employee->atm_status === 'for_releasing')
                                                For Releasing
                                            @elseif($employee->atm_status === 'received')
                                                Received
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                    <div class="profile-field">
                                        <span class="profile-field__label">Bank Name</span>
                                        <span class="profile-field__value">
                                            {{ $employee->bankType?->name ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- LAST UPDATED LOG --}}
                        @if(isset($latestUpdate) && $latestUpdate)
                            <div class="mt-4 p-2 rounded-2 d-flex align-items-center shadow-sm" style="background-color: #f3f4f6; border-left: 5px solid #dc2626;">
                                <div class="ps-3 py-1">
                                    <span style="font-size: 0.82rem; color: #374151;">
                                        <span class="fw-bold text-danger text-uppercase">{{ $employee->full_name }}</span>'s data was last updated by 
                                        <span class="fw-bold text-danger text-uppercase">{{ $latestUpdate->user?->name ?: 'System' }}</span> on 
                                        <span class="fw-bold text-danger">{{ $latestUpdate->created_at->format('M d, Y') }}</span> at 
                                        <span class="fw-bold text-danger">{{ $latestUpdate->created_at->format('h:i A') }}</span>.
                                        <a href="javascript:void(0)" onclick="showUpdateHistory({{ $employee->id }})" class="text-primary fw-bold text-decoration-none ms-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                            SEE MORE <i class="fas fa-chevron-right ms-1" style="font-size: 0.65rem;"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-light py-3 border-0">
                                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center" style="font-size: 1rem;">
                                    <i class="fas fa-users me-2 text-danger"></i>Employee Directory
                                </h5>
                            </div>
                            @if(isset($employees) && $employees->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle" style="font-size: 0.9rem;">
                                        <thead style="background-color: #f9fafb;">
                                            <tr>
                                                <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Folder Code</th>
                                                <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Full Name</th>
                                                <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Company</th>
                                                <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Location</th>
                                                <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Status</th>
                                                <th class="border-0 text-uppercase text-muted text-center" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px; width: 150px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($employees as $emp)
                                                <tr>
                                                    <td class="border-bottom-0" style="padding: 16px 24px;">
                                                        <span class="fw-semibold" style="color: {{ config('brand.primary_color') }}; font-family: monospace;">
                                                            {{ $emp->folder?->folder_code ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="border-bottom-0 text-uppercase fw-semibold" style="padding: 16px 24px;">
                                                        {{ $emp->full_name }}
                                                    </td>
                                                    <td class="border-bottom-0" style="padding: 16px 24px;">
                                                        {{ $emp->company?->name ?? '—' }}
                                                    </td>
                                                    <td class="border-bottom-0 fw-semibold" style="padding: 16px 24px; color: {{ config('brand.primary_color') }}; font-family: monospace;">
                                                        {{ $emp->folderLocation?->full_location ?? '—' }}
                                                    </td>
                                                    <td class="border-bottom-0" style="padding: 16px 24px;">
                                                        <span class="emp-status-badge emp-status-badge--{{ $emp->status }}">
                                                            {{ ucfirst($emp->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="border-bottom-0 text-center" style="padding: 16px 24px;">
                                                        <button type="button" class="btn btn-sm"
                                                           title="View Profile"
                                                           style="border-radius: 6px; padding: 6px 12px; background-color: var(--company-primary-light); color: {{ config('brand.primary_color') }}; font-weight: 500; transition: all 0.2s;"
                                                           onmouseover="this.style.backgroundColor='{{ config('brand.primary_color') }}33'"
                                                           onmouseout="this.style.backgroundColor='var(--company-primary-light)'"
                                                           onclick="openEmployeeDetailModal({{ $emp->id }})">
                                                            <i class="fas fa-eye me-1"></i> View
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($employees->hasPages())
                                    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 0 0 12px 12px;">
                                        <div class="text-muted" style="font-size: 0.8rem;">
                                            Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}
                                        </div>
                                        <div>
                                            {{ $employees->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="card-body text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No active employees found.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ══ PERSONAL TAB (editable form) (Admin/Encoder only) ══ --}}
                @if(Auth::user()->hasRole('admin', 'encoder'))
                    <div class="tab-pane fade {{ $initialTab === 'personal' ? 'show active' : '' }}" id="panel-personal"
                         role="tabpanel" aria-labelledby="tab-personal">

                        <h6 class="panel-section-title">Personal Information</h6>
                        <div class="row g-3">

                            {{-- Row 1: Names + Suffix --}}
                            <div class="col-md-3">
                                <label class="form-label" for="firstNameInput">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="firstNameInput" name="first_name"
                                       class="form-control field-input text-uppercase @error('first_name') is-invalid @enderror"
                                       placeholder="Enter First Name"
                                       value="{{ old('first_name', $employee?->first_name) }}">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="middleNameInput">Middle Name</label>
                                <input type="text" id="middleNameInput" name="middle_name"
                                       class="form-control field-input text-uppercase @error('middle_name') is-invalid @enderror"
                                       placeholder="Enter Middle Name"
                                       value="{{ old('middle_name', $employee?->middle_name) }}">
                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="lastNameInput">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="lastNameInput" name="last_name"
                                       class="form-control field-input text-uppercase @error('last_name') is-invalid @enderror"
                                       placeholder="Enter Last Name"
                                       value="{{ old('last_name', $employee?->last_name) }}">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="suffixSelect">Suffix</label>
                                @php
                                    // Get existing value from old input or database
                                    $suffixRaw = old('suffix', $employee?->suffix ?? '');
                                    
                                    // Define standard suffixes
                                    $defaultSuffixes = ['JR.', 'SR.', 'II', 'III', 'IV', 'V'];
                                    $allSuffixes = $defaultSuffixes;

                                    // If the database has a suffix not in our list (e.g., "PhD"), 
                                    // add it to the options so it remains selected and visible.
                                    if ($suffixRaw && !in_array($suffixRaw, $defaultSuffixes)) {
                                        $allSuffixes[] = $suffixRaw;
                                    }
                                @endphp

                                <select id="suffixSelect" name="suffix"
                                    class="form-control basic-select field-input" 
                                    data-placeholder="- Choose Suffix -">
                                    <option value=""></option>
                                    @foreach($allSuffixes as $suffix)
                                        <option value="{{ $suffix }}" {{ $suffixRaw === $suffix ? 'selected' : '' }}>
                                            {{ $suffix }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Row 2: IDs --}}
                            <div class="col-md-4">
                                <label class="form-label" for="barcodeInput">Barcode ID</label>
                                <input type="text" id="barcodeInput" name="barcode_id"
                                       class="form-control field-input @error('barcode_id') is-invalid @enderror"
                                       placeholder="Enter Barcode ID"
                                       value="{{ old('barcode_id', $employee?->barcode_id) }}">
                                @error('barcode_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="systemIdInput">System ID <span class="text-danger">*</span></label>
                                <input type="text" id="systemIdInput" name="system_id"
                                       class="form-control field-input @error('system_id') is-invalid @enderror"
                                       placeholder="Enter System ID"
                                       value="{{ old('system_id', $employee?->system_id) }}">
                                @error('system_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="dateHiredInput">Date Hired</label>
                                <input type="date" id="dateHiredInput" name="date_hired"
                                       class="form-control field-input @error('date_hired') is-invalid @enderror"
                                       value="{{ old('date_hired', $employee?->date_hired?->format('Y-m-d')) }}">
                                @error('date_hired')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                    

                            <div class="col-md-4">
                                <label class="form-label" for="statusSelect">Status <span class="text-danger">*</span></label>
                                <select id="statusSelect" name="status"
                                        class="form-control basic-select field-input @error('status') is-invalid @enderror"
                                        data-placeholder="- Choose -">
                                    <option value=""></option>
                                    @foreach(['active' => 'Active', 'awol' => 'AWOL', 'resigned' => 'Resigned'] as $val => $label)
                                        @if($isNew && $val === 'resigned')
                                            @continue
                                        @endif
                                        <option value="{{ $val }}"
                                            {{ old('status', $employee?->status) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label" for="atmStatusSelect">ATM Status</label>
                                <select id="atmStatusSelect" name="atm_status"
                                        class="form-control basic-select field-input @error('atm_status') is-invalid @enderror"
                                        data-placeholder="- Choose ATM Status -">
                                    <option value=""></option>
                                    <option value="on_process" {{ old('atm_status', $employee?->atm_status) === 'on_process' ? 'selected' : '' }}>On Process</option>
                                    <option value="for_releasing" {{ old('atm_status', $employee?->atm_status) === 'for_releasing' ? 'selected' : '' }}>For Releasing</option>
                                    <option value="received" {{ old('atm_status', $employee?->atm_status) === 'received' ? 'selected' : '' }}>Received</option>
                                </select>
                                @error('atm_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label" for="bankTypeSelect">Bank Type</label>
                                <select id="bankTypeSelect" name="bank_type_id"
                                        class="form-control basic-select field-input @error('bank_type_id') is-invalid @enderror"
                                        data-placeholder="- Choose Bank Type -">
                                    <option value=""></option>
                                    @foreach($bankTypes as $bankType)
                                        <option value="{{ $bankType->id }}" {{ old('bank_type_id', $employee?->bank_type_id) == $bankType->id ? 'selected' : '' }}>
                                            {{ $bankType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-4">
                                <label class="form-label" for="companySelectForm">Company <span class="text-danger">*</span></label>
                                <select id="companySelectForm" name="company_id" 
                                    class="form-control basic-select field-input @error('company_id') is-invalid @enderror"
                                    data-placeholder="- Choose -"
                                    required>
                                    <option value=""></option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" 
                                            data-code="{{ $company->code }}"
                                            data-next-folder-code="{{ $companyNextFolderCodes[$company->id] ?? '' }}"
                                            {{ old('company_id', $employee?->company_id) == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <h6 class="panel-section-title mt-4">Document Location Information</h6>

                            <div class="col-md-4">
                                <label class="form-label" for="folderCodeInput">Folder Code</label>
                                @php
                                    $selectedCompanyId = old('company_id', $employee?->company_id ?? ($companies->first()?->id ?? null));
                                    $lastFolderCode = $companyLastFolderCodes[$selectedCompanyId] ?? 'None';
                                    $nextFolderCode = $companyNextFolderCodes[$selectedCompanyId] ?? '';
                                    
                                    $company = $companies->firstWhere('id', $selectedCompanyId);
                                    $prefix = $company ? config('brand.folder_prefix') . '-' . strtoupper($company->code) . '-' : config('brand.folder_prefix') . '-HR-';
                                    
                                    $numericPart = $lastFolderCode !== 'None' ? preg_replace('/[^0-9]/', '', $lastFolderCode) : '0000';
                                    $dynamicMaxLength = max(4, strlen($numericPart));
                                    
                                    $currentCodeValue = ($employee && $employee->folder) 
                                        ? str_replace($prefix, '', $employee->folder->folder_code) 
                                        : (old('folder_id') ? '' : str_replace($prefix, '', $nextFolderCode));
                                    
                                    // If user selected an available folder code (not next available)
                                    if (old('folder_id') && isset($availableFoldersByCompany[$selectedCompanyId])) {
                                        foreach($availableFoldersByCompany[$selectedCompanyId] as $f) {
                                            if ($f['id'] == old('folder_id')) {
                                                $currentCodeValue = str_replace($prefix, '', $f['folder_code']);
                                                break;
                                            }
                                        }
                                    }
                                    $hasAvailable = isset($availableFoldersByCompany[$selectedCompanyId]) && count($availableFoldersByCompany[$selectedCompanyId]) > 0;
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text" id="companyPrefixSpan" style="background-color: #f8f9fa; border-color: #dee2e6; color: rgb(221, 39, 13); font-weight: 500;">{{ $prefix }}</span>
                                    <input type="text" id="folderCodeInput" 
                                           class="form-control field-input @error('folder_id') is-invalid @enderror"
                                           placeholder="0001"
                                           maxlength="{{ $dynamicMaxLength }}"
                                           value="{{ $currentCodeValue }}"
                                           readonly
                                           style="background-color: #e9ecef; cursor: not-allowed;">
                                    <button class="btn btn-outline-secondary px-2" type="button" id="clearFolderCode" title="Clear to auto-generate" style="border-color: #dee2e6; color: #6c757d;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                                    <small class="text-muted">
                                        Last number: <span class="fw-bold" id="lastFolderCodeSpan">{{ $lastFolderCode }}</span>
                                    </small>
                                    
                                    <div id="availableCodeGroup" class="d-flex align-items-center gap-2" style="{{ $hasAvailable ? '' : 'display: none !important;' }}">
                                        <small class="fw-bold" style="color: rgb(221, 39, 13);">Available Code:</small>
                                        <select id="availableCodeSelect" class="form-select form-select-sm py-0" 
                                            style="width: auto; height: 24px; font-size: 0.75rem; color: rgb(221, 39, 13); border-color: rgb(221, 39, 13);"
                                            data-company-folders='@json($availableFoldersByCompany ?? [])'
                                            data-company-next-codes='@json($companyNextFolderCodes ?? [])'
                                            data-company-last-codes='@json($companyLastFolderCodes ?? [])'>
                                            <option value="">- Select -</option>
                                        </select>
                                    </div>
                                </div>
                                <small id="folderNote" class="mt-1 d-block" style="color: rgb(221, 39, 13); line-height: 1.2; {{ $hasAvailable ? '' : 'display: none !important;' }}">
                                    <strong>Note:</strong> Update the folder location based on the selected folder code.
                                </small>
                                
                                <input type="hidden" name="folder_id" id="folderIdHidden" value="{{ old('folder_id', $employee?->folder_id) }}">

                                @error('folder_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="locationSelectForm">Folder Location</label>
                                <select id="locationSelectForm" name="folder_location_id" 
                                    class="form-control basic-select field-input @error('folder_location_id') is-invalid @enderror"
                                    data-placeholder="- Choose Location -">
                                    <option value=""></option>

                                    @if(isset($locations) && count($locations) > 0)
                                        @foreach($locations as $loc)
                                            @php
                                                $isFull = ($loc->employees_count ?? 0) >= ($loc->max_capacity ?? 500);
                                                $isCurrent = old('folder_location_id', $employee?->folder_location_id) == $loc->id;
                                                $companyCode = $loc->company?->code ?? 'N/A';
                                                $statusLabel = $isFull ? '[FULL]' : '[' . ($loc->employees_count ?? 0) . '/' . ($loc->max_capacity ?? 500) . ']';
                                            @endphp
                                            <option value="{{ $loc->id }}"
                                                    data-company-id="{{ $loc->company_id }}"
                                                    data-initial-disabled="{{ ($isFull && !$isCurrent) ? '1' : '0' }}"
                                                    {{ $isCurrent ? 'selected' : '' }}
                                                    {{ ($isFull && !$isCurrent) ? 'disabled' : '' }}>
                                                {{ $companyCode }} - Row {{ $loc->row_name }} ({{ $loc->range }}) {{ $statusLabel }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-muted mt-1 d-block">Locations are filtered by selected company.</small>
                                @error('folder_location_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif
            </div>{{-- end tab-content --}}
        </div>{{-- end file-panel --}}
        @include('employees.partials.resigned_modal')
        @include('employees.partials.update_history_modal')
        @include('employees.partials.detail_modal')
    </form>

    {{-- ── Import Excel Modal ── --}}
    @if($isEncoderOrAdmin)
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                <form action="{{ route('employees.import-excel') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                        <h5 class="fw-bold mb-0" id="importExcelModalLabel" style="color: #111827; letter-spacing: -0.025em;">
                            <i class="fas fa-file-import me-2" style="color: {{ config('brand.primary_color') }};"></i>Import Excel
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <div id="importFormContent">
                            <div class="mb-3">
                                <label for="importFile" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                    Excel File <span class="text-danger">*</span>
                                </label>
                                <input type="file" id="importFile" name="file"
                                       class="form-control @error('file') is-invalid @enderror"
                                       accept=".xlsx,.xls" required>
                                <small class="text-muted">Only .xlsx and .xls files are accepted.</small>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="importCompany" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                    Company <span class="text-danger">*</span>
                                </label>
                                <select id="importCompany" name="company_id"
                                        class="form-control @error('company_id') is-invalid @enderror"
                                        required>
                                    <option value="">- Choose Company -</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info p-3 mb-0" role="alert" style="font-size: 0.85rem; border-radius: 8px;">
                                <i class="fas fa-info-circle me-1"></i>
                                The file must have a column labelled <strong>NAME</strong> (format: <em>Lastname, Firstname Middlename</em>) and optionally <strong>BARCODE</strong> and <strong>NUMBER</strong> (folder sequence number). All imported employees will be set as <strong>Active</strong>.
                            </div>
                        </div>

                        <div id="importLoading" class="d-none text-center py-4">
                            <div class="spinner-border text-danger mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="fw-semibold mb-1" style="color: #111827;">Uploading & Importing...</p>
                            <p class="text-muted small mb-0">Please wait, this may take a moment.</p>
                            <div class="mt-4">
                                <div class="placeholder-glow mb-2">
                                    <span class="placeholder col-12 rounded" style="height: 10px;"></span>
                                </div>
                                <div class="placeholder-glow mb-2">
                                    <span class="placeholder col-8 rounded" style="height: 10px;"></span>
                                </div>
                                <div class="placeholder-glow mb-2">
                                    <span class="placeholder col-10 rounded" style="height: 10px;"></span>
                                </div>
                                <div class="placeholder-glow mb-2">
                                    <span class="placeholder col-7 rounded" style="height: 10px;"></span>
                                </div>
                                <div class="placeholder-glow">
                                    <span class="placeholder col-11 rounded" style="height: 10px;"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-light w-100 m-0"
                                style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px; color: #4b5563; background-color: #f3f4f6; border: none;"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger w-100 m-0 d-inline-flex align-items-center justify-content-center gap-2"
                                style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px; border: none; background-color: {{ config('brand.primary_color') }};">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.getElementById('importExcelModal')?.addEventListener('submit', function (e) {
            const form  = e.target;
            const btn   = form.querySelector('button[type="submit"]');
            const modal = this;

            document.getElementById('importFormContent').classList.add('d-none');
            document.getElementById('importLoading').classList.remove('d-none');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Importing...';

            form.querySelectorAll('button[data-bs-dismiss="modal"]').forEach(el => el.disabled = true);

            modal.querySelector('.btn-close')?.classList.add('d-none');

            if (typeof bootstrap !== 'undefined') {
                const instance = bootstrap.Modal.getInstance(modal);
                if (instance) instance._config.keyboard = false;
            }
        });
    </script>
    @endpush
</x-app-layout>
