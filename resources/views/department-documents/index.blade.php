<x-app-layout>
    @php
        $selectedDepartment = $departments->firstWhere('id', $selectedDepartmentId);
        $rootFolders = $foldersByParent->get(0, collect());
        $canManageFolders = auth()->user()->hasRole('admin', 'encoder') && $selectedDepartmentId > 0;
        $canCreateFolders = auth()->user()->hasRole('admin', 'encoder') && $selectedDepartmentId > 0;
        $canEditDeleteFolders = auth()->user()->isAdmin() && $selectedDepartmentId > 0;
        $canUploadAndEdit = auth()->user()->hasRole('admin', 'encoder');

        $activePathIds = isset($folderBreadcrumbs) ? $folderBreadcrumbs->pluck('id')->toArray() : [];
        if ($currentFolderId) {
            $activePathIds[] = $currentFolderId;
        }
    @endphp

    <div class="animate-fade-in stagger-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold text-dark">Department Documents</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Browse folders first, then upload into the active
                    folder context.</p>
            </div>
        </div>

        @if (!$departments->isEmpty())
            <form action="{{ route('department-documents.index') }}" method="GET"
                class="doc-command-search mb-4" data-doc-live-search-form
                data-doc-suggest-url="{{ route('department-documents.search') }}">
                                    <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}" data-doc-search-department>
                                    <input type="hidden" name="global_search" value="{{ request('global_search') ? '1' : '0' }}" data-doc-global-search>
                                    @if (request('document_folder_id'))
                                        <input type="hidden" name="document_folder_id" value="{{ (int) request('document_folder_id') }}">
                                    @endif

                <div class="doc-command-search__bar">
                    <i class="fas fa-search doc-command-search__icon"></i>
                    <input type="text" name="search" class="doc-command-search__input" data-doc-live-search-input
                        placeholder="Search documents and folders" value="{{ request('search') }}" autocomplete="off">
                    <button type="button"
                        class="btn btn-sm doc-command-search__global {{ request('global_search') ? 'btn-danger text-white' : 'btn-outline-secondary' }}"
                        data-doc-global-search-toggle aria-pressed="{{ request('global_search') ? 'true' : 'false' }}"
                        title="Search all accessible departments">
                        <i class="fas fa-globe me-1"></i>Global
                    </button>
                    <button type="submit" class="btn btn-sm btn-light px-3 fw-medium">Search</button>
                </div>

                <div class="doc-command-search__scope text-muted" data-doc-search-scope>
                    {{ request('global_search') ? 'Quick suggestions across all accessible departments' : 'Searching within this department' }}
                </div>

                <div class="doc-command-search__results d-none" data-doc-search-suggestions>
                    <div class="doc-command-search__results-inner" data-doc-search-results></div>
                    <div class="doc-command-search__results-footer">
                        <button type="button" class="btn btn-link text-decoration-none p-0" data-doc-search-view-all>
                            View all results
                        </button>
                    </div>
                </div>
            </form>
        @endif

        @if (session('success'))
            <div class="alert-flash alert-flash--success mb-4" data-flash-success>
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-flash alert-flash--error mb-4" data-flash-error>
                <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        @if ($departments->isEmpty())
            <div class="card doc-list-card">
                <div class="card-body p-4 text-center text-muted">
                    <i class="fas fa-folder-open fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0">No accessible departments found for your account.</p>
                </div>
            </div>
        @else
            <div class="explorer-grid mb-4" id="department-document-explorer">
                <div class="card doc-list-card explorer-sidebar">
                    <div class="card-body p-3">
                        <div class="mb-4 pt-1">
                            <label class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Department</label>
                            <div class="d-flex align-items-center gap-2 px-2 py-2 rounded-3" style="background: rgba(221, 39, 13, 0.05); border: 1px solid rgba(221, 39, 13, 0.1);">
                                <i class="fas fa-building text-accent-red" style="font-size: 0.85rem;"></i>
                                <span class="fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">{{ $selectedDepartment?->name ?? 'None' }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">Folders</h6>
                            <div class="d-flex align-items-center gap-2">
                                @if ($selectedDepartmentId)
                                    <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId]) }}"
                                        class="text-decoration-none small">{{ $selectedDepartmentName ? $selectedDepartmentName . ' (Root)' : 'Root' }}</a>
                                @endif
                                 @if ($canCreateFolders)
                                     <button type="button" class="btn-action-round btn-action-round--xs"
                                         data-bs-toggle="modal" data-bs-target="#createDepartmentFolderModal"
                                         data-folder-department-id="{{ $selectedDepartmentId }}"
                                         data-folder-parent-id=""
                                         data-folder-create-scope="New folder at root level"
                                         title="Create folder at root">
                                         <i class="fas fa-plus"></i>
                                     </button>
                                 @endif
                            </div>
                        </div>
                        <ul class="ui-tree m-0 p-0 text-dark">
                            @forelse($rootFolders as $folder)
                                @include('department-documents.partials.folder-tree-node', [
                                    'folder' => $folder,
                                    'foldersByParent' => $foldersByParent,
                                    'selectedDepartmentId' => $selectedDepartmentId,
                                    'currentFolderId' => $currentFolderId,
                                    'activePathIds' => $activePathIds,
                                    'depth' => 0,
                                    'canManageFolders' => $canCreateFolders,
                                    'canEditDeleteFolders' => $canEditDeleteFolders,
                                ])
                            @empty
                                <div class="text-muted small py-2">No folders yet.</div>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="explorer-main d-flex flex-column gap-4">
                    <div class="card doc-list-card">
                        <div
                            class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0">
                            <div>
                                <h5 class="mb-1 fw-bold text-dark" style="font-size: 1.1rem;">
                                    <i class="fas fa-file-lines me-2 text-primary"></i>Documents in Current Scope
                                </h5>
                                <div class="text-muted small">
                                    {{ $currentFolder ? 'Folder: ' . $currentFolder->name . ($currentFolder->folder_code ? ' (' . $currentFolder->folder_code . ')' : '') : ($selectedDepartmentName ? $selectedDepartmentName . ' (Root)' : 'Root') . ' — All documents' }}
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                @if($canUploadAndEdit)
                                <button type="button"
                                    class="btn btn-accent-red btn-sm px-3 shadow-sm d-flex align-items-center gap-2 fw-medium"
                                    data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload
                                </button>
                                @endif
                            </div>
                        </div>

                        <div class="card-body pt-0 pb-3">
                            <div class="explorer-breadcrumb mb-2">
                                <a
                                    href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId]) }}">{{ $selectedDepartmentName ? $selectedDepartmentName . ' (Root)' : 'Root' }}</a>
                                @foreach ($folderBreadcrumbs as $crumb)
                                    <i class="fas fa-chevron-right mx-2 text-muted" style="font-size: 0.7rem;"></i>
                                    <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId, 'document_folder_id' => $crumb->id]) }}"
                                        class="{{ (int) $crumb->id === (int) $currentFolderId ? 'active' : '' }}">
                                        {{ $crumb->name }}{{ $crumb->folder_code ? ' (' . $crumb->folder_code . ')' : '' }}
                                    </a>
                                @endforeach
                            </div>

                        </div>

                        <div class="doc-table-wrapper border-0">
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Resource</th>
                                        <th>Department & Type</th>
                                        <th>Folder Code</th>
                                        <th>Physical Location</th>
                                        <th>Received On</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documents as $index => $doc)
                                        @php
                                            $ext = strtolower(pathinfo($doc->system_filename, PATHINFO_EXTENSION));
                                            $iconClass = 'file-icon--generic';
                                            if ($ext === 'pdf') {
                                                $iconClass = 'file-icon--pdf';
                                            } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                                $iconClass = 'file-icon--img';
                                            } elseif ($ext === 'docx') {
                                                $iconClass = 'file-icon--doc';
                                            } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                                $iconClass = 'file-icon--xls';
                                            } elseif ($ext === 'csv') {
                                                $iconClass = 'file-icon--csv';
                                            }

                                            $mime = strtolower((string) ($doc->mime_type ?? ''));
                                            $previewKind = null;

                                            if (
                                                str_starts_with($mime, 'image/') ||
                                                in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])
                                            ) {
                                                $previewKind = 'image';
                                            } elseif ($mime === 'application/pdf' || $ext === 'pdf') {
                                                $previewKind = 'pdf';
                                            } elseif (
                                                $ext === 'docx' ||
                                                $mime ===
                                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                                            ) {
                                                $previewKind = 'docx';
                                            } elseif (
                                                in_array($ext, ['xls', 'xlsx', 'csv']) ||
                                                in_array($mime, [
                                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                    'application/vnd.ms-excel',
                                                    'text/csv',
                                                    'application/csv',
                                                ])
                                            ) {
                                                $previewKind = 'sheet';
                                            }

                                            $previewUrl = null;
                                            if (in_array($previewKind, ['image', 'pdf'], true)) {
                                                $previewUrl = route('department-documents.preview', $doc);
                                            } elseif (in_array($previewKind, ['docx', 'sheet'], true)) {
                                                $previewUrl = route('department-documents.download', $doc);
                                            }

                                            $previewable = $previewUrl !== null;
                                            $docFolder = $doc->documentFolder;
                                            $docFolderCode = $docFolder?->folder_code ?? null;
                                            $docFolderPath = $docFolder
                                                ? $folderPathMaps[$docFolder->id]['display_path'] ?? $docFolder->name
                                                : ($selectedDepartmentName ? $selectedDepartmentName . ' (Root)' : 'Root');
                                        @endphp
                                        @php
                                            $isSelectedSearchDoc = (int) request('document_id') === (int) $doc->id;
                                        @endphp
                                        <tr class="animate-fade-in stagger-{{ ($index % 5) + 1 }} {{ $isSelectedSearchDoc ? 'doc-row--selected' : '' }}">
                                            <td class="cursor-pointer"
                                                title="Double-click to preview"
                                                @if($previewable)
                                                    data-preview-dblclick
                                                    data-preview-url="{{ $previewUrl }}"
                                                    data-preview-kind="{{ $previewKind }}"
                                                    data-preview-mime="{{ $doc->mime_type }}"
                                                    data-preview-name="{{ $doc->original_filename }}"
                                                    data-preview-ext="{{ $ext }}"
                                                @endif
                                            >
                                                <div class="d-flex align-items-center">
                                                    <div class="file-icon-wrapper {{ $iconClass }}">
                                                        <i
                                                            class="fas fa-{{ $ext === 'pdf' ? 'file-pdf' : (in_array($ext, ['jpg', 'jpeg', 'png']) ? 'file-image' : 'file-lines') }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark d-flex align-items-center gap-2 text-break">
                                                            {{ $doc->original_filename }}
                                                        </div>
                                                        <div class="text-muted small">{{ strtoupper($ext) }} &bull;
                                                            {{ number_format(($doc->file_size_bytes ?? 0) / 1024, 2) }}
                                                            KB</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-dark fw-medium small">{{ $doc->department->name }}
                                                </div>
                                                <div class="text-muted x-small text-uppercase mt-1">
                                                    {{ $doc->documentType->name }}</div>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold font-monospace">
                                                    {{ $docFolderCode ?? '—' }}</div>
                                                <div class="text-muted x-small mt-1">{{ $docFolderPath }}</div>
                                            </td>
                                            <td>
                                                {{-- <div class="small">{{ $doc->folderLocation->name }} </div> --}}
                                                <div class="text-muted x-small mt-1">
                                                    {{ $doc->folderLocation->full_location }}</div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    {{ $doc->date_received?->format('F d, Y') ?? '-' }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none shadow-none"
                                                        type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Document actions">
                                                        <i class="fas fa-ellipsis-v px-2 py-1"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                        @if ($previewable)
                                                            <li>
                                                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                                                    aria-label="Preview Document"
                                                                    data-preview-trigger
                                                                    data-preview-url="{{ $previewUrl }}"
                                                                    data-preview-kind="{{ $previewKind }}"
                                                                    data-preview-mime="{{ $doc->mime_type }}"
                                                                    data-preview-name="{{ $doc->original_filename }}"
                                                                    data-preview-ext="{{ $ext }}">
                                                                    <i class="fas fa-eye text-secondary" style="width: 16px;"></i><span class="fw-medium">Preview</span>
                                                                </button>
                                                            </li>
                                                        @endif
                                                        @if($canUploadAndEdit)
                                                        <li>
                                                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                                                data-bs-toggle="modal" data-bs-target="#renameDocumentModal"
                                                                data-doc-name="{{ $doc->original_filename }}"
                                                                data-doc-action="{{ route('department-documents.update', $doc) }}">
                                                                <i class="fas fa-edit text-secondary" style="width: 16px;"></i><span class="fw-medium">Rename</span>
                                                            </button>
                                                        </li>
                                                        @endif
                                                        <li>
                                                            <a href="{{ route('department-documents.download', $doc) }}"
                                                                class="dropdown-item d-flex align-items-center gap-2 py-2">
                                                                <i class="fas fa-download text-secondary" style="width: 16px;"></i><span class="fw-medium">Download</span>
                                                            </a>
                                                        </li>
                                                        @if($canUploadAndEdit)
                                                        <li><hr class="dropdown-divider opacity-50 my-1"></li>
                                                        <li>
                                                            <button type="button" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2"
                                                                data-bs-toggle="modal" data-bs-target="#archiveDocumentModal"
                                                                data-doc-name="{{ $doc->original_filename }}"
                                                                data-doc-url="{{ route('department-documents.archive', $doc) }}">
                                                                <i class="fas fa-archive" style="width: 16px;"></i><span class="fw-medium">Archive</span>
                                                            </button>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
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

                        @if ($documents->hasPages())
                            <div class="card-footer bg-white border-top-0 py-3">
                                {{ $documents->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>


                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="createDepartmentFolderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form method="POST" action="{{ route('department-documents.folders.store') }}" data-ajax-folder
                    data-folder-create-form data-loading-target>
                    @csrf
                    <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}"
                        data-folder-create-department-id>
                    <input type="hidden" name="parent_id" value="{{ $currentFolderId ?: '' }}"
                        data-folder-create-parent-id>

                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold text-dark fs-5">Add Folder</h5>
                        <button type="button" class="btn-close opacity-50" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 pt-2">
                        <p class="text-muted mb-4 small" data-folder-create-scope>
                            {{ $currentFolderId ? 'New folder inside current folder' : 'New folder at root level' }}
                        </p>

                        <div class="mb-2">
                            <label for="create_folder_name" class="form-label fw-semibold text-secondary"
                                style="font-size: 0.85rem;">
                                Folder Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="create_folder_name" name="name"
                                class="form-control field-input bg-light" placeholder="e.g. 2026 Expenses" required
                                data-folder-create-name>
                        </div>
                        <div class="mb-2">
                            <label for="create_folder_code" class="form-label fw-semibold text-secondary"
                                style="font-size: 0.85rem;">
                                Folder Code <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <input type="text" id="create_folder_code" name="folder_code"
                                class="form-control field-input bg-light" placeholder="e.g. CSC-FIN-0001"
                                data-folder-create-code>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2 bg-white">
                        <button type="button" class="btn btn-light fw-semibold text-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-danger text-white d-inline-flex align-items-center gap-2 fw-semibold shadow-sm btn-submit-loading"
                            data-folder-submit>
                            <i class="fas fa-save"></i> Save Folder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rename Folder Modal -->
    <div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="global-rename-folder-form" method="POST" data-ajax-folder data-loading-target>
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold text-dark fs-5">Rename Folder</h5>
                        <button type="button" class="btn-close opacity-50" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 pt-2">
                        <div class="mb-2">
                            <label for="rename_folder_name" class="form-label fw-semibold text-secondary"
                                style="font-size: 0.85rem;">Folder Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="rename_folder_name"
                                class="form-control field-input bg-light" required>
                        </div>
                        <div class="mb-2">
                            <label for="rename_folder_code" class="form-label fw-semibold text-secondary"
                                style="font-size: 0.85rem;">Folder Code <span
                                    class="text-muted fw-normal">(Optional)</span></label>
                            <input type="text" name="folder_code" id="rename_folder_code"
                                class="form-control field-input bg-light" placeholder="e.g. CSC-HR-0001">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2 bg-white">
                        <button type="button" class="btn btn-light fw-semibold text-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold shadow-sm btn-submit-loading">
                            <i class="fas fa-pen me-1"></i> Rename
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Folder Modal -->
    <div class="modal fade" id="deleteFolderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="global-delete-folder-form" method="POST" data-ajax-folder data-loading-target>
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold text-dark fs-5">Delete Folder</h5>
                        <button type="button" class="btn-close opacity-50" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <p class="text-dark mb-0">Are you sure you want to delete <strong
                                id="delete_folder_name_display"></strong>?</p>
                        <p class="text-muted small mt-2 mb-0"><i
                                class="fas fa-exclamation-triangle text-warning me-1"></i>Deletion is only allowed when
                            the folder has no subfolders and no documents.</p>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2 bg-white">
                        <button type="button" class="btn btn-light fw-semibold text-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger fw-semibold shadow-sm btn-submit-loading">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Archive Document Modal -->
    <div class="modal fade" id="archiveDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="archiveDocumentForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold text-dark fs-5">Archive Document</h5>
                        <button type="button" class="btn-close opacity-50" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <p class="text-dark mb-0">Are you sure you want to archive <strong
                                id="archive_document_name_display"></strong>?</p>
                        <p class="text-muted small mt-2 mb-0">It will no longer appear in the main listing.</p>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2 bg-white">
                        <button type="button" class="btn btn-light fw-semibold text-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger fw-semibold shadow-sm btn-submit-loading">
                            <i class="fas fa-archive"></i> Archive
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rename Document Modal -->
    <div class="modal fade" id="renameDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="renameDocumentForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold text-dark fs-5">Rename Document</h5>
                        <button type="button" class="btn-close opacity-50" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 pt-2">
                        <div class="mb-3">
                            <label for="rename_doc_name" class="form-label fw-semibold text-secondary"
                                style="font-size: 0.85rem;">Document Name</label>
                            <input type="text" name="original_filename" id="rename_doc_name"
                                class="form-control field-input bg-light" required>
                            <div class="form-text small">Extension will be preserved automatically.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2 bg-white">
                        <button type="button" class="btn btn-light fw-semibold text-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold shadow-sm btn-submit-loading">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark fs-5">
                        <i class="fas fa-cloud-upload-alt me-2 text-accent-red"></i> Upload Documents
                    </h5>
                    <button type="button" class="btn-close opacity-50" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('department-documents.store') }}"
                        enctype="multipart/form-data" id="upload-form" x-data="{ dragging: false, files: [], uploadMode: 'standard', showExpiry: false }"
                        x-init="$nextTick(() => { const select = $refs.typeSelect; if (select && select.selectedIndex >= 0) showExpiry = select.options[select.selectedIndex].dataset.hasExpiry === '1'; })" data-loading-target>
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                        @if ($currentFolderId)
                            <input type="hidden" name="document_folder_id" value="{{ $currentFolderId }}">
                        @endif

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="upload_department"
                                            class="form-label small fw-bold text-uppercase text-muted">Department</label>
                                        <input type="text" id="upload_department"
                                            class="form-control field-input bg-light"
                                            value="{{ $selectedDepartment?->name ?? 'N/A' }}" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="upload_type"
                                            class="form-label small fw-bold text-uppercase text-muted">Document Type
                                            <span class="text-danger">*</span></label>
                                        <select id="upload_type" name="document_type_id"
                                            class="form-select field-input" required x-ref="typeSelect"
                                            @change="showExpiry = $event.target.options[$event.target.selectedIndex].dataset.hasExpiry === '1'">
                                            <option value="" data-has-expiry="0">Select type</option>
                                            @foreach ($documentTypes as $type)
                                                <option value="{{ $type->id }}"
                                                    data-has-expiry="{{ $type->has_expiry ? '1' : '0' }}"
                                                    {{ request('document_type_id') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="upload_location"
                                            class="form-label small fw-bold text-uppercase text-muted">Physical
                                            Location <span class="text-danger">*</span></label>
                                        <select id="upload_location" name="folder_location_id"
                                            class="form-select field-input" required>
                                            <option value="">Select location</option>
                                            @foreach ($folderLocations as $location)
                                                <option value="{{ $location->id }}">{{ $location->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="upload_mode"
                                            class="form-label small fw-bold text-uppercase text-muted">Upload Mode
                                            <span class="text-danger">*</span></label>
                                        <select id="upload_mode" name="upload_mode" class="form-select field-input"
                                            required x-model="uploadMode">
                                            <option value="standard">Standard (Keep original)</option>
                                            <option value="scan_packet">Scan Packet (Merge to PDF)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="upload_date_received"
                                            class="form-label small fw-bold text-uppercase text-muted">Date Received
                                            <span class="text-danger">*</span></label>
                                        <input type="date" id="upload_date_received" name="date_received"
                                            class="form-control field-input" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-md-6" x-show="showExpiry" x-cloak>
                                        <label for="upload_expiry_date"
                                            class="form-label small fw-bold text-uppercase text-muted">Expiry
                                            Date</label>
                                        <input type="date" id="upload_expiry_date" name="expiry_date"
                                            class="form-control field-input">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <label class="form-label small fw-bold text-uppercase text-muted">Files to Upload <span
                                        class="text-danger">*</span></label>
                                <div class="upload-zone position-relative overflow-hidden rounded-3 border-2 border-dashed upload-zone--accent-red bg-light"
                                    :class="{ 'dragging': dragging }" @dragover.prevent="dragging = true"
                                    @dragleave.prevent="dragging = false"
                                    @drop.prevent="dragging = false; files = Array.from($event.dataTransfer.files); $refs.fileInput.files = $event.dataTransfer.files"
                                    style="min-height: 200px;">

                                    <input type="file" name="files[]" multiple required x-ref="fileInput"
                                        :accept="uploadMode === 'scan_packet' ? '.pdf,.jpg,.jpeg,.png' :
                                            '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv'"
                                        @change="files = Array.from($event.target.files)"
                                        style="opacity: 0; position: absolute; top:0; left:0; width: 100%; height: 100%; cursor: pointer; z-index: 10;">

                                    <div class="upload-zone__content p-4 text-center d-flex flex-column justify-content-center h-100 position-absolute w-100 h-100 start-0 top-0 align-items-center"
                                        style="pointer-events: none;">
                                        <i class="fas fa-file-export mb-2 fs-2 text-accent-red opacity-75"
                                            x-show="files.length === 0"></i>
                                        <div class="upload-zone__text fw-medium text-dark"
                                            x-show="files.length === 0">Drag & drop files or click to browse</div>

                                        <div class="w-100" x-show="files.length > 0" x-cloak
                                            style="max-height: 140px; overflow-y: auto;">
                                            <div class="d-flex flex-wrap justify-content-center gap-1 mt-1 px-2">
                                                <template x-for="file in files" :key="file.name">
                                                    <span class="badge badge-accent-red py-2 px-3 fw-medium"
                                                        style="max-width: 100%; overflow: hidden; text-overflow: ellipsis;"
                                                        x-text="file.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="upload-zone__subtext text-muted small mt-2"
                                            x-show="files.length === 0"
                                            x-text="uploadMode === 'scan_packet' ? 'Supported: PDF, JPG, PNG' : 'Supported: PDF, JPG, PNG, DOC(X), XLS(X), CSV'">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 d-flex justify-content-end bg-white">
                            <button type="button" class="btn btn-light fw-semibold text-secondary me-2"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-accent-red px-4 shadow-sm btn-submit-loading">
                                <i class="fas fa-cloud-upload-alt"></i> Upload Documents
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="preview-overlay" id="document-preview-overlay" hidden>
        <div class="preview-overlay__backdrop" data-preview-close></div>
        <div class="preview-overlay__toolbar">
            <div class="preview-overlay__toolbar-left">
                <button type="button" class="preview-overlay__btn" data-preview-close aria-label="Close preview"
                    title="Close">
                    <i class="fas fa-times"></i>
                </button>
                <span class="preview-overlay__filename" id="document-preview-title">Preview</span>
            </div>
            <div class="preview-overlay__toolbar-center d-none" id="preview-pdf-controls">
                <div class="preview-overlay__pager" aria-label="PDF page navigation">
                    <span class="preview-overlay__pager-label">Page</span>
                    <input type="text" inputmode="numeric" class="preview-overlay__pager-input" id="preview-page-input"
                        aria-label="Current page">
                    <span>/</span>
                    <span id="preview-page-total">1</span>
                </div>
                <div class="preview-overlay__zoom" aria-label="PDF zoom controls">
                    <button type="button" class="preview-overlay__btn" id="preview-zoom-out" title="Zoom out"
                        aria-label="Zoom out">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button type="button" class="preview-overlay__btn" id="preview-zoom-reset" title="Reset zoom"
                        aria-label="Reset zoom">
                        <i class="fas fa-compress-arrows-alt"></i>
                    </button>
                    <button type="button" class="preview-overlay__btn" id="preview-zoom-in" title="Zoom in"
                        aria-label="Zoom in">
                        <i class="fas fa-search-plus"></i>
                    </button>
                </div>
            </div>
            <div class="preview-overlay__toolbar-right">
                <a href="#" class="preview-overlay__btn" id="preview-download-btn" title="Download" download>
                    <i class="fas fa-download"></i>
                </a>
                <button type="button" class="preview-overlay__btn" id="preview-print-btn" title="Print">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </div>
        <div class="preview-overlay__content" data-preview-close>
            <div class="preview-overlay__body" id="document-preview-body">
                <div class="text-muted small">Select a previewable file (PDF, image, DOCX, Excel, or CSV).</div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Bootstrap TreeView - Tree3 Port */
            .ui-tree {
                list-style: none;
                padding-left: 0;
            }

            .ui-tree-children {
                list-style: none;
                padding-left: 1.25rem;
                position: relative;
                margin-top: 2px;
            }

            .ui-tree-children::before {
                content: '';
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0.6rem;
                width: 1px;
                background-color: var(--bs-border-color);
            }

            .ui-tree-node {
                position: relative;
                margin-bottom: 2px;
            }

            .ui-tree-children>.ui-tree-node::before {
                content: '';
                position: absolute;
                top: 15px;
                left: -0.65rem;
                width: 0.65rem;
                height: 1px;
                background-color: var(--bs-border-color);
            }

            .ui-tree-children>.ui-tree-node:last-child::after {
                content: '';
                position: absolute;
                top: 15px;
                left: calc(-0.65rem - 1px);
                bottom: -10px;
                width: 3px;
                background-color: #fff;
            }

            .ui-tree-item {
                transition: background-color 0.2s ease-out;
            }

            .ui-tree-item:hover {
                background-color: var(--bs-light);
            }

            .ui-tree-actions {
                opacity: 0;
                transition: opacity 0.2s;
            }

            .ui-tree-item:hover .ui-tree-actions,
            .ui-tree-actions.show {
                opacity: 1;
            }

            .ui-tree-item.active {
                background-color: var(--bs-light);
            }

            .collapse-icon {
                transition: transform 0.2s ease-in-out;
            }

            .collapsed .collapse-icon {
                transform: rotate(-90deg);
            }

            .animate-fade-in {
                animation: fadeIn 0.3s ease-in-out forwards;
            }

            .doc-table tbody tr.animate-fade-in {
                animation-name: fadeInNoTransform;
            }

            .doc-table tbody tr.doc-row--selected td {
                background: rgba(221, 39, 13, 0.08);
            }

            .doc-table tbody tr.doc-row--selected td:first-child {
                border-left: 3px solid var(--company-primary);
            }

            .animate-fade-out {
                animation: fadeOut 0.4s ease-in-out forwards;
            }

            .btn-accent-red {
                background-color: var(--company-primary) !important;
                border-color: var(--company-primary) !important;
                color: #fff !important;
            }

            .btn-accent-red:hover {
                background-color: var(--company-primary-hover) !important;
                border-color: var(--company-primary-hover) !important;
            }

            .text-accent-red {
                color: var(--company-primary) !important;
            }

            .upload-zone--accent-red {
                border-color: var(--company-primary) !important;
            }

            .upload-zone--accent-red.dragging {
                background-color: var(--company-primary-light) !important;
                border-color: var(--company-primary) !important;
            }

            .badge-accent-red {
                background-color: var(--company-primary-light) !important;
                color: var(--company-primary) !important;
                border: 1px solid var(--company-primary-border) !important;
            }

            .doc-command-search {
                position: relative;
            }

            .doc-command-search__bar {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                background: #f3f4f6;
                border: 1px solid #e5e7eb;
                border-radius: 999px;
                padding: 0.45rem 0.6rem;
            }

            .doc-command-search__icon {
                color: #6b7280;
                margin-left: 0.35rem;
            }

            .doc-command-search__input {
                flex: 1;
                border: none;
                background: transparent;
                outline: none;
                min-width: 160px;
                color: #111827;
            }

            .doc-command-search__input:focus {
                box-shadow: none;
            }

            .doc-command-search__global {
                border-radius: 999px;
            }

            .doc-command-search__scope {
                font-size: 0.75rem;
                margin-top: 0.45rem;
                padding-left: 0.9rem;
            }

            .doc-command-search__results {
                position: absolute;
                z-index: 1090;
                top: calc(100% + 0.4rem);
                left: 0;
                right: 0;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 0.9rem;
                box-shadow: 0 12px 36px rgba(15, 23, 42, 0.12);
                overflow: hidden;
            }

            .doc-command-search__results-inner {
                max-height: 360px;
                overflow-y: auto;
            }

            .doc-command-search__result-item {
                width: 100%;
                border: none;
                background: #fff;
                padding: 0.75rem 0.95rem;
                text-align: left;
                border-bottom: 1px solid #f3f4f6;
            }

            .doc-command-search__result-item:hover,
            .doc-command-search__result-item:focus {
                background: #fef2f2;
                outline: none;
            }

            .doc-command-search__result-title {
                font-weight: 700;
                color: #111827;
                line-height: 1.25;
            }

            .doc-command-search__result-meta {
                font-size: 0.76rem;
                color: #6b7280;
                margin-top: 0.18rem;
                line-height: 1.2;
            }

            .doc-command-search__results-footer {
                display: flex;
                justify-content: flex-end;
                padding: 0.55rem 0.9rem;
                background: #fafafa;
            }

            @media (max-width: 768px) {
                .doc-command-search__bar {
                    border-radius: 1rem;
                    flex-wrap: wrap;
                }

                .doc-command-search__input {
                    width: 100%;
                    order: 1;
                }
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeInNoTransform {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @keyframes fadeOut {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }

                to {
                    opacity: 0;
                    transform: translateY(-10px);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                let explorer = document.getElementById('department-document-explorer');
                const previewOverlay = document.getElementById('document-preview-overlay');
                const previewContent = previewOverlay?.querySelector('.preview-overlay__content');
                const previewTitle = document.getElementById('document-preview-title');
                const previewBody = document.getElementById('document-preview-body');
                const previewDownloadBtn = document.getElementById('preview-download-btn');
                const previewPrintBtn = document.getElementById('preview-print-btn');
                const previewPdfControls = document.getElementById('preview-pdf-controls');
                const previewPager = previewPdfControls?.querySelector('.preview-overlay__pager');
                const previewPageInput = document.getElementById('preview-page-input');
                const previewPageTotal = document.getElementById('preview-page-total');
                const previewZoomOutBtn = document.getElementById('preview-zoom-out');
                const previewZoomResetBtn = document.getElementById('preview-zoom-reset');
                const previewZoomInBtn = document.getElementById('preview-zoom-in');
                let currentPreviewUrl = '';
                let currentPreviewKind = '';
                const pdfPreviewState = {
                    totalPages: 0,
                    currentPage: 1,
                    zoom: 1,
                    minZoom: 0.5,
                    maxZoom: 2.5,
                };
                let previewControlsHideTimer = null;
                const liveSearchState = {
                    shouldRefocus: false,
                    caretStart: null,
                    caretEnd: null,
                    requestId: 0,
                    activeRequestId: 0,
                    abortController: null,
                };

                const resetPreviewBody = () => {
                    if (!previewBody) {
                        return;
                    }

                    previewBody.innerHTML =
                        '<div class="text-muted small">Select a previewable file (PDF, image, DOCX, Excel, or CSV).</div>';
                };

                const clearFlash = () => {
                    document.querySelectorAll('[data-flash-success], [data-flash-error]').forEach((node) => node
                        .remove());
                };

                const setPreviewControlsMode = (mode) => {
                    if (!previewPdfControls || !previewOverlay) {
                        return;
                    }

                    const visible = mode === 'pdf' || mode === 'docx' || mode === 'zoom';
                    previewPdfControls.classList.toggle('d-none', !visible);
                    previewOverlay.classList.toggle('preview-overlay--pdf', mode === 'pdf');
                    previewOverlay.classList.toggle('preview-overlay--has-controls', visible);
                    previewOverlay.classList.toggle('preview-overlay--paged', false);
                    previewOverlay.classList.toggle('preview-overlay--controls-hidden', false);

                    if (previewPager) {
                        previewPager.classList.toggle('d-none', !(mode === 'pdf' || mode === 'docx'));
                    }

                    if (previewControlsHideTimer) {
                        clearTimeout(previewControlsHideTimer);
                        previewControlsHideTimer = null;
                    }
                };

                const revealPdfControlsTemporarily = () => {
                    if (!previewOverlay || previewOverlay.hidden || !previewOverlay.classList.contains('preview-overlay--has-controls')) {
                        return;
                    }

                    previewOverlay.classList.remove('preview-overlay--controls-hidden');
                    if (previewControlsHideTimer) {
                        clearTimeout(previewControlsHideTimer);
                    }

                    previewControlsHideTimer = setTimeout(() => {
                        if (!previewOverlay.hidden && previewOverlay.classList.contains('preview-overlay--has-controls')) {
                            previewOverlay.classList.add('preview-overlay--controls-hidden');
                        }
                    }, 1600);
                };

                const syncPdfControls = () => {
                    if (previewPageInput) {
                        previewPageInput.value = String(pdfPreviewState.currentPage || 1);
                    }

                    if (previewPageTotal) {
                        previewPageTotal.textContent = String(pdfPreviewState.totalPages || 1);
                    }
                };

                const applyPreviewZoom = () => {
                    if (!previewBody) {
                        return;
                    }

                    if (currentPreviewKind === 'pdf') {
                        const canvases = previewBody.querySelectorAll('.preview-overlay__pdf-canvas');
                        const zoomPercent = Math.max(25, Math.round(pdfPreviewState.zoom * 100));
                        canvases.forEach((canvas) => {
                            canvas.style.width = `${zoomPercent}%`;
                            canvas.style.maxWidth = 'none';
                            canvas.style.position = '';
                            canvas.style.left = '';
                            canvas.style.transform = '';
                            canvas.style.transformOrigin = '';
                            canvas.style.marginLeft = '';
                            canvas.style.marginRight = '';
                            canvas.style.alignSelf = 'center';
                        });
                        return;
                    }

                    if (currentPreviewKind === 'image') {
                        const image = previewBody.querySelector('.preview-overlay__image');
                        const imageCanvas = previewBody.querySelector('.preview-overlay__image-canvas');
                        if (image) {
                            image.style.width = '';
                            image.style.maxWidth = '100%';
                            image.style.transform = `scale(${pdfPreviewState.zoom})`;
                            image.style.transformOrigin = 'center center';
                            image.style.marginLeft = 'auto';
                            image.style.marginRight = 'auto';
                        }

                        if (imageCanvas) {
                            imageCanvas.style.transform = `scale(${pdfPreviewState.zoom})`;
                            imageCanvas.style.transformOrigin = 'center center';
                            imageCanvas.style.marginLeft = 'auto';
                            imageCanvas.style.marginRight = 'auto';
                        }
                        return;
                    }

                    if (currentPreviewKind === 'docx') {
                        const docx = previewBody.querySelector('.preview-overlay__docx');
                        if (docx) {
                            docx.style.zoom = String(pdfPreviewState.zoom);
                        }
                        return;
                    }

                    if (currentPreviewKind === 'sheet') {
                        const sheet = previewBody.querySelector('.preview-overlay__sheet-wrap');
                        if (sheet) {
                            sheet.style.zoom = String(pdfPreviewState.zoom);
                        }
                    }
                };

                const scrollPdfToPage = (page) => {
                    const scroller = previewContent || previewBody;
                    if (!scroller) {
                        return;
                    }

                    const index = Math.max(1, Math.min(page, pdfPreviewState.totalPages || 1));
                    const target = previewBody?.querySelector(`.preview-overlay__pdf-canvas[data-page-number="${index}"]`);
                    if (target) {
                        const scrollerRect = scroller.getBoundingClientRect();
                        const targetRect = target.getBoundingClientRect();
                        const nextTop = scroller.scrollTop + (targetRect.top - scrollerRect.top) - 12;
                        scroller.scrollTo({
                            top: Math.max(0, nextTop),
                            behavior: 'smooth',
                        });
                        pdfPreviewState.currentPage = index;
                        syncPdfControls();
                    }
                };

                const scrollDocxToPage = (page) => {
                    const scroller = previewContent || previewBody;
                    if (!scroller || !previewBody) {
                        return;
                    }

                    const pages = Array.from(previewBody.querySelectorAll('.preview-overlay__docx .docx-wrapper section.docx'));
                    if (pages.length === 0) {
                        return;
                    }

                    const index = Math.max(1, Math.min(page, pages.length));
                    const target = pages[index - 1];
                    const scrollerRect = scroller.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();
                    const nextTop = scroller.scrollTop + (targetRect.top - scrollerRect.top) - 12;

                    scroller.scrollTo({
                        top: Math.max(0, nextTop),
                        behavior: 'smooth',
                    });

                    pdfPreviewState.currentPage = index;
                    pdfPreviewState.totalPages = pages.length;
                    syncPdfControls();
                };

                // Rename Document Modal logic
                const renameDocumentModal = document.getElementById('renameDocumentModal');
                if (renameDocumentModal) {
                    renameDocumentModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const docName = button.getAttribute('data-doc-name');
                        const actionUrl = button.getAttribute('data-doc-action');

                        const form = renameDocumentModal.querySelector('#renameDocumentForm');
                        const input = renameDocumentModal.querySelector('#rename_doc_name');

                        form.action = actionUrl;
                        input.value = docName;
                        
                        // Focus after a short delay to allow modal animation
                        setTimeout(() => input.select(), 500);
                    });
                }

                const bindPreviewDoubleClick = () => {
                    document.querySelectorAll('[data-preview-dblclick]').forEach((cell) => {
                        if (cell.dataset.previewDblclickBound === '1') {
                            return;
                        }

                        cell.dataset.previewDblclickBound = '1';
                        cell.addEventListener('dblclick', function() {
                            const previewUrl = this.getAttribute('data-preview-url');
                            const previewKind = this.getAttribute('data-preview-kind');
                            const previewMime = this.getAttribute('data-preview-mime');
                            const previewName = this.getAttribute('data-preview-name');
                            const previewExt = this.getAttribute('data-preview-ext');

                            if (typeof window.triggerPreview === 'function') {
                                window.triggerPreview({
                                    url: previewUrl,
                                    kind: previewKind,
                                    mime: previewMime,
                                    name: previewName,
                                    ext: previewExt
                                });
                            } else {
                                const trigger = this.closest('tr')?.querySelector('[data-preview-trigger]');
                                if (trigger) {
                                    trigger.click();
                                }
                            }
                        });
                    });
                };

                const showFlash = (message, type = 'success') => {
                    clearFlash();
                    if (!explorer || !explorer.parentElement) {
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.setAttribute(type === 'success' ? 'data-flash-success' : 'data-flash-error', '1');
                    wrapper.className = `alert-flash alert-flash--${type} mb-4 animate-fade-in`;
                    wrapper.innerHTML =
                        `${type === 'success' ? '<i class="fas fa-check-circle me-2"></i>' : '<i class="fas fa-exclamation-circle me-2"></i>'}${message}`;
                    explorer.parentElement.insertBefore(wrapper, explorer);

                    // Auto-fade after 2 seconds
                    setTimeout(() => {
                        wrapper.classList.remove('animate-fade-in');
                        wrapper.classList.add('animate-fade-out');
                        setTimeout(() => wrapper.remove(), 400);
                    }, 2000);
                };

                const disableSubmit = (form, disabled) => {
                    form.querySelectorAll('button[type="submit"]').forEach((button) => {
                        button.disabled = disabled;
                    });
                };

                const clearOneTimeDocumentFocusParam = () => {
                    const currentUrl = new URL(window.location.href);
                    if (!currentUrl.searchParams.has('document_id')) {
                        return;
                    }

                    currentUrl.searchParams.delete('document_id');
                    const nextQuery = currentUrl.searchParams.toString();
                    const nextUrl = `${currentUrl.pathname}${nextQuery ? `?${nextQuery}` : ''}${currentUrl.hash || ''}`;
                    window.history.replaceState({}, '', nextUrl);
                };

                const PREVIEW_LIBRARIES = {
                    jsZip: 'https://unpkg.com/jszip/dist/jszip.min.js',
                    pdfJs: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js',
                    pdfWorker: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js',
                    docxPreview: 'https://unpkg.com/docx-preview@0.3.7/dist/docx-preview.min.js',
                    sheetJs: 'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
                };

                const scriptPromises = {};
                let previewRenderToken = 0;

                const closePreview = () => {
                    if (!previewOverlay) {
                        return;
                    }

                    previewRenderToken += 1;
                    previewOverlay.hidden = true;
                    document.body.classList.remove('preview-overlay-open');
                    currentPreviewKind = '';
                    setPreviewControlsMode('none');
                    previewOverlay.classList.remove('preview-overlay--pdf', 'preview-overlay--paged', 'preview-overlay--has-controls', 'preview-overlay--controls-hidden', 'preview-overlay--sheet');
                    resetPreviewBody();
                };

                const openPreview = () => {
                    if (!previewOverlay) {
                        return;
                    }

                    previewOverlay.hidden = false;
                    document.body.classList.add('preview-overlay-open');
                    if (previewContent) {
                        previewContent.scrollTo({ top: 0, left: 0, behavior: 'auto' });
                        previewContent.scrollLeft = 0;
                    }
                };

                const escapeHtml = (value) => String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');

                const loadScript = (src) => {
                    if (scriptPromises[src]) {
                        return scriptPromises[src];
                    }

                    scriptPromises[src] = new Promise((resolve, reject) => {
                        const existing = Array.from(document.querySelectorAll('script[data-preview-lib]'))
                            .find((node) => node.getAttribute('data-preview-lib') === src);

                        if (existing) {
                            if (existing.dataset.loaded === '1') {
                                resolve();
                                return;
                            }

                            existing.addEventListener('load', () => resolve(), {
                                once: true
                            });
                            existing.addEventListener('error', () => reject(new Error(
                                `Failed to load ${src}`)), {
                                once: true
                            });
                            return;
                        }

                        const script = document.createElement('script');
                        script.src = src;
                        script.async = true;
                        script.defer = true;
                        script.dataset.previewLib = src;
                        script.addEventListener('load', () => {
                            script.dataset.loaded = '1';
                            resolve();
                        }, {
                            once: true
                        });
                        script.addEventListener('error', () => reject(new Error(`Failed to load ${src}`)), {
                            once: true
                        });
                        document.head.appendChild(script);
                    });

                    return scriptPromises[src];
                };

                const fetchFileBuffer = async (url) => {
                    const response = await fetch(url, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to fetch preview file.');
                    }

                    return response.arrayBuffer();
                };

                const fetchFileBlob = async (url) => {
                    const response = await fetch(url, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to fetch preview file.');
                    }

                    return response.blob();
                };

                const loadImageElement = (src) => new Promise((resolve, reject) => {
                    const img = new Image();
                    img.onload = () => resolve(img);
                    img.onerror = () => reject(new Error('Image failed to load.'));
                    img.src = src;
                });

                const showPreviewLoading = (message) => {
                    if (!previewBody) {
                        return;
                    }

                    previewBody.innerHTML =
                        `<div class="preview-overlay__loading">${escapeHtml(message || 'Loading preview...')}</div>`;
                };

                const renderPdfWithPdfJs = async (url, token) => {
                    const MAX_PDF_PREVIEW_PAGES = 20;
                    const MAX_CANVAS_PIXELS = 5_000_000;
                    const MAX_CANVAS_SIDE = 8192;
                    const MIN_PDF_SCALE_FOR_CANVAS = 0.02;
                    const MAX_PAGE_POINTS_FOR_CANVAS = 30000;

                    await loadScript(PREVIEW_LIBRARIES.pdfJs);

                    if (!window.pdfjsLib) {
                        throw new Error('PDF renderer is unavailable.');
                    }

                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = PREVIEW_LIBRARIES.pdfWorker;

                    const buffer = await fetchFileBuffer(url);
                    if (token !== previewRenderToken) {
                        return;
                    }

                    const pdf = await window.pdfjsLib.getDocument({
                        data: buffer,
                        maxImageSize: 20_000_000,
                    }).promise;
                    if (token !== previewRenderToken) {
                        return;
                    }

                    const stack = document.createElement('div');
                    stack.className = 'preview-overlay__pdf-stack';
                    previewBody.innerHTML = '';
                    previewBody.appendChild(stack);
                    if (previewContent) {
                        previewContent.scrollTo({ top: 0, left: 0, behavior: 'auto' });
                    }

                    pdfPreviewState.totalPages = Math.min(pdf.numPages, MAX_PDF_PREVIEW_PAGES);
                    pdfPreviewState.currentPage = 1;
                    pdfPreviewState.zoom = 1;
                    syncPdfControls();
                    setPreviewControlsMode('pdf');
                    revealPdfControlsTemporarily();

                    const containerWidth = Math.max((previewBody.clientWidth || 900) - 24, 320);
                    const pagesToRender = Math.min(pdf.numPages, MAX_PDF_PREVIEW_PAGES);
                    previewOverlay?.classList.toggle('preview-overlay--paged', pagesToRender > 1);
                    for (let pageNumber = 1; pageNumber <= pagesToRender; pageNumber++) {
                        if (token !== previewRenderToken) {
                            return;
                        }

                        const page = await pdf.getPage(pageNumber);
                        const baseViewport = page.getViewport({
                            scale: 1,
                        });

                        const hasExtremePageDimension = baseViewport.width > MAX_PAGE_POINTS_FOR_CANVAS ||
                            baseViewport.height > MAX_PAGE_POINTS_FOR_CANVAS;
                        if (hasExtremePageDimension) {
                            page.cleanup();
                            const note = document.createElement('div');
                            note.className = 'preview-overlay__sheet-note';
                            note.innerHTML = `Page ${pageNumber} is too large for reliable inline canvas preview. <a href="${encodeURI(url)}" target="_blank" rel="noopener">Open / Download</a>.`;
                            stack.appendChild(note);
                            continue;
                        }

                        const fitScale = containerWidth / Math.max(baseViewport.width, 1);
                        const baseScale = Math.max(0.02, Math.min(1.75, fitScale));
                        const pagePixelAreaAtBaseScale = (baseViewport.width * baseScale) * (baseViewport.height * baseScale);
                        const areaScale = pagePixelAreaAtBaseScale > MAX_CANVAS_PIXELS
                            ? Math.sqrt(MAX_CANVAS_PIXELS / Math.max(pagePixelAreaAtBaseScale, 1))
                            : 1;
                        const sideScale = Math.min(
                            1,
                            MAX_CANVAS_SIDE / Math.max(baseViewport.width * baseScale, 1),
                            MAX_CANVAS_SIDE / Math.max(baseViewport.height * baseScale, 1)
                        );
                        const scale = Math.max(0.005, baseScale * areaScale * sideScale);
                        if (scale < MIN_PDF_SCALE_FOR_CANVAS) {
                            page.cleanup();
                            const note = document.createElement('div');
                            note.className = 'preview-overlay__sheet-note';
                            note.innerHTML = `Page ${pageNumber} is too large for readable inline preview. <a href="${encodeURI(url)}" target="_blank" rel="noopener">Open / Download</a>.`;
                            stack.appendChild(note);
                            continue;
                        }
                        const viewport = page.getViewport({
                            scale,
                        });

                        const canvas = document.createElement('canvas');
                        canvas.className = 'preview-overlay__pdf-canvas';
                        canvas.dataset.pageNumber = String(pageNumber);
                        canvas.width = Math.floor(viewport.width);
                        canvas.height = Math.floor(viewport.height);

                        if (canvas.width < 2 || canvas.height < 2) {
                            continue;
                        }

                        const context = canvas.getContext('2d');
                        if (!context) {
                            throw new Error('Canvas rendering is unavailable.');
                        }

                        await page.render({
                            canvasContext: context,
                            viewport,
                        }).promise;

                        const sample = context.getImageData(0, 0, 1, 1).data;
                        if (sample[0] === 255 && sample[1] === 255 && sample[2] === 255 && sample[3] === 255) {
                            const note = document.createElement('div');
                            note.className = 'preview-overlay__sheet-note';
                            note.innerHTML = `Inline preview may be limited on this page size. <a href="${encodeURI(url)}" target="_blank" rel="noopener">Open / Download</a>.`;
                            stack.appendChild(note);
                        }

                        if (token !== previewRenderToken) {
                            return;
                        }

                        stack.appendChild(canvas);
                        page.cleanup();
                    }

                    applyPreviewZoom();
                    if (previewContent) {
                        previewContent.scrollTo({ top: 0, left: 0, behavior: 'auto' });
                    }

                    if (previewContent && previewContent.dataset.pdfScrollBound !== '1') {
                        previewContent.dataset.pdfScrollBound = '1';
                        previewContent.addEventListener('scroll', () => {
                            if (currentPreviewKind === 'pdf') {
                                const canvases = Array.from((previewBody || document).querySelectorAll('.preview-overlay__pdf-canvas'));
                                if (canvases.length === 0) {
                                    return;
                                }

                                const bodyTop = previewContent.getBoundingClientRect().top;
                                let closestPage = 1;
                                let closestDistance = Number.POSITIVE_INFINITY;

                                canvases.forEach((canvas) => {
                                    const rect = canvas.getBoundingClientRect();
                                    const distance = Math.abs(rect.top - bodyTop - 16);
                                    if (distance < closestDistance) {
                                        closestDistance = distance;
                                        closestPage = Number(canvas.dataset.pageNumber || 1);
                                    }
                                });

                                if (closestPage !== pdfPreviewState.currentPage) {
                                    pdfPreviewState.currentPage = closestPage;
                                    syncPdfControls();
                                }
                                return;
                            }

                            if (currentPreviewKind === 'docx') {
                                const pages = Array.from(previewBody?.querySelectorAll('.preview-overlay__docx .docx-wrapper section.docx') || []);
                                if (pages.length === 0) {
                                    return;
                                }

                                const bodyTop = previewContent.getBoundingClientRect().top;
                                let closestPage = 1;
                                let closestDistance = Number.POSITIVE_INFINITY;

                                pages.forEach((pageEl, index) => {
                                    const rect = pageEl.getBoundingClientRect();
                                    const distance = Math.abs(rect.top - bodyTop - 16);
                                    if (distance < closestDistance) {
                                        closestDistance = distance;
                                        closestPage = index + 1;
                                    }
                                });

                                if (closestPage !== pdfPreviewState.currentPage) {
                                    pdfPreviewState.currentPage = closestPage;
                                    pdfPreviewState.totalPages = pages.length;
                                    syncPdfControls();
                                }
                            }
                        });
                    }

                    if (pdf.numPages > pagesToRender) {
                        const note = document.createElement('div');
                        note.className = 'preview-overlay__sheet-note';
                        note.textContent = `Preview limited to first ${pagesToRender} pages for performance. Use Open/Download for full file.`;
                        stack.appendChild(note);
                    }
                };

                const renderDocxWithLibrary = async (url, token) => {
                    await loadScript(PREVIEW_LIBRARIES.jsZip);
                    await loadScript(PREVIEW_LIBRARIES.docxPreview);

                    if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                        throw new Error('DOCX renderer is unavailable.');
                    }

                    const buffer = await fetchFileBuffer(url);
                    if (token !== previewRenderToken) {
                        return;
                    }

                    previewBody.innerHTML = '<div class="preview-overlay__docx"></div>';
                    const docxHost = previewBody.querySelector('.preview-overlay__docx');
                    if (!docxHost) {
                        return;
                    }

                    await window.docx.renderAsync(buffer, docxHost, undefined, {
                        className: 'docx',
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: false,
                        breakPages: true,
                        ignoreLastRenderedPageBreak: false,
                        useBase64URL: true,
                    });

                    const pages = Array.from(previewBody.querySelectorAll('.preview-overlay__docx .docx-wrapper section.docx'));
                    pdfPreviewState.totalPages = Math.max(1, pages.length || 1);
                    pdfPreviewState.currentPage = 1;
                    previewOverlay?.classList.toggle('preview-overlay--paged', pdfPreviewState.totalPages > 1);
                    syncPdfControls();

                    if (previewContent) {
                        previewContent.scrollTo({ top: 0, left: 0, behavior: 'auto' });
                    }
                };

                const renderImageWithLimits = async (url, token, safeUrl, safeName) => {
                    const MAX_IMAGE_SIDE = 8192;
                    const MAX_IMAGE_PIXELS = 20_000_000;

                    const blob = await fetchFileBlob(url);
                    if (token !== previewRenderToken) {
                        return;
                    }

                    const blobUrl = URL.createObjectURL(blob);
                    try {
                        const image = await loadImageElement(blobUrl);
                        if (token !== previewRenderToken) {
                            return;
                        }

                        const width = Math.max(1, image.naturalWidth || image.width || 1);
                        const height = Math.max(1, image.naturalHeight || image.height || 1);
                        const sideScale = Math.min(1, MAX_IMAGE_SIDE / width, MAX_IMAGE_SIDE / height);
                        const areaScale = Math.min(1, Math.sqrt(MAX_IMAGE_PIXELS / (width * height)));
                        const scale = Math.min(1, sideScale, areaScale);

                        if (scale < 1) {
                            const canvas = document.createElement('canvas');
                            canvas.className = 'preview-overlay__image-canvas';
                            canvas.width = Math.max(1, Math.floor(width * scale));
                            canvas.height = Math.max(1, Math.floor(height * scale));

                            const context = canvas.getContext('2d');
                            if (!context) {
                                throw new Error('Canvas rendering is unavailable.');
                            }

                            context.drawImage(image, 0, 0, canvas.width, canvas.height);
                            previewBody.innerHTML = '';
                            previewBody.appendChild(canvas);
                        } else {
                            previewBody.innerHTML =
                                `<img src="${safeUrl}" alt="${safeName}" class="preview-overlay__image">`;
                        }
                    } finally {
                        URL.revokeObjectURL(blobUrl);
                    }
                };

                const renderSheetWithLibrary = async (url, token) => {
                    await loadScript(PREVIEW_LIBRARIES.sheetJs);

                    if (!window.XLSX) {
                        throw new Error('Spreadsheet renderer is unavailable.');
                    }

                    const buffer = await fetchFileBuffer(url);
                    if (token !== previewRenderToken) {
                        return;
                    }

                    const workbook = window.XLSX.read(buffer, {
                        type: 'array',
                    });
                    const firstSheetName = workbook.SheetNames?.[0];
                    if (!firstSheetName) {
                        throw new Error('Spreadsheet has no data.');
                    }

                    const rows = window.XLSX.utils.sheet_to_json(workbook.Sheets[firstSheetName], {
                        header: 1,
                        raw: false,
                        defval: '',
                    });

                    const maxRows = 250;
                    const maxCols = 30;
                    const clippedRows = rows.slice(0, maxRows).map((row) =>
                        Array.isArray(row) ? row.slice(0, maxCols) : []
                    );

                    previewBody.innerHTML = '<div class="preview-overlay__sheet-wrap"></div>';
                    const wrap = previewBody.querySelector('.preview-overlay__sheet-wrap');
                    if (!wrap) {
                        return;
                    }

                    const table = document.createElement('table');
                    table.className =
                        'table table-sm table-bordered table-striped mb-0 preview-overlay__sheet-table';

                    clippedRows.forEach((row, rowIndex) => {
                        const tr = document.createElement('tr');
                        const safeRow = row.length > 0 ? row : [''];

                        safeRow.forEach((cell) => {
                            const cellEl = document.createElement(rowIndex === 0 ? 'th' : 'td');
                            cellEl.textContent = String(cell ?? '');
                            tr.appendChild(cellEl);
                        });

                        table.appendChild(tr);
                    });

                    wrap.appendChild(table);
                    wrap.scrollLeft = 0;

                    const wasTrimmed = rows.length > maxRows || rows.some((row) => Array.isArray(row) && row
                        .length > maxCols);
                    if (wasTrimmed) {
                        const note = document.createElement('div');
                        note.className = 'preview-overlay__sheet-note';
                        note.textContent = `Preview limited to first ${maxRows} rows and ${maxCols} columns.`;
                        wrap.appendChild(note);
                    }

                    const horizontalHint = document.createElement('div');
                    horizontalHint.className = 'preview-overlay__sheet-note';
                    horizontalHint.textContent = 'Tip: Hold Shift while scrolling to move horizontally across columns.';
                    wrap.appendChild(horizontalHint);
                };

                const renderPreview = async (url, kind, filename, ext) => {
                    if (!previewBody || !previewTitle) {
                        return;
                    }

                    const safeUrl = encodeURI(url);
                    const safeName = escapeHtml(filename || 'Preview');
                    const safeExt = escapeHtml((ext || '').toUpperCase());
                    const token = ++previewRenderToken;
                    previewTitle.textContent = filename || 'Preview';

                    try {
                        if (kind === 'image') {
                            currentPreviewKind = 'image';
                            pdfPreviewState.zoom = 1;
                            setPreviewControlsMode('zoom');
                            previewOverlay?.classList.remove('preview-overlay--sheet');
                            await renderImageWithLimits(url, token, safeUrl, safeName);
                            applyPreviewZoom();
                            revealPdfControlsTemporarily();
                            return;
                        }

                        if (kind === 'pdf') {
                            currentPreviewKind = 'pdf';
                            previewOverlay?.classList.remove('preview-overlay--sheet');
                            showPreviewLoading('Rendering PDF preview...');
                            await renderPdfWithPdfJs(url, token);
                            return;
                        }

                        if (kind === 'docx') {
                            currentPreviewKind = 'docx';
                            pdfPreviewState.zoom = 1;
                            setPreviewControlsMode('docx');
                            previewOverlay?.classList.remove('preview-overlay--sheet');
                            showPreviewLoading('Rendering DOCX preview...');
                            await renderDocxWithLibrary(url, token);
                            applyPreviewZoom();
                            revealPdfControlsTemporarily();
                            return;
                        }

                        if (kind === 'sheet') {
                            currentPreviewKind = 'sheet';
                            pdfPreviewState.zoom = 1;
                            setPreviewControlsMode('zoom');
                            previewOverlay?.classList.add('preview-overlay--sheet');
                            showPreviewLoading('Rendering spreadsheet preview...');
                            await renderSheetWithLibrary(url, token);
                            applyPreviewZoom();
                            revealPdfControlsTemporarily();
                            return;
                        }

                        currentPreviewKind = '';
                        setPreviewControlsMode('none');

                        previewBody.innerHTML = `
                            <div class="preview-overlay__unsupported">
                                <div class="mb-2">Inline preview is not available for <strong>${safeExt || 'this format'}</strong>.</div>
                                <a href="${safeUrl}" class="btn btn-brand btn-sm" target="_blank" rel="noopener">Open / Download</a>
                            </div>
                        `;
                    } catch (error) {
                        currentPreviewKind = '';
                        setPreviewControlsMode('none');
                        if (token !== previewRenderToken) {
                            return;
                        }

                        previewBody.innerHTML = `
                            <div class="preview-overlay__unsupported">
                                <div class="mb-2">Preview could not be rendered for <strong>${safeExt || 'this format'}</strong>.</div>
                                <div class="small text-muted mb-3">Open or download the file as a fallback.</div>
                                <a href="${safeUrl}" class="btn btn-brand btn-sm" target="_blank" rel="noopener">Open / Download</a>
                            </div>
                        `;
                    }
                };

                const bindPreviewButtons = () => {
                    document.querySelectorAll('[data-preview-trigger]').forEach((button) => {
                        if (button.dataset.previewBound === '1') {
                            return;
                        }

                        button.dataset.previewBound = '1';
                        button.addEventListener('click', () => {
                            const url = button.getAttribute('data-preview-url');
                            const kind = button.getAttribute('data-preview-kind') || 'unknown';
                            const filename = button.getAttribute('data-preview-name') || 'Preview';
                            const ext = button.getAttribute('data-preview-ext') || '';

                            if (!url) {
                                return;
                            }

                            currentPreviewUrl = url;
                            if (previewDownloadBtn) {
                                previewDownloadBtn.href = url;
                                previewDownloadBtn.setAttribute('download', filename);
                            }

                            openPreview();
                            renderPreview(url, kind, filename, ext);
                        });
                    });
                };

                const bindActionDropdownZIndexFix = () => {
                    document.querySelectorAll('.doc-table [data-bs-toggle="dropdown"]').forEach((toggle) => {
                        if (toggle.dataset.rowDropdownBound === '1') {
                            return;
                        }

                        toggle.dataset.rowDropdownBound = '1';
                        const row = toggle.closest('tr');
                        if (!row) {
                            return;
                        }

                        toggle.addEventListener('show.bs.dropdown', () => {
                            row.classList.add('row-dropdown-open');
                        });

                        toggle.addEventListener('hide.bs.dropdown', () => {
                            row.classList.remove('row-dropdown-open');
                        });
                    });
                };

                const bindLiveSearch = () => {
                    const form = document.querySelector('[data-doc-live-search-form]');
                    const input = form?.querySelector('[data-doc-live-search-input]');
                    const globalSearchInput = form?.querySelector('[data-doc-global-search]');
                    const globalSearchToggle = form?.querySelector('[data-doc-global-search-toggle]');
                    const scopeLabel = form?.querySelector('[data-doc-search-scope]');
                    const suggestionPanel = form?.querySelector('[data-doc-search-suggestions]');
                    const suggestionResults = form?.querySelector('[data-doc-search-results]');
                    const viewAllButton = form?.querySelector('[data-doc-search-view-all]');
                    const suggestionUrl = form?.getAttribute('data-doc-suggest-url') || '';
                    let suggestionRequestId = 0;
                    let activeSuggestionRequestId = 0;
                    let suggestionAbortController = null;

                    if (!form || !input || input.dataset.liveSearchBound === '1') {
                        return;
                    }

                    input.dataset.liveSearchBound = '1';
                    let suggestionTimer = null;

                    const hideSuggestions = () => {
                        if (suggestionPanel) {
                            suggestionPanel.classList.add('d-none');
                        }
                        if (suggestionResults) {
                            suggestionResults.innerHTML = '';
                        }
                    };

                    const showSuggestions = () => {
                        if (suggestionPanel) {
                            suggestionPanel.classList.remove('d-none');
                        }
                    };

                    const buildSearchUrl = () => {
                        const url = new URL(form.action, window.location.origin);
                        const formData = new FormData(form);

                        for (const [key, value] of formData.entries()) {
                            if (key === 'global_search') {
                                continue;
                            }

                            if (typeof value !== 'string') {
                                continue;
                            }

                            const trimmed = value.trim();
                            if (trimmed !== '') {
                                url.searchParams.set(key, trimmed);
                            }
                        }

                        return url;
                    };

                    const renderSuggestions = (items) => {
                        if (!suggestionResults) {
                            return;
                        }

                        if (!Array.isArray(items) || items.length === 0) {
                            suggestionResults.innerHTML =
                                '<div class="px-3 py-3 text-muted small">No quick matches found.</div>';
                            showSuggestions();
                            return;
                        }

                        suggestionResults.innerHTML = items.map((item) => {
                            const title = escapeHtml(item?.title || 'Untitled');
                            const department = escapeHtml(item?.department_name || 'Unknown department');
                            const folder = escapeHtml(item?.folder_label || 'Root');
                            const updatedAt = escapeHtml(item?.updated_at || '');
                            const targetUrl = escapeHtml(item?.url || '#');

                            return `
                                <button type="button" class="doc-command-search__result-item" data-doc-search-url="${targetUrl}">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div class="min-w-0">
                                            <div class="doc-command-search__result-title text-truncate">${title}</div>
                                            <div class="doc-command-search__result-meta">${department} • ${folder}</div>
                                        </div>
                                        <div class="doc-command-search__result-meta text-nowrap">${updatedAt}</div>
                                    </div>
                                </button>
                            `;
                        }).join('');

                        showSuggestions();
                    };

                    const fetchSuggestions = async () => {
                        const query = input.value.trim();
                        if (query === '' || !suggestionUrl) {
                            hideSuggestions();
                            return;
                        }

                        const requestId = ++suggestionRequestId;
                        activeSuggestionRequestId = requestId;

                        if (suggestionAbortController) {
                            suggestionAbortController.abort();
                        }

                        const abortController = new AbortController();
                        suggestionAbortController = abortController;

                        try {
                            const url = new URL(suggestionUrl, window.location.origin);
                            url.searchParams.set('q', query);
                            url.searchParams.set('department_id', form.querySelector('[data-doc-search-department]')?.value || '');
                            url.searchParams.set('global_search', globalSearchInput?.value === '1' ? '1' : '0');

                            const response = await fetch(url.toString(), {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                signal: abortController.signal,
                            });

                            if (!response.ok) {
                                throw new Error('Suggestion request failed');
                            }

                            const payload = await response.json().catch(() => ({
                                results: [],
                            }));

                            if (requestId !== activeSuggestionRequestId) {
                                return;
                            }

                            renderSuggestions(payload.results || []);
                        } catch (error) {
                            if (error?.name === 'AbortError') {
                                return;
                            }

                            hideSuggestions();
                        } finally {
                            if (suggestionAbortController === abortController) {
                                suggestionAbortController = null;
                            }
                        }
                    };

                    const syncGlobalToggleState = () => {
                        if (!globalSearchInput || !globalSearchToggle) {
                            return;
                        }

                        const enabled = globalSearchInput.value === '1';
                        globalSearchToggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
                        globalSearchToggle.classList.toggle('btn-danger', enabled);
                        globalSearchToggle.classList.toggle('text-white', enabled);
                        globalSearchToggle.classList.toggle('btn-outline-secondary', !enabled);

                        if (scopeLabel) {
                            scopeLabel.textContent = enabled
                                ? 'Quick suggestions across all accessible departments'
                                : 'Searching within this department';
                        }
                    };

                    const triggerSearch = () => {
                        liveSearchState.shouldRefocus = document.activeElement === input;
                        liveSearchState.caretStart = input.selectionStart;
                        liveSearchState.caretEnd = input.selectionEnd;
                        hideSuggestions();
                        reloadExplorerFromUrl(buildSearchUrl().toString());
                    };

                    syncGlobalToggleState();

                    if (globalSearchToggle && globalSearchInput && globalSearchToggle.dataset.liveSearchBound !== '1') {
                        globalSearchToggle.dataset.liveSearchBound = '1';
                        globalSearchToggle.addEventListener('click', () => {
                            globalSearchInput.value = globalSearchInput.value === '1' ? '0' : '1';
                            syncGlobalToggleState();
                            input.focus({
                                preventScroll: true,
                            });
                            clearTimeout(suggestionTimer);
                            suggestionTimer = setTimeout(fetchSuggestions, 150);
                        });
                    }

                    input.addEventListener('input', () => {
                        clearTimeout(suggestionTimer);

                        if (input.value.trim() === '') {
                            hideSuggestions();
                            triggerSearch();
                            return;
                        }

                        suggestionTimer = setTimeout(fetchSuggestions, 170);
                    });

                    input.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            hideSuggestions();
                        }

                        if (event.key === 'ArrowDown') {
                            const firstItem = suggestionResults?.querySelector('[data-doc-search-url]');
                            if (firstItem) {
                                event.preventDefault();
                                firstItem.focus();
                            }
                        }
                    });

                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        clearTimeout(suggestionTimer);
                        triggerSearch();
                    });

                    if (viewAllButton && viewAllButton.dataset.liveSearchBound !== '1') {
                        viewAllButton.dataset.liveSearchBound = '1';
                        viewAllButton.addEventListener('click', () => {
                            triggerSearch();
                        });
                    }

                    if (suggestionResults && suggestionResults.dataset.liveSearchBound !== '1') {
                        suggestionResults.dataset.liveSearchBound = '1';

                        suggestionResults.addEventListener('click', (event) => {
                            const button = event.target.closest('[data-doc-search-url]');
                            if (!button) {
                                return;
                            }

                            const targetUrl = button.getAttribute('data-doc-search-url');
                            if (targetUrl) {
                                window.location.assign(targetUrl);
                            }
                        });

                        suggestionResults.addEventListener('keydown', (event) => {
                            const current = event.target.closest('[data-doc-search-url]');
                            if (!current) {
                                return;
                            }

                            if (event.key === 'ArrowDown') {
                                event.preventDefault();
                                const next = current.nextElementSibling?.matches('[data-doc-search-url]')
                                    ? current.nextElementSibling
                                    : current;
                                next.focus();
                            } else if (event.key === 'ArrowUp') {
                                event.preventDefault();
                                const prev = current.previousElementSibling?.matches('[data-doc-search-url]')
                                    ? current.previousElementSibling
                                    : null;
                                if (prev) {
                                    prev.focus();
                                } else {
                                    input.focus({
                                        preventScroll: true,
                                    });
                                }
                            } else if (event.key === 'Enter') {
                                event.preventDefault();
                                const targetUrl = current.getAttribute('data-doc-search-url');
                                if (targetUrl) {
                                    window.location.assign(targetUrl);
                                }
                            }
                        });
                    }

                    if (form.dataset.liveSearchOutsideBound !== '1') {
                        form.dataset.liveSearchOutsideBound = '1';
                        document.addEventListener('click', (event) => {
                            if (!form.contains(event.target)) {
                                hideSuggestions();
                            }
                        });
                    }
                };

                // Extended modal setup logic
                const bindModals = () => {
                    const renameModal = document.getElementById('renameFolderModal');
                    if (renameModal) {
                        renameModal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget;
                            if (button && button.hasAttribute('data-folder-name')) {
                                document.getElementById('rename_folder_name').value = button.getAttribute(
                                    'data-folder-name');
                            }
                            if (button && button.hasAttribute('data-folder-code')) {
                                document.getElementById('rename_folder_code').value = button.getAttribute(
                                    'data-folder-code');
                            } else {
                                document.getElementById('rename_folder_code').value = '';
                            }
                            if (button && button.hasAttribute('data-folder-action')) {
                                document.getElementById('global-rename-folder-form').action = button
                                    .getAttribute('data-folder-action');
                            }
                        });
                    }
                    const deleteModal = document.getElementById('deleteFolderModal');
                    if (deleteModal) {
                        deleteModal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget;
                            if (button && button.hasAttribute('data-folder-name')) {
                                document.getElementById('delete_folder_name_display').textContent = button
                                    .getAttribute('data-folder-name');
                            }
                            if (button && button.hasAttribute('data-folder-action')) {
                                document.getElementById('global-delete-folder-form').action = button
                                    .getAttribute('data-folder-action');
                            }
                        });
                    }
                    const archiveModal = document.getElementById('archiveDocumentModal');
                    if (archiveModal) {
                        archiveModal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget;
                            if (button && button.hasAttribute('data-doc-name')) {
                                document.getElementById('archive_document_name_display').textContent = button
                                    .getAttribute('data-doc-name');
                                document.getElementById('archiveDocumentForm').action = button.getAttribute(
                                    'data-doc-url');
                            }
                        });
                    }
                };
                bindModals();
                bindActionDropdownZIndexFix();
                bindLiveSearch();

                // Loading State Handlers
                document.querySelectorAll('form[data-loading-target], #archiveDocumentForm').forEach(form => {
                    form.addEventListener('submit', function() {
                        const btn = this.querySelector('.btn-submit-loading');
                        if (btn && !btn.disabled) {
                            if (!btn.dataset.originalHTML) btn.dataset.originalHTML = btn.innerHTML;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
                            btn.disabled = true;
                            setTimeout(() => {
                                btn.innerHTML = btn.dataset.originalHTML;
                                btn.disabled = false;
                            }, 5000);
                        }
                    });
                });

                const bindFolderCreateUi = () => {
                    const createModal = document.getElementById('createDepartmentFolderModal');
                    const createForm = createModal?.querySelector('[data-folder-create-form]');
                    const nameInput = createForm?.querySelector('[data-folder-create-name]');
                    const departmentInput = createForm?.querySelector('[data-folder-create-department-id]');
                    const parentInput = createForm?.querySelector('[data-folder-create-parent-id]');
                    const scopeLabel = createForm?.querySelector('[data-folder-create-scope]');

                    if (!createModal || !createForm || !nameInput || createModal.dataset.createBound === '1') {
                        return;
                    }

                    createModal.dataset.createBound = '1';

                    createModal.addEventListener('show.bs.modal', (event) => {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        const departmentId = trigger.getAttribute('data-folder-department-id') || '';
                        const parentId = trigger.getAttribute('data-folder-parent-id') || '';
                        const scopeText = trigger.getAttribute('data-folder-create-scope') ||
                            'New folder at root level';

                        if (departmentInput) {
                            departmentInput.value = departmentId;
                        }

                        if (parentInput) {
                            parentInput.value = parentId;
                        }

                        if (scopeLabel) {
                            scopeLabel.textContent = scopeText;
                        }
                    });

                    createModal.addEventListener('shown.bs.modal', () => {
                        nameInput.focus();
                    });

                    createModal.addEventListener('hidden.bs.modal', () => {
                        nameInput.value = '';
                    });
                };

                const bindFolderAjaxForms = () => {
                    document.querySelectorAll('form[data-ajax-folder]').forEach((form) => {
                        if (form.dataset.ajaxBound === '1') {
                            return;
                        }

                        form.dataset.ajaxBound = '1';
                        form.addEventListener('submit', (event) => {
                            event.preventDefault();
                            submitFolderFormAjax(form);
                        });
                    });
                };

                const reloadExplorerFromUrl = async (url) => {
                    if (!url) {
                        return;
                    }

                    const normalizedUrl = String(url).replace(/&amp;/g, '&');
                    const requestId = ++liveSearchState.requestId;
                    liveSearchState.activeRequestId = requestId;

                    if (liveSearchState.abortController) {
                        liveSearchState.abortController.abort();
                    }

                    const abortController = new AbortController();
                    liveSearchState.abortController = abortController;

                    try {
                        const response = await fetch(normalizedUrl, {
                            headers: {
                                Accept: 'text/html,application/xhtml+xml',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            signal: abortController.signal,
                        });

                        if (!response.ok) {
                            throw new Error('Reload request failed');
                        }

                        const html = await response.text();
                        const parser = new DOMParser();
                        const nextDoc = parser.parseFromString(html, 'text/html');
                        const nextExplorer = nextDoc.getElementById('department-document-explorer');

                        if (requestId !== liveSearchState.activeRequestId) {
                            return;
                        }

                        if (!nextExplorer || !explorer || !explorer.parentElement) {
                            window.location.assign(normalizedUrl);
                            return;
                        }

                        explorer.replaceWith(nextExplorer);
                        explorer = nextExplorer;
                        window.history.replaceState({}, '', normalizedUrl);
                        bindFolderCreateUi();
                        bindFolderAjaxForms();
                        bindPreviewButtons();
                        bindPreviewDoubleClick();
                        bindActionDropdownZIndexFix();
                        bindLiveSearch();

                        if (liveSearchState.shouldRefocus) {
                            const nextInput = explorer.querySelector('[data-doc-live-search-input]');
                            if (nextInput) {
                                window.requestAnimationFrame(() => {
                                    nextInput.focus({
                                        preventScroll: true,
                                    });
                                    const start = liveSearchState.caretStart;
                                    const end = liveSearchState.caretEnd;
                                    const fallbackPos = nextInput.value.length;
                                    nextInput.setSelectionRange(
                                        typeof start === 'number' ? Math.min(start, fallbackPos) : fallbackPos,
                                        typeof end === 'number' ? Math.min(end, fallbackPos) : fallbackPos
                                    );
                                });
                            }

                            liveSearchState.shouldRefocus = false;
                            liveSearchState.caretStart = null;
                            liveSearchState.caretEnd = null;
                        }
                    } catch (error) {
                        if (error?.name === 'AbortError') {
                            return;
                        }

                        window.location.assign(normalizedUrl);
                    } finally {
                        if (liveSearchState.abortController === abortController) {
                            liveSearchState.abortController = null;
                        }
                    }
                };

                const closeAllFolderModals = () => {
                    const modalIds = ['createDepartmentFolderModal', 'renameFolderModal', 'deleteFolderModal'];
                    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

                    modalIds.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) {
                            const instance = bootstrap.Modal.getInstance(el);
                            if (instance) instance.hide();
                        }
                    });
                };

                const submitFolderFormAjax = async (form) => {
                    const formData = new FormData(form);

                    disableSubmit(form, true);
                    clearFlash();

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                            credentials: 'same-origin',
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || payload.ok === false) {
                            showFlash(payload.message || 'Folder action failed.', 'error');
                            return;
                        }

                        closeAllFolderModals();
                        showFlash(payload.message || 'Folder updated successfully.');
                        await reloadExplorerFromUrl(payload.redirect_url || window.location.href);
                    } catch (error) {
                        showFlash('Folder action failed. Please try again.', 'error');
                    } finally {
                        disableSubmit(form, false);
                    }
                };

                bindFolderCreateUi();
                bindFolderAjaxForms();
                bindPreviewButtons();
                bindPreviewDoubleClick();
                clearOneTimeDocumentFocusParam();

                document.querySelectorAll('[data-preview-close]').forEach((button) => {
                    button.addEventListener('click', (e) => {
                        if (e.target === e.currentTarget) {
                            closePreview();
                        }
                    });
                });

                if (previewPrintBtn) {
                    previewPrintBtn.addEventListener('click', () => {
                        if (!currentPreviewUrl) return;
                        const printWin = window.open(currentPreviewUrl, '_blank');
                        if (printWin) {
                            printWin.addEventListener('load', () => {
                                printWin.print();
                            });
                        }
                    });
                }

                if (previewDownloadBtn && previewDownloadBtn.dataset.bound !== '1') {
                    previewDownloadBtn.dataset.bound = '1';
                    previewDownloadBtn.addEventListener('click', async (event) => {
                        event.preventDefault();
                        if (!currentPreviewUrl) {
                            return;
                        }

                        const downloadName = previewDownloadBtn.getAttribute('download') || 'download';

                        try {
                            const response = await fetch(currentPreviewUrl, {
                                credentials: 'same-origin',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Download request failed.');
                            }

                            const blob = await response.blob();
                            const blobUrl = URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = blobUrl;
                            link.download = downloadName;
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                            window.setTimeout(() => URL.revokeObjectURL(blobUrl), 1500);
                        } catch (error) {
                            window.location.assign(currentPreviewUrl);
                        }
                    });
                }

                if (previewZoomInBtn) {
                    previewZoomInBtn.addEventListener('click', () => {
                        pdfPreviewState.zoom = Math.min(pdfPreviewState.maxZoom, Number((pdfPreviewState.zoom + 0.1).toFixed(2)));
                        applyPreviewZoom();
                    });
                }

                if (previewZoomOutBtn) {
                    previewZoomOutBtn.addEventListener('click', () => {
                        pdfPreviewState.zoom = Math.max(pdfPreviewState.minZoom, Number((pdfPreviewState.zoom - 0.1).toFixed(2)));
                        applyPreviewZoom();
                    });
                }

                if (previewZoomResetBtn) {
                    previewZoomResetBtn.addEventListener('click', () => {
                        pdfPreviewState.zoom = 1;
                        applyPreviewZoom();
                    });
                }

                if (previewPageInput) {
                    previewPageInput.addEventListener('keydown', (event) => {
                        if (event.key !== 'Enter') {
                            return;
                        }

                        event.preventDefault();
                        const requestedPage = Number.parseInt(previewPageInput.value, 10);
                        if (!Number.isFinite(requestedPage)) {
                            syncPdfControls();
                            return;
                        }

                        if (currentPreviewKind === 'pdf') {
                            scrollPdfToPage(requestedPage);
                            return;
                        }

                        if (currentPreviewKind === 'docx') {
                            scrollDocxToPage(requestedPage);
                            return;
                        }

                        syncPdfControls();
                    });
                }

                if (previewOverlay && previewOverlay.dataset.previewMouseBound !== '1') {
                    previewOverlay.dataset.previewMouseBound = '1';
                    previewOverlay.addEventListener('mousemove', revealPdfControlsTemporarily);
                    previewOverlay.addEventListener('mouseenter', revealPdfControlsTemporarily);
                }

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closePreview();
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
