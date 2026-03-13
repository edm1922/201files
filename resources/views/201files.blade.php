<x-app-layout>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="alert-flash alert-flash--success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert-flash alert-flash--error">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
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
                <div id="milliResults" class="milli-results-container" style="display:none;"></div>
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

    <form id="employeeForm" action="{{ $formAction }}" method="POST">
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
                        <button class="file-tab {{ $isNew ? '' : 'active' }}" id="tab-employee"
                                data-bs-toggle="tab" data-bs-target="#panel-employee"
                                type="button" role="tab"
                                aria-controls="panel-employee"
                                aria-selected="{{ $isNew ? 'false' : 'true' }}">
                            <i class="fas fa-id-card me-1"></i>Employee
                        </button>
                    </li>

                    {{-- Personal tab --}}
                    <li class="nav-item" role="presentation">
                        <button class="file-tab {{ $isNew ? 'active' : '' }}" id="tab-personal"
                                data-bs-toggle="tab" data-bs-target="#panel-personal"
                                type="button" role="tab"
                                aria-controls="panel-personal"
                                aria-selected="{{ $isNew ? 'true' : 'false' }}">
                            <i class="fas fa-user me-1"></i>Personal
                        </button>
                    </li>
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
                <div class="tab-pane fade {{ $isNew ? '' : 'show active' }}" id="panel-employee"
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
                                    {{ $employee->folder_code ?? '—' }}
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
                                        <span class="profile-field__label">Document Location</span>
                                        <span class="profile-field__value profile-field__value--red profile-field__value--mono">
                                            {{ $employee->slot?->full_location ?? '—' }}
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
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-3"></i>
                            <p class="mb-0">No employee loaded. Search above or click <strong>New</strong> to create one.</p>
                        </div>
                    @endif
                </div>

                {{-- ══ PERSONAL TAB (editable form) ══ --}}
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
                            <label class="form-label">Folder Code</label>
                            <input type="text"
                                   class="form-control field-input"
                                   placeholder="Assigned via Slot"
                                   value="{{ $employee?->slot?->folder_code }}" readonly
                                   style="background-color: transparent; border-color: transparent; font-family: 'Courier New', monospace; font-weight: bold; color: #dd270d !important;">
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

                        <div class="col-md-4">
                            <label class="form-label" for="locationSelectForm">Folder Slot</label>
                            <select id="locationSelectForm" name="slot_id" 
                                class="form-control basic-select field-input @error('slot_id') is-invalid @enderror"
                                data-placeholder="- Choose Slot -">
                                <option value=""></option>
                                
                                {{-- If employee already has a slot, show it first so it's not lost on edit --}}
                                @if($employee?->slot)
                                    <optgroup label="Current Assignment">
                                        <option value="{{ $employee->slot->id }}" selected>
                                            {{ $employee->slot->full_location }}
                                        </option>
                                    </optgroup>
                                @endif

                                @if(isset($slots) && count($slots) > 0)
                                    <optgroup label="Available Slots">
                                    @foreach($slots as $slot)
                                        <option value="{{ $slot->id }}" 
                                            {{ old('slot_id', $employee?->slot_id) == $slot->id ? 'selected' : '' }}>
                                            {{ $slot->full_location }}
                                        </option>
                                    @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            @error('slot_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>{{-- end tab-content --}}
        </div>{{-- end file-panel --}}
    </form>

    @push('scripts')
    <script>
        // Sync toolbar company dropdown → hidden form field
        document.getElementById('companySelect').addEventListener('change', function () {
            document.getElementById('companyIdHidden').value = this.value;
        });

    </script>
    @endpush

</x-app-layout>


