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
        $formAction = $isNew
            ? route('employees.store')
            : route('employees.update', $employee);
        $formMethod = $isNew ? 'POST' : 'PUT';
    @endphp

    <form id="employeeForm" action="{{ $formAction }}" method="POST" x-data="statusManager('{{ old('status', $employee?->status ?? '') }}')">
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
                        <button class="file-tab {{ ($isNew && Auth::user()->hasRole('admin', 'encoder')) ? '' : 'active' }}" id="tab-employee"
                                data-bs-toggle="tab" data-bs-target="#panel-employee"
                                type="button" role="tab"
                                aria-controls="panel-employee"
                                aria-selected="{{ ($isNew && Auth::user()->hasRole('admin', 'encoder')) ? 'false' : 'true' }}">
                            <i class="fas fa-id-card me-1"></i>Employee
                        </button>
                    </li>

                    {{-- Personal tab (Admin/Encoder only) --}}
                    @if(Auth::user()->hasRole('admin', 'encoder'))
                        <li class="nav-item" role="presentation">
                            <button class="file-tab {{ $isNew ? 'active' : '' }}" id="tab-personal"
                                    data-bs-toggle="tab" data-bs-target="#panel-personal"
                                    type="button" role="tab"
                                    aria-controls="panel-personal"
                                    aria-selected="{{ $isNew ? 'true' : 'false' }}">
                                <i class="fas fa-user me-1"></i>Personal
                            </button>
                        </li>
                    @endif
                </ul>

                <div class="file-panel__actions">
                    <button type="submit" class="btn-file-save">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                    <a href="{{ route('201files') }}" class="btn btn-secondary ms-2" style="border-radius:4px; font-weight:500;">
                        <i class="fas fa-times me-1"></i>Close
                    </a>
                </div>
            </div>

            {{-- Tab Content --}}
            <div class="tab-content file-panel__body" id="fileTabsContent">

                {{-- ══ EMPLOYEE TAB (display-only summary) ══ --}}
                <div class="tab-pane fade {{ ($isNew && Auth::user()->hasRole('admin', 'encoder')) ? '' : 'show active' }}" id="panel-employee"
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
                                        <span class="profile-field__label">Physical Location</span>
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
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-3"></i>
                            <p class="mb-0">No employee loaded. Search above or click <strong>New</strong> to create one.</p>
                        </div>
                    @endif
                </div>

                {{-- ══ PERSONAL TAB (editable form) (Admin/Encoder only) ══ --}}
                @if(Auth::user()->hasRole('admin', 'encoder'))
                    <div class="tab-pane fade {{ $isNew ? 'show active' : '' }}" id="panel-personal"
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
                                    data-placeholder="- Choose -">
                                    <option value=""></option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" 
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
                                    $numericPart = $lastFolderCode ? preg_replace('/[^0-9]/', '', $lastFolderCode) : '0000';
                                    $dynamicMaxLength = max(4, strlen($numericPart));
                                    
                                    $currentCodeValue = ($employee && $employee->folder) ? str_replace('CSC-HR-', '', $employee->folder->folder_code) : old('folder_code');
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #f8f9fa; border-color: #dee2e6; color: #6c757d; font-weight: 500; color: rgb(221, 39, 13);">CSC-HR-</span>
                                    @php
                                        $nextNumber = intval($numericPart) + 1;
                                        $nextCodeNumeric = str_pad($nextNumber, $dynamicMaxLength, '0', STR_PAD_LEFT);
                                        
                                        $value = $currentCodeValue;
                                        if (is_null($value) || $value === '') {
                                            $value = $nextCodeNumeric;
                                        }
                                    @endphp
                                    <input type="text" id="folderCodeInput" name="folder_code"
                                           class="form-control field-input @error('folder_code') is-invalid @enderror"
                                           placeholder="{{ $nextCodeNumeric }}"
                                           maxlength="{{ $dynamicMaxLength }}"
                                           value="{{ $value }}"
                                           readonly
                                           style="background-color: #e9ecef; cursor: not-allowed;">
                                    <button class="btn btn-outline-secondary px-2" type="button" id="clearFolderCode" title="Clear to auto-generate" style="border-color: #dee2e6; color: #6c757d;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                                    <small class="text-muted">
                                        Last number: <span class="fw-bold">{{ $lastFolderCode ?? 'None' }}</span>
                                    </small>
                                    
                                    @if($folders && $folders->count() > 0)
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="fw-bold" style="color: rgb(221, 39, 13);">Available Code:</small>
                                            <select id="availableCodeSelect" class="form-select form-select-sm py-0" style="width: auto; height: 24px; font-size: 0.75rem; color: rgb(221, 39, 13); border-color: rgb(221, 39, 13);">
                                                <option value="">- Select -</option>
                                                @foreach($folders as $folder)
                                                    @php $code = str_replace('CSC-HR-', '', $folder->folder_code); @endphp
                                                    <option value="{{ $code }}">{{ $code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                                @if($folders && $folders->count() > 0)
                                    <small class="mt-1 d-block" style="color: rgb(221, 39, 13); line-height: 1.2;">
                                        <strong>Note:</strong> Update the folder location based on the selected folder code.
                                    </small>
                                @endif
                                @error('folder_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="locationSelectForm">Folder Location</label>
                                <select id="locationSelectForm" name="folder_location_id" 
                                    class="form-control basic-select field-input @error('folder_location_id') is-invalid @enderror"
                                    data-placeholder="- Choose Location -">
                                    <option value=""></option>
                                    
                                    @if($employee?->folderLocation)
                                        <optgroup label="Current Assignment">
                                            <option value="{{ $employee->folderLocation->id }}" selected>
                                                {{ $employee->folderLocation->full_location }}
                                            </option>
                                        </optgroup>
                                    @endif

                                    @if(isset($locations) && count($locations) > 0)
                                        <optgroup label="Available Locations">
                                        @foreach($locations as $loc)
                                            @php
                                                $isFull = ($loc->employees_count ?? 0) >= ($loc->max_capacity ?? 500);
                                                $isCurrent = old('folder_location_id', $employee?->folder_location_id) == $loc->id;
                                                $displaySuffix = $isFull ? '<span class="text-full-limit">[FULL]</span>' : '[' . ($loc->employees_count ?? 0) . '/' . ($loc->max_capacity ?? 500) . ']';
                                            @endphp
                                            <option value="{{ $loc->id }}"
                                                {{ $isCurrent ? 'selected' : '' }}
                                                {{ ($isFull && !$isCurrent) ? 'disabled' : '' }}>
                                                {{ $loc->full_location }} {{ $displaySuffix }}
                                            </option>
                                        @endforeach
                                        </optgroup>
                                    @endif
                                </select>
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
    </form>
</x-app-layout>
