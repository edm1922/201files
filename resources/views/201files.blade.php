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

        {{-- (2) Company Selector — top-right --}}
        <div class="div2">
            <label for="companySelect" class="toolbar-label mb-1">
                <i class="fas fa-building me-1"></i> Company
            </label>
            <select id="companySelect" name="company_id" class="form-select field-input w-100">
                <option value="">-- Choose Company --</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        {{ ($employee && $employee->company_id == $company->id) ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- (1) Search Bar ── --}}
        <div class="div1">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    id="employeeSearch"
                    class="search-input"
                    placeholder="Search employees... (or press Enter)"
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

        {{-- Hidden company_id that syncs with the toolbar dropdown --}}
        <input type="hidden" id="companyIdHidden" name="company_id" value="{{ old('company_id', $employee?->company_id) }}">

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

                    {{-- Documents tab --}}
                    <li class="nav-item" role="presentation">
                        <button class="file-tab" id="tab-documents"
                                data-bs-toggle="tab" data-bs-target="#panel-documents"
                                type="button" role="tab"
                                aria-controls="panel-documents" aria-selected="false">
                            <i class="fas fa-folder-open me-1"></i>Documents
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
                        <h6 class="panel-section-title">Employee Summary</h6>

                        <div class="emp-summary-grid">

                            <div class="emp-summary-card">
                                <span class="emp-summary-label">Full Name</span>
                                <span class="emp-summary-value">{{ $employee->full_name }}</span>
                            </div>

                            <div class="emp-summary-card">
                                <span class="emp-summary-label">Barcode ID</span>
                                <span class="emp-summary-value emp-summary-mono">
                                    {{ $employee->barcode_id ?? '—' }}
                                </span>
                            </div>

                            <div class="emp-summary-card">
                                <span class="emp-summary-label">System ID</span>
                                <span class="emp-summary-value emp-summary-mono">
                                    {{ $employee->system_id }}
                                </span>
                            </div>

                            <div class="emp-summary-card">
                                <span class="emp-summary-label">Folder Code</span>
                                <span class="emp-summary-value emp-summary-mono">
                                    {{ $employee->folder_code ?? '—' }}
                                </span>
                            </div>

                            <div class="emp-summary-card">
                                <span class="emp-summary-label">Status</span>
                                <span class="emp-summary-value">
                                    <span class="emp-status-badge emp-status-badge--{{ $employee->status }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </span>
                            </div>

                            <div class="emp-summary-card">
                                <span class="emp-summary-label">Current Company</span>
                                <span class="emp-summary-value">
                                    {{ $employee->company?->name ?? '— Not Assigned —' }}
                                </span>
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
                                   class="form-control field-input @error('first_name') is-invalid @enderror"
                                   placeholder="Enter First Name"
                                   value="{{ old('first_name', $employee?->first_name) }}">
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="middleNameInput">Middle Name</label>
                            <input type="text" id="middleNameInput" name="middle_name"
                                   class="form-control field-input @error('middle_name') is-invalid @enderror"
                                   placeholder="Enter Middle Name"
                                   value="{{ old('middle_name', $employee?->middle_name) }}">
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="lastNameInput">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="lastNameInput" name="last_name"
                                   class="form-control field-input @error('last_name') is-invalid @enderror"
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
                            <label class="form-label" for="folderCodeInput">Folder Code</label>
                            <input type="text" id="folderCodeInput" name="folder_code"
                                   class="form-control field-input"
                                   placeholder="Auto-generated (e.g. 201HR-0001)"
                                   value="{{ $employee?->folder_code }}" readonly>
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
                            <label class="form-label" for="documentLocationSelect">Document Location</label>
                            @php
                                $locRaw = old('document_location', $employee?->document_location ?? '');
                                $defaultLocs = ['A1','A2','A3','A4','A5','A6','A7','A8','A9','A10'];
                                $allLocs = $defaultLocs;
                                if ($locRaw && !in_array($locRaw, $defaultLocs)) {
                                    $allLocs[] = $locRaw;
                                }
                            @endphp
                            <select id="documentLocationSelect" name="document_location"
                                class="form-control basic-select field-input" {{-- Changed tagging-select to basic-select --}}
                                data-placeholder="- Choose -">
                                <option value=""></option>
                                @foreach($allLocs as $loc)
                                    <option value="{{ $loc }}" {{ $locRaw === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ══ DOCUMENTS TAB ══ --}}
                <div class="tab-pane fade" id="panel-documents"
                     role="tabpanel" aria-labelledby="tab-documents">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="panel-section-title mb-0">Documents</h6>
                        <button type="button" class="btn-doc-upload">
                            <i class="fas fa-upload me-1"></i>Upload Document
                        </button>
                    </div>

                    <div class="doc-table-wrapper">
                        <table class="doc-table" id="documentsTable">
                            <thead>
                                <tr>
                                    <th style="width:50px;">No.</th>
                                    <th style="width:200px;">Document Type</th>
                                    <th>File Name</th>
                                    <th style="width:120px;">Expiry Date</th>
                                    <th style="width:110px;">Remarks</th>
                                    <th style="width:140px;">Uploaded By</th>
                                    <th style="width:120px;">Uploaded On</th>
                                    <th style="width:80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentTypes as $i => $docType)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td class="doc-type-cell">{{ $docType->name }}</td>
                                    <td class="text-muted" style="font-size:0.8rem;">N/A</td>
                                    <td class="text-center text-muted" style="font-size:0.8rem;">
                                        {{ $docType->has_expiry ? 'N/A' : '—' }}
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <button type="button" class="btn-doc-action" title="View file">
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>{{-- end documents tab --}}

            </div>{{-- end tab-content --}}
        </div>{{-- end file-panel --}}
    </form>

    @push('scripts')
    <script>
        // Sync toolbar company dropdown → hidden form field
        document.getElementById('companySelect').addEventListener('change', function () {
            document.getElementById('companyIdHidden').value = this.value;
        });

        // When a search result is clicked, also update the company dropdown if the employee has a deployment
        const originalFill = window.fillEmployeeData;
        window.fillEmployeeData = function (emp) {
            // Navigate to the employee profile
            if (emp.id) {
                window.location.href = '/employees/' + emp.id;
            }
        };
    </script>
    @endpush

</x-app-layout>


