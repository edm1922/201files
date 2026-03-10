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
            <div class="tab-pane fade show active" id="panel-personal" role="tabpanel" aria-labelledby="tab-personal">
                <h6 class="panel-section-title">PERSONAL</h6>
                <div class="row g-3">
                    {{-- Row 1: Names and Suffix aligned --}}
                    <div class="col-md-3">
                        <label class="form-label">First Name</label>
                        <input type="text" id="firstNameInput" class="form-control field-input" placeholder="First Name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" id="middleNameInput" class="form-control field-input" placeholder="Middle Name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" id="lastNameInput" class="form-control field-input" placeholder="Last Name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="suffixInput">Suffix</label>
                        <input
                            type="text"
                            id="suffixInput"
                            class="form-control field-input"
                            list="suffixOptions"
                            placeholder="-- Select or type --"
                            autocomplete="off"
                        >
                        <datalist id="suffixOptions">
                            <option value="JR.">
                            <option value="SR.">
                            <option value="II">
                            <option value="III"> 
                        </datalist>
                    </div>

                    {{-- Row 2: Secondary Information --}}
                    <div class="col-md-4">
                        <label class="form-label" for="barcodeInput">Barcode</label>
                        <input type="text" id="barcodeInput" class="form-control field-input" placeholder="Barcode">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="systemIdInput">System ID</label>
                        <input type="text" id="systemIdInput" class="form-control field-input" placeholder="System ID">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="folderCodeInput">Folder Code</label>
                        <input type="text" id="folderCodeInput" class="form-control field-input" placeholder="Folder Code">
                    </div>

                    {{-- Row 3: Status and Location --}}
                    <!-- <div class="col-md-4">
                        <label class="form-label">Document Location</label>
                        <input type="text" class="form-control field-input" placeholder="Document Location">
                    </div> -->
                    <div class="col-md-4">
                        <label class="form-label" for="documentLocationInput">Document Location</label>
                        <input
                            type="text"
                            id="documentLocationInput"
                            class="form-control field-input"
                            list="documentLocationOptions"
                            placeholder="-- Select or type --"
                            autocomplete="off"
                        >
                        <datalist id="documentLocationOptions">
                            <option value="A1">
                            <option value="A2">
                            <option value="A3">
                            <option value="A4">
                            <option value="A5">
                            <option value="A6">
                            <option value="A7">
                            <option value="A8">
                            <option value="A9">
                            <option value="A10">
                        </datalist>
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

                {{-- Header row: title + upload button --}}
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="panel-section-title mb-0">Documents</h6>
                    <button class="btn-doc-upload">
                        <i class="fas fa-upload me-1"></i> Upload Document
                    </button>
                </div>


                {{-- Documents table --}}
                <div class="doc-table-wrapper">
                    <table class="doc-table" id="documentsTable">
                        <thead>
                            <tr>
                                <th style="width:60px;">No.</th>
                                <th style="width:160px;">Document Type</th>
                                <th>File Name</th>
                                <th style="width:130px;">Validity</th>
                                <th style="width:110px;">Remarks</th>
                                <th style="width:150px;">Uploaded by</th>
                                <th style="width:130px;">Uploaded on</th>
                                <th style="width:80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $documentTypes = [
                                    'SSS',
                                    'Pag-Ibig',
                                    'Phil-Health',
                                    'HMO',
                                    'Birth Certificate / Marriage Contract',
                                    'Personal Information Sheet',
                                ];
                            @endphp
                            @foreach($documentTypes as $i => $type)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="doc-type-cell">{{ $type }}</td>
                                <td class="text-muted" style="font-size:0.8rem;">N/A</td>
                                <td class="text-center text-muted" style="font-size:0.8rem;">N/A</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center">
                                    <button class="btn-doc-action" title="View file">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>


        </div>{{-- end tab-content --}}
    </div>{{-- end file-panel --}}

</x-app-layout>
