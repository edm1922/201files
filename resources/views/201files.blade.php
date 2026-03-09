<x-app-layout>

    {{-- ── Toolbar Grid ── --}}
    <div class="parent mb-3">

        {{-- (2) Company Selector — top-right (col 5, row 1) --}}
        <div class="div2">
            <label for="companySelect" class="toolbar-label mb-1">
                <i class="fas fa-building me-1"></i> Company
            </label>
            <select id="companySelect" class="form-select field-input w-100">
                <option value="" selected disabled>-- Choose Company --</option>
                <option value="gentuna">Gentuna</option>
                <option value="pg-ang">PG-Ang</option>
                <option value="7-eleven">7 Eleven</option>
                <option value="mandaue">Mandaue</option>
            </select>
        </div>

        {{-- (1) Search Bar — spans full width (row 2) --}}
        <div class="div1">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    id="employeeSearch"
                    class="search-input"
                    placeholder="Search employees..."
                    autocomplete="off"
                >
            </div>
        </div>

    </div>

    {{-- ── 201 File Tab Panel ── --}}
    <div class="file-panel">

        {{-- Tab Bar + Save/Close --}}
        <div class="file-panel__tabbar">
            <ul class="nav file-tabs" id="fileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="file-tab active" id="tab-personal"
                            data-bs-toggle="tab" data-bs-target="#panel-personal"
                            type="button" role="tab"
                            aria-controls="panel-personal" aria-selected="true">
                        Personal
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="file-tab" id="tab-documents"
                            data-bs-toggle="tab" data-bs-target="#panel-documents"
                            type="button" role="tab"
                            aria-controls="panel-documents" aria-selected="false">
                        Documents
                    </button>
                </li>
            </ul>
            <div class="file-panel__actions">
                <button class="btn-file-save"><i class="fas fa-save me-1"></i>Save</button>
                <button class="btn-file-close"><i class="fas fa-times me-1"></i>Close</button>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content file-panel__body" id="fileTabsContent">

            {{-- PERSONAL TAB --}}
            <div class="tab-pane fade show active" id="panel-personal"
                 role="tabpanel" aria-labelledby="tab-personal">
                <h6 class="panel-section-title">PERSONAL</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control field-input" placeholder="Code">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control field-input" placeholder="First Name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control field-input" placeholder="Middle Name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control field-input" placeholder="Last Name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Barcode</label>
                        <input type="text" class="form-control field-input" placeholder="Barcode">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">System ID</label>
                        <input type="text" class="form-control field-input" placeholder="System ID">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Document Location</label>
                        <input type="text" class="form-control field-input" placeholder="Document Location">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="statusInput">Status</label>
                        <input
                            type="text"
                            id="statusInput"
                            class="form-control field-input"
                            list="statusOptions"
                            placeholder="-- Select or type --"
                            autocomplete="off"
                        >
                        <datalist id="statusOptions">
                            <option value="Active">
                            <option value="Resigned">
                            <option value="Awol">
                        </datalist>
                    </div>

                </div>
            </div>

            {{-- DOCUMENTS TAB --}}
            <div class="tab-pane fade" id="panel-documents"
                 role="tabpanel" aria-labelledby="tab-documents">
                <h6 class="panel-section-title">DOCUMENTS</h6>
                <p class="text-muted">Document records will appear here.</p>
            </div>

        </div>{{-- end tab-content --}}
    </div>{{-- end file-panel --}}

</x-app-layout>
