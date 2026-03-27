<x-app-layout>
    @php
        $selectedDepartment = $departments->firstWhere('id', $selectedDepartmentId);
        $rootFolders = $foldersByParent->get(0, collect());
    @endphp

    <div class="animate-fade-in stagger-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold text-dark">Department Documents</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Browse folders first, then upload into the active folder context.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId ?: null, 'document_folder_id' => $currentFolderId ?: null]) }}" class="btn btn-action-round" title="Refresh Explorer">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-flash alert-flash--success mb-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-flash alert-flash--error mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        @if($departments->isEmpty())
            <div class="card doc-list-card">
                <div class="card-body p-4 text-center text-muted">
                    <i class="fas fa-folder-open fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0">No accessible departments found for your account.</p>
                </div>
            </div>
        @else
            <div class="explorer-grid mb-4">
                <div class="card doc-list-card explorer-sidebar">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('department-documents.index') }}" class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Department</label>
                            <select name="department_id" class="form-select field-input" onchange="this.form.submit()" required>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ (int) $department->id === (int) $selectedDepartmentId ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">Folders</h6>
                            @if($selectedDepartmentId)
                                <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId]) }}" class="text-decoration-none small">Root</a>
                            @endif
                        </div>

                        <div class="folder-tree mb-3">
                            @forelse($rootFolders as $folder)
                                @include('department-documents.partials.folder-tree-node', [
                                    'folder' => $folder,
                                    'foldersByParent' => $foldersByParent,
                                    'selectedDepartmentId' => $selectedDepartmentId,
                                    'currentFolderId' => $currentFolderId,
                                    'depth' => 0,
                                ])
                            @empty
                                <div class="text-muted small py-2">No folders yet.</div>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('department-documents.folders.store') }}" class="border-top pt-3">
                            @csrf
                            <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                            @if($currentFolderId)
                                <input type="hidden" name="parent_id" value="{{ $currentFolderId }}">
                            @endif

                            <label class="form-label small fw-bold text-uppercase text-muted mb-1">New Folder {{ $currentFolderId ? '(inside current)' : '(root)' }}</label>
                            <input type="text" name="name" class="form-control field-input mb-2" placeholder="Folder name" required>
                            <button type="submit" class="btn btn-brand btn-sm w-100">
                                <i class="fas fa-folder-plus me-1"></i>Create Folder
                            </button>
                        </form>
                    </div>
                </div>

                <div class="explorer-main d-flex flex-column gap-4">
                    <div class="card doc-list-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1" style="font-size: 1rem; color: #1e293b;">
                                        <i class="fas fa-compass me-2 text-primary"></i>Current Location
                                    </h5>
                                    <div class="text-muted small">
                                        Department: <strong>{{ $selectedDepartment?->name ?? '—' }}</strong>
                                    </div>
                                </div>

                                <form action="{{ route('department-documents.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                                    @if($currentFolderId)
                                        <input type="hidden" name="document_folder_id" value="{{ $currentFolderId }}">
                                    @endif
                                    <div class="search-wrapper" style="width: 240px;">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" name="search" class="search-input py-1" placeholder="Search files..." value="{{ request('search') }}">
                                    </div>
                                    <button type="submit" class="btn btn-brand btn-sm px-3">Filter</button>
                                </form>
                            </div>

                            <div class="explorer-breadcrumb">
                                <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId]) }}">Root</a>
                                @foreach($folderBreadcrumbs as $crumb)
                                    <i class="fas fa-chevron-right mx-2 text-muted" style="font-size: 0.7rem;"></i>
                                    <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId, 'document_folder_id' => $crumb->id]) }}"
                                       class="{{ (int) $crumb->id === (int) $currentFolderId ? 'active' : '' }}">
                                        {{ $crumb->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card doc-list-card">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4" style="font-size: 1rem; color: #1e293b;">
                                <i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Upload to Current Folder
                            </h5>

                            <form method="POST" action="{{ route('department-documents.store') }}" enctype="multipart/form-data" id="upload-form"
                                  x-data="{ dragging: false, files: [] }">
                                @csrf

                                <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                                @if($currentFolderId)
                                    <input type="hidden" name="document_folder_id" value="{{ $currentFolderId }}">
                                @endif

                                <div class="row g-4">
                                    <div class="col-lg-7">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Department</label>
                                                <input type="text" class="form-control field-input" value="{{ $selectedDepartment?->name ?? 'N/A' }}" readonly>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Current Folder</label>
                                                <input type="text" class="form-control field-input" value="{{ $currentFolder?->name ?? 'Root (No virtual folder)' }}" readonly>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Document Type</label>
                                                <select name="document_type_id" class="form-select field-input" required>
                                                    <option value="">Select type</option>
                                                    @foreach($documentTypes as $type)
                                                        <option value="{{ $type->id }}" {{ request('document_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Folder Location (Physical)</label>
                                                <select name="folder_location_id" class="form-select field-input" required>
                                                    <option value="">Select location</option>
                                                    @foreach($folderLocations as $location)
                                                        <option value="{{ $location->id }}">{{ $location->display_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Upload Mode</label>
                                                <select name="upload_mode" class="form-select field-input" required>
                                                    <option value="standard" selected>Standard (Keep original)</option>
                                                    <option value="scan_packet">Scan Packet (Merge to PDF)</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Date Received</label>
                                                <input type="date" name="date_received" class="form-control field-input" value="{{ date('Y-m-d') }}" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Expiry Date</label>
                                                <input type="date" name="expiry_date" class="form-control field-input">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-5">
                                        <label class="form-label small fw-bold text-uppercase text-muted">Files to Upload</label>
                                        <div class="upload-zone"
                                             :class="{ 'upload-zone--dragover': dragging }"
                                             @dragover.prevent="dragging = true"
                                             @dragleave.prevent="dragging = false"
                                             @drop.prevent="dragging = false; files = Array.from($event.dataTransfer.files)">

                                            <input type="file" name="files[]" multiple required
                                                   @change="files = Array.from($event.target.files)">

                                            <div class="upload-zone__content">
                                                <i class="fas fa-file-export upload-zone__icon"></i>
                                                <div class="upload-zone__text" x-show="files.length === 0">Drag and drop files here or click to browse</div>
                                                <div class="upload-zone__text text-primary fw-bold" x-show="files.length > 0" x-cloak>
                                                    <span x-text="files.length"></span> file(s) selected
                                                </div>
                                                <div class="upload-zone__subtext">
                                                    Supported: PDF, JPG, PNG, DOCX, XLSX, CSV
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-brand w-100 py-2 shadow-sm">
                                                <i class="fas fa-cloud-upload-alt me-2"></i>Upload to Current Folder
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card doc-list-card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0">
                            <h5 class="mb-0 fw-bold" style="font-size: 1rem; color: #1e293b;">Documents in Current Scope</h5>
                            <span class="text-muted small">
                                {{ $currentFolder ? 'Folder: ' . $currentFolder->name : 'Root documents' }}
                            </span>
                        </div>

                        <div class="doc-table-wrapper border-0">
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Resource</th>
                                        <th>Department & Type</th>
                                        <th>Organizational Folder</th>
                                        <th>Physical Location</th>
                                        <th>Received On</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $index => $doc)
                                        @php
                                            $ext = strtolower(pathinfo($doc->system_filename, PATHINFO_EXTENSION));
                                            $iconClass = 'file-icon--generic';
                                            if($ext === 'pdf') $iconClass = 'file-icon--pdf';
                                            elseif(in_array($ext, ['jpg', 'jpeg', 'png'])) $iconClass = 'file-icon--img';
                                            elseif($ext === 'docx') $iconClass = 'file-icon--doc';
                                            elseif(in_array($ext, ['xls', 'xlsx'])) $iconClass = 'file-icon--xls';
                                            elseif($ext === 'csv') $iconClass = 'file-icon--csv';
                                        @endphp
                                        <tr class="animate-fade-in stagger-{{ ($index % 5) + 1 }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="file-icon-wrapper {{ $iconClass }}">
                                                        <i class="fas fa-{{ $ext === 'pdf' ? 'file-pdf' : (in_array($ext, ['jpg', 'jpeg', 'png']) ? 'file-image' : 'file-lines') }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $doc->original_filename }}</div>
                                                        <div class="text-muted small">{{ strtoupper($ext) }} &bull; {{ number_format(($doc->file_size_bytes ?? 0) / 1024, 2) }} KB</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-dark fw-medium small">{{ $doc->department->name }}</div>
                                                <div class="text-muted x-small text-uppercase mt-1">{{ $doc->documentType->name }}</div>
                                            </td>
                                            <td>
                                                @if($doc->documentFolder)
                                                    <span class="text-muted"><i class="fas fa-folder-open me-1 small"></i>{{ $doc->documentFolder->name }}</span>
                                                @else
                                                    <span class="text-muted italic">Root</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small"><i class="fas fa-building me-1 text-muted"></i>{{ $doc->folderLocation->name }}</div>
                                                <div class="text-muted x-small mt-1">{{ $doc->folderLocation->full_location }}</div>
                                            </td>
                                            <td>
                                                <div class="small">{{ $doc->date_received?->format('F d, Y') ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <span class="status-pill {{ $doc->status === 'active' ? 'status-pill--active' : 'status-pill--archived' }}">
                                                    {{ $doc->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('department-documents.download', $doc) }}" class="btn-action-round" title="Download Resource">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <form action="{{ route('department-documents.archive', $doc) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn-action-round" title="Archive Document"
                                                                onclick="return confirm('Archive this document? It will no longer appear in the main listing.')">
                                                            <i class="fas fa-archive"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-20"></i>
                                                    <p>No documents found in this scope.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($documents->hasPages())
                            <div class="card-footer bg-white border-top-0 py-3">
                                {{ $documents->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
