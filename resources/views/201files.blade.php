<x-app-layout>
    <div class="parent">
        {{-- (1) Search Bar - spans full width below --}}
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
    <div class="file-panel mt-3">

        {{-- Tab Bar + Save/Close --}}
        <div class="file-panel__tabbar">
            <ul class="nav file-tabs" id="fileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="file-tab" id="tab-personal" data-bs-toggle="tab"
                            data-bs-target="#panel-personal" type="button" role="tab">Personal</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="file-tab" id="tab-documents" data-bs-toggle="tab"
                            data-bs-target="#panel-documents" type="button" role="tab">Documents</button>
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
            <div class="tab-pane fade" id="panel-personal" role="tabpanel">
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
                        <label class="form-label">BARCODE</label>
                        <input type="text" class="form-control field-input" placeholder="BARCODE">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">System ID</label>
                        <input type="text" class="form-control field-input" placeholder="System ID">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Document location</label>
                        <input type="text" class="form-control field-input" placeholder="Document location">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select field-input">
                            <option value="">-- Select --</option>
                            <option>Active</option>
                            <option>Resigned</option>
                            <option>Awol</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>{{-- end tab-content --}}
    </div>{{-- end file-panel --}}
</x-app-layout>
