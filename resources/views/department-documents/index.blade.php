<x-app-layout>
    @php
        $selectedDepartment = $departments->firstWhere('id', $selectedDepartmentId);
        $rootFolders = $foldersByParent->get(0, collect());
        $canManageFolders = auth()->user()->hasRole('admin', 'encoder') && $selectedDepartmentId > 0;
        $canCreateFolders = auth()->user()->hasRole('admin', 'encoder') && $selectedDepartmentId > 0;
        $canEditDeleteFolders = auth()->user()->isAdmin() && $selectedDepartmentId > 0;
        $canUploadAndEdit = auth()->user()->hasRole('admin', 'encoder');
        $canUploadInCurrentContext = $canUploadAndEdit && (int) ($currentFolderId ?? 0) > 0;

        $activePathIds = isset($folderBreadcrumbs) ? $folderBreadcrumbs->pluck('id')->toArray() : [];
        if ($currentFolderId) {
            $activePathIds[] = $currentFolderId;
        }

        $currentFolderHistoryUrl = $currentFolder
            ? route('department-documents.folders.update-history', $currentFolder)
            : null;
    @endphp

    <div class="animate-fade-in stagger-1 mt-4">
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
            <div class="explorer-grid mb-0" id="department-document-explorer">
                <aside class="explorer-sidebar">
                    <div class="explorer-sidebar__inner">
                        <div class="doc-sidebar-department">{{ $selectedDepartment?->name ?? 'No Department' }}</div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem; letter-spacing: 0.02em;">FOLDERS</h6>
                            <div class="d-flex align-items-center gap-1">
                                {{-- Root Navigation --}}
                                @if ($selectedDepartmentId)
                                    <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId]) }}"
                                        class="btn-sidebar-nav" 
                                        title="Back to Root">
                                        <i class="fas fa-house"></i>
                                    </a>
                                @endif

                                {{-- Folder/Root Update History --}}
                                @if ($currentFolder && $currentFolderHistoryUrl)
                                    <button type="button" class="btn-sidebar-nav btn-sidebar-nav--history"
                                        data-folder-history-trigger
                                        data-folder-name="{{ $currentFolder->name }}"
                                        data-folder-history-url="{{ $currentFolderHistoryUrl }}"
                                        title="View Folder Activity">
                                        <i class="fas fa-clock-rotate-left"></i>
                                    </button>
                                @endif

                                @if (!$currentFolder && auth()->user()->hasRole('admin') && $selectedDepartmentId > 0)
                                    <button type="button" class="btn-sidebar-nav btn-sidebar-nav--history"
                                        data-folder-history-trigger
                                        data-folder-name="{{ $selectedDepartmentName }} Activity"
                                        data-folder-history-url="{{ route('department-documents.root-update-history', ['department_id' => $selectedDepartmentId]) }}"
                                        title="View Department Activity">
                                        <i class="fas fa-clock-rotate-left"></i>
                                    </button>
                                @endif

                                {{-- Create Folder --}}
                                @if ($canCreateFolders)
                                    <button type="button" class="btn-sidebar-nav"
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
                </aside>

                <div class="explorer-main d-flex flex-column gap-3">
                    <div class="doc-command-search-row">
                        <form action="{{ route('department-documents.index') }}" method="GET"
                            class="doc-command-search mb-0" data-doc-live-search-form
                            data-doc-suggest-url="{{ route('department-documents.search') }}">
                            <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}" data-doc-search-department>
                            <input type="hidden" name="global_search" value="{{ request('global_search') ? '1' : '0' }}" data-doc-global-search>
                            @if (request('document_folder_id'))
                                <input type="hidden" name="document_folder_id" value="{{ (int) request('document_folder_id') }}">
                            @endif

                            <div class="doc-command-search__shell">
                                <div class="doc-command-search__bar">
                                    <i class="fas fa-search doc-command-search__icon"></i>
                                    <input type="text" name="search" class="doc-command-search__input" data-doc-live-search-input
                                        placeholder="Search documents and folders" value="{{ request('search') }}" autocomplete="off">
                                    <button type="button" class="doc-command-search__clear d-none" data-doc-search-clear
                                        aria-label="Clear search" title="Clear search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button type="button"
                                        class="doc-command-search__global {{ request('global_search') ? 'is-active' : '' }}"
                                        data-doc-global-search-toggle aria-pressed="{{ request('global_search') ? 'true' : 'false' }}"
                                        aria-label="Global search"
                                        title="Search all accessible departments">
                                        <i class="fas fa-globe"></i>
                                    </button>
                                </div>

                                <div class="doc-command-search__results d-none" data-doc-search-suggestions>
                                    <div class="doc-command-search__results-inner" data-doc-search-results></div>
                                </div>
                            </div>

                            <div class="doc-command-search__scope text-muted" data-doc-search-scope>
                                {{ request('global_search') ? 'Quick suggestions across all accessible departments' : 'Searching within this department' }}
                            </div>

                        </form>

                        @if($canUploadInCurrentContext)
                            <button type="button"
                                class="btn btn-accent-red btn-sm px-3 shadow-sm d-inline-flex align-items-center gap-2 fw-medium doc-upload-btn"
                                data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                <i class="fas fa-cloud-upload-alt"></i> Upload
                            </button>
                        @endif
                    </div>

                    <div class="card doc-list-card">
                        <div class="doc-table-context">
                            <div class="explorer-breadcrumb mb-0">
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
                        
                        @php
                            $subfolders = $currentFolderId ? $foldersByParent->get((int) $currentFolderId, collect()) : $foldersByParent->get(0, collect());
                        @endphp

                        <div class="doc-table-wrapper border-0 {{ ($documents->count() === 0 && $subfolders->count() === 0) ? 'is-empty' : '' }}">
                        @if ($subfolders->isNotEmpty())
                            <div class="mb-4 pb-2 border-bottom">
                                <h6 class="text-muted fw-bold small text-uppercase px-3 pt-3 pb-2 mb-0 d-flex align-items-center gap-2">
                                    <i class="fas fa-sitemap text-secondary"></i> Subfolders
                                </h6>
                                <table class="doc-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Folder Name</th>
                                            <th>Folder Code</th>
                                            <th>Physical Location</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subfolders as $sub)
                                            <tr class="animate-fade-in" style="background-color: #fafbfc;">
                                                <td>
                                                    <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId, 'document_folder_id' => $sub->id]) }}" 
                                                       class="d-flex align-items-center gap-3 text-decoration-none">
                                                        <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0;">
                                                            <i class="fas fa-folder text-danger fs-6"></i>
                                                        </div>
                                                        <div class="d-flex flex-column overflow-hidden">
                                                            <span class="fw-bold text-dark text-truncate d-block">{{ $sub->name }}</span>
                                                        </div>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="text-secondary fw-semibold">{{ $sub->folder_code ?? '—' }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-secondary">{{ $sub->documentLocation?->name ?? '—' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if (($canManageFolders ?? false) || ($canEditDeleteFolders ?? false))
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none shadow-none"
                                                                type="button" data-bs-toggle="dropdown" 
                                                                data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}'
                                                                aria-expanded="false" title="Folder actions">
                                                                <i class="fas fa-ellipsis-v px-2 py-1"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                                <li>
                                                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                                                        data-folder-history-trigger
                                                                        data-folder-name="{{ $sub->name }}"
                                                                        data-folder-history-url="{{ route('department-documents.folders.update-history', $sub) }}">
                                                                        <i class="fas fa-history text-secondary" style="width: 16px;"></i><span class="fw-medium">Update History</span>
                                                                    </button>
                                                                </li>
                                                                @if ($canEditDeleteFolders ?? false)
                                                                    <li>
                                                                        <hr class="dropdown-divider opacity-50 my-1">
                                                                    </li>
                                                                    <li>
                                                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                                                            data-bs-toggle="modal" data-bs-target="#renameFolderModal"
                                                                            data-folder-name="{{ $sub->name }}" data-folder-code="{{ $sub->folder_code }}"
                                                                            data-folder-location-id="{{ $sub->document_location_id }}"
                                                                            data-folder-action="{{ route('department-documents.folders.update', $sub) }}">
                                                                            <i class="fas fa-pen text-secondary" style="width: 16px;"></i><span class="fw-medium">Edit Folder</span>
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        <hr class="dropdown-divider opacity-50 my-1">
                                                                    </li>
                                                                    <li>
                                                                        <button type="button" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2"
                                                                            data-bs-toggle="modal" data-bs-target="#deleteFolderModal"
                                                                            data-folder-name="{{ $sub->name }}"
                                                                            data-folder-action="{{ route('department-documents.folders.destroy', $sub) }}">
                                                                            <i class="fas fa-trash" style="width: 16px;"></i><span class="fw-medium">Delete Folder</span>
                                                                        </button>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($documents->count() > 0)
                            <h6 class="text-muted fw-bold small text-uppercase px-3 pt-3 pb-2 mb-0 d-flex align-items-center gap-2">
                                <i class="fas fa-file-alt text-secondary"></i> Documents
                            </h6>
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Resource</th>
                                        <th>Department & Type</th>
                                        <th>Folder Code</th>
                                        <th>Physical Location</th>
                                        <th>Expiry</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $index => $doc)
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
                                            $receivedOnDisplay = $doc->date_received?->format('F d, Y') ?? '-';
                                            $receivedOnInput = $doc->date_received?->format('Y-m-d') ?? '';
                                            $updatedDateDisplay = $doc->updated_at?->format('M d, Y') ?? '-';
                                            $updatedTimeDisplay = $doc->updated_at?->format('h:i A') ?? '-';
                                            $fileSizeDisplay = number_format(($doc->file_size_bytes ?? 0) / 1024, 2);

                                            $expiryDate = $doc->expiry_date;
                                            $isExpired = $doc->is_expired;
                                            $isExpiringSoon = $doc->isExpiringSoon(30);

                                            if (!$expiryDate) {
                                                $expiryStatus = 'N/A';
                                                $expiryDisplay = 'N/A';
                                            } elseif ($isExpired) {
                                                $expiryStatus = 'Expired';
                                                $expiryDisplay = $expiryDate->format('M d, Y');
                                            } elseif ($isExpiringSoon) {
                                                $expiryStatus = 'Expiring soon';
                                                $expiryDisplay = $expiryDate->format('M d, Y');
                                            } else {
                                                $expiryStatus = 'Valid';
                                                $expiryDisplay = $expiryDate->format('M d, Y');
                                            }

                                            $expiryInput = $expiryDate?->format('Y-m-d') ?? '';
                                        @endphp
                                        <tr class="animate-fade-in stagger-{{ ($index % 5) + 1 }} {{ $isSelectedSearchDoc ? 'doc-row--selected' : '' }}" data-document-row-id="{{ $doc->id }}">
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
                                                        <button type="button"
                                                            class="btn btn-link p-0 fw-bold text-dark d-inline-flex align-items-center gap-2 text-break text-start text-decoration-none doc-detail-trigger"
                                                            data-doc-detail-trigger
                                                            data-doc-id="{{ $doc->id }}"
                                                            data-doc-name="{{ $doc->original_filename }}"
                                                            data-doc-department="{{ $doc->department->name }}"
                                                            data-doc-type="{{ $doc->documentType->name }}"
                                                            data-doc-folder="{{ $docFolderPath }}"
                                                            data-doc-folder-code="{{ $docFolderCode ?? '—' }}"
                                                            data-doc-location="{{ $doc->documentLocation?->name ?? '—' }}"
                                                            data-doc-uploader="{{ $doc->uploader?->name ?? 'Unknown' }}"
                                                            data-doc-size="{{ $fileSizeDisplay }}"
                                                            data-doc-ext="{{ strtoupper($ext) }}"
                                                            data-doc-received="{{ $receivedOnDisplay }}"
                                                            data-doc-expiry-status="{{ $expiryStatus }}"
                                                            data-doc-expiry-date="{{ $expiryDisplay }}"
                                                            data-doc-updated-date="{{ $updatedDateDisplay }}"
                                                            data-doc-updated-time="{{ $updatedTimeDisplay }}"
                                                            data-doc-updated-by="{{ $doc->uploader?->name ?? 'Unknown' }}"
                                                            data-doc-history-url="{{ route('department-documents.update-history', $doc) }}">
                                                            {{ $doc->original_filename }}
                                                        </button>
                                                        <div class="text-muted">{{ strtoupper($ext) }} &bull;
                                                            {{ $fileSizeDisplay }}
                                                            KB</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-dark fw-medium">{{ $doc->department->name }}
                                                </div>
                                                <div class="text-muted text-uppercase mt-1">
                                                    {{ $doc->documentType->name }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold font-monospace">
                                                    {{ $docFolderCode ?? '—' }}</div>
                                                <div class="text-muted mt-1">{{ $docFolderPath }}</div>
                                            </td>
                                            <td>
                                                <div class="text-muted mt-1">
                                                    {{ $doc->documentLocation?->name ?? '—' }}</div>
                                            </td>
                                            <td>
                                                @if (!$expiryDate)
                                                    <span class="badge badge-soft-secondary">N/A</span>
                                                @elseif($isExpired)
                                                    <span class="badge badge-soft-danger">Expired</span>
                                                    <div class="text-muted x-small mt-1">{{ $expiryDate->format('M d, Y') }}</div>
                                                @elseif($isExpiringSoon)
                                                    <span class="badge badge-soft-warning">Expiring soon</span>
                                                    <div class="text-muted x-small mt-1">{{ $expiryDate->format('M d, Y') }}</div>
                                                @else
                                                    <span class="badge badge-soft-success">Valid</span>
                                                    <div class="text-muted x-small mt-1">{{ $expiryDate->format('M d, Y') }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none shadow-none"
                                                        type="button" data-bs-toggle="dropdown" 
                                                        data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}'
                                                        aria-expanded="false" title="Document actions">
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
                                                                data-doc-detail-trigger
                                                                data-doc-id="{{ $doc->id }}"
                                                                data-doc-name="{{ $doc->original_filename }}"
                                                                data-doc-department="{{ $doc->department->name }}"
                                                                data-doc-type="{{ $doc->documentType->name }}"
                                                                data-doc-folder="{{ $docFolderPath }}"
                                                                data-doc-folder-code="{{ $docFolderCode ?? '—' }}"
                                                                data-doc-location="{{ $doc->documentLocation?->name ?? '—' }}"
                                                                data-doc-uploader="{{ $doc->uploader?->name ?? 'Unknown' }}"
                                                                data-doc-size="{{ $fileSizeDisplay }}"
                                                                data-doc-ext="{{ strtoupper($ext) }}"
                                                                data-doc-received="{{ $receivedOnDisplay }}"
                                                                data-doc-expiry-status="{{ $expiryStatus }}"
                                                                data-doc-expiry-date="{{ $expiryDisplay }}"
                                                                data-doc-updated-date="{{ $updatedDateDisplay }}"
                                                                data-doc-updated-time="{{ $updatedTimeDisplay }}"
                                                                data-doc-updated-by="{{ $doc->uploader?->name ?? 'Unknown' }}"
                                                                data-doc-history-url="{{ route('department-documents.update-history', $doc) }}">
                                                                <i class="fas fa-circle-info text-secondary" style="width: 16px;"></i><span class="fw-medium">Details</span>
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                                                data-bs-toggle="modal" data-bs-target="#editDocumentDetailsModal"
                                                                data-doc-name="{{ $doc->original_filename }}"
                                                                data-doc-type-id="{{ $doc->document_type_id }}"
                                                                data-doc-location-id="{{ $doc->document_location_id }}"
                                                                data-doc-folder-id="{{ $doc->document_folder_id ?? '' }}"
                                                                data-doc-date-received="{{ $receivedOnInput }}"
                                                                data-doc-expiry-date="{{ $expiryInput }}"
                                                                data-doc-action="{{ route('department-documents.update', $doc) }}">
                                                                <i class="fas fa-pen-to-square text-secondary" style="width: 16px;"></i><span class="fw-medium">Edit Details</span>
                                                            </button>
                                                        </li>
                                                        @else
                                                        <li>
                                                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                                                data-doc-detail-trigger
                                                                data-doc-id="{{ $doc->id }}"
                                                                data-doc-name="{{ $doc->original_filename }}"
                                                                data-doc-department="{{ $doc->department->name }}"
                                                                data-doc-type="{{ $doc->documentType->name }}"
                                                                data-doc-folder="{{ $docFolderPath }}"
                                                                data-doc-folder-code="{{ $docFolderCode ?? '—' }}"
                                                                data-doc-location="{{ $doc->documentLocation?->name ?? '—' }}"
                                                                data-doc-uploader="{{ $doc->uploader?->name ?? 'Unknown' }}"
                                                                data-doc-size="{{ $fileSizeDisplay }}"
                                                                data-doc-ext="{{ strtoupper($ext) }}"
                                                                data-doc-received="{{ $receivedOnDisplay }}"
                                                                data-doc-expiry-status="{{ $expiryStatus }}"
                                                                data-doc-expiry-date="{{ $expiryDisplay }}"
                                                                data-doc-updated-date="{{ $updatedDateDisplay }}"
                                                                data-doc-updated-time="{{ $updatedTimeDisplay }}"
                                                                data-doc-updated-by="{{ $doc->uploader?->name ?? 'Unknown' }}"
                                                                data-doc-history-url="{{ route('department-documents.update-history', $doc) }}">
                                                                <i class="fas fa-circle-info text-secondary" style="width: 16px;"></i><span class="fw-medium">Details</span>
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
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                            @if ($documents->count() === 0 && $subfolders->count() === 0)
                                <div class="doc-table-empty-overlay" aria-live="polite">
                                    <div class="text-muted doc-table-empty-state">
                                        <i class="fas fa-folder-open fa-3x mb-3 opacity-20"></i>
                                        <p>No documents found in this scope.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($documents->count() > 0)
                            <div class="card-footer doc-table-footer bg-white border-top-0 py-2 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <div class="text-muted small d-inline-flex align-items-center gap-1">
                                    <span>Showing</span>
                                    <span>{{ $documents->count() }}</span>
                                    @if (method_exists($documents, 'total'))
                                        <span>of {{ $documents->total() }}</span>
                                    @endif
                                    <span>documents</span>
                                </div>

                                @if ($documents->hasMorePages())
                                    @php
                                        $nextPerPage = max(15, (int) request('per_page', 15)) + 10;
                                        $loadMoreParams = array_merge(
                                            request()->except(['document_id', 'page']),
                                            ['per_page' => $nextPerPage]
                                        );
                                    @endphp
                                    <a href="{{ route('department-documents.index', $loadMoreParams) }}"
                                        class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                                        data-doc-load-more>
                                        <i class="fas fa-plus"></i> Load 10 more
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>


                </div>
            </div>
        @endif
    </div>

    <aside class="doc-details-panel" data-doc-details-panel aria-hidden="true">
        <div class="doc-details-panel__backdrop" data-doc-details-close></div>
        <div class="doc-details-panel__sheet" role="dialog" aria-modal="true" aria-labelledby="doc-details-title">
            <div class="doc-details-panel__header">
                <h5 id="doc-details-title" class="mb-0 fw-bold">Document Details</h5>
                <button type="button" class="btn btn-sm btn-link text-secondary p-0 text-decoration-none" data-doc-details-close aria-label="Close details panel">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="doc-details-panel__body">
                <div class="mb-3">
                    <div class="text-muted x-small text-uppercase mb-1">File Name</div>
                    <div class="fw-semibold text-dark" data-doc-detail-name>—</div>
                </div>

                <div class="doc-details-grid">
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Department</div>
                        <div class="doc-detail-item__value" data-doc-detail-department>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Document Type</div>
                        <div class="doc-detail-item__value" data-doc-detail-type>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Folder</div>
                        <div class="doc-detail-item__value" data-doc-detail-folder>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Folder Code</div>
                        <div class="doc-detail-item__value" data-doc-detail-folder-code>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Location</div>
                        <div class="doc-detail-item__value" data-doc-detail-location>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">File Type</div>
                        <div class="doc-detail-item__value" data-doc-detail-ext>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">File Size</div>
                        <div class="doc-detail-item__value" data-doc-detail-size>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Uploader</div>
                        <div class="doc-detail-item__value" data-doc-detail-uploader>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Received On</div>
                        <div class="doc-detail-item__value" data-doc-detail-received>—</div>
                    </div>
                    <div class="doc-detail-item">
                        <div class="doc-detail-item__label">Expiry</div>
                        <div class="doc-detail-item__value" data-doc-detail-expiry>—</div>
                    </div>
                </div>

                <div class="doc-details-last-updated mt-4">
                    <span data-doc-detail-last-updated>—</span>
                    <a href="javascript:void(0)" class="text-primary fw-bold text-decoration-none ms-2"
                        style="font-size: 0.72rem; letter-spacing: 0.05em;" data-doc-detail-history-link>
                        SEE MORE <i class="fas fa-chevron-right ms-1" style="font-size: 0.65rem;"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <div class="modal fade" id="documentUpdateHistoryModal" tabindex="-1" aria-labelledby="documentUpdateHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="documentUpdateHistoryModalLabel">
                        <i class="fas fa-history me-2 text-danger"></i>Update History
                    </h5>
                </div>
                <div class="modal-body pt-4">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="documentUpdateHistoryTable">
                            <thead class="bg-light sticky-top" style="z-index: 1;">
                                <tr>
                                    <th class="border-0 text-muted small text-uppercase fw-bold ps-3">User</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold">Description</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold">Changes</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold pe-3">Date &amp; Time</th>
                                </tr>
                            </thead>
                            <tbody id="documentUpdateHistoryContent">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No update history loaded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-brand px-4" data-bs-dismiss="modal" style="border-radius:6px; font-size:0.85rem; font-weight: 500;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="folderUpdateHistoryModal" tabindex="-1" aria-labelledby="folderUpdateHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="folderUpdateHistoryModalLabel">
                        <i class="fas fa-folder-tree me-2 text-danger"></i>Folder Activity History
                    </h5>
                </div>
                <div class="modal-body pt-4">
                    <div class="small text-muted mb-2" id="folderUpdateHistoryFolderName"></div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="folderUpdateHistoryTable">
                            <thead class="bg-light sticky-top" style="z-index: 1;">
                                <tr>
                                    <th class="border-0 text-muted small text-uppercase fw-bold ps-3">User</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold">Description</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold">Changes</th>
                                    <th class="border-0 text-muted small text-uppercase fw-bold pe-3">Date &amp; Time</th>
                                </tr>
                            </thead>
                            <tbody id="folderUpdateHistoryContent">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No folder history loaded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-brand px-4" data-bs-dismiss="modal" style="border-radius:6px; font-size:0.85rem; font-weight: 500;">Close</button>
                </div>
            </div>
        </div>
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
                                class="form-control field-input bg-light" placeholder="e.g. {{ config('brand.folder_prefix') }}-FIN-0001"
                                data-folder-create-code>
                        </div>
                        <div class="mb-2">
                            <label for="create_folder_location" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Physical Location <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <select id="create_folder_location" name="document_location_id" class="form-select field-input bg-light" data-folder-create-location>
                                <option value="">No specific location</option>
                                @foreach ($documentLocations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted" style="font-size: 0.75rem;">Documents uploaded here will inherit this location.</div>
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

    <!-- Edit Folder Modal -->
    <div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="global-rename-folder-form" method="POST" data-ajax-folder data-loading-target>
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold text-dark fs-5">Edit Folder</h5>
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
                                class="form-control field-input bg-light" placeholder="e.g. {{ config('brand.folder_prefix') }}-HR-0001">
                        </div>
                        <div class="mb-2">
                            <label for="rename_folder_location" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Physical Location <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <select id="rename_folder_location" name="document_location_id" class="form-select field-input bg-light">
                                <option value="">No specific location</option>
                                @foreach ($documentLocations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2 bg-white">
                        <button type="button" class="btn btn-light fw-semibold text-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent-red fw-semibold shadow-sm btn-submit-loading">
                            <i class="fas fa-save me-1"></i> Save Changes
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

    <!-- Edit Document Details Modal -->
    <div class="modal fade" id="editDocumentDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="editDocumentDetailsForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold text-dark fs-5">Edit Document Details</h5>
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

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_doc_type" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Document Type <span class="text-danger">*</span></label>
                                <select id="edit_doc_type" name="document_type_id" class="form-select field-input" required>
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type->id }}" data-has-expiry="{{ $type->has_expiry ? '1' : '0' }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_doc_location" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Physical Location <span class="text-danger">*</span></label>
                                <div id="edit_doc_location_manual_wrap">
                                    <select id="edit_doc_location" name="document_location_id" class="form-select field-input" required>
                                        <option value="">Select location</option>
                                        @foreach ($documentLocations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="edit_doc_location_inherited_wrap" class="d-none">
                                    <input type="text" id="edit_doc_location_inherited_display" class="form-control field-input bg-light" readonly>
                                    <div class="form-text small text-muted"><i class="fas fa-lock me-1"></i>Inherited from folder.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_doc_folder" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Folder</label>
                                <select id="edit_doc_folder" name="document_folder_id" class="form-select field-input">
                                    <option value="">Root</option>
                                    @foreach ($allFolders as $folderOption)
                                        <option value="{{ $folderOption->id }}" 
                                            data-location-id="{{ $folderOption->document_location_id ?? '' }}"
                                            data-location-name="{{ $folderOption->documentLocation?->name ?? '' }}">
                                            {{ $folderPathMaps[$folderOption->id]['display_path'] ?? $folderOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_doc_received" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Date Received <span class="text-danger">*</span></label>
                                <input type="date" id="edit_doc_received" name="date_received" class="form-control field-input" required>
                            </div>
                            <div class="col-md-6 d-none" id="edit_doc_expiry_wrap">
                                <label for="edit_doc_expiry" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Expiry Date</label>
                                <input type="date" id="edit_doc_expiry" name="expiry_date" class="form-control field-input">
                            </div>
                            <div class="col-12 d-none" id="edit_doc_expiry_reason_wrap">
                                <label for="edit_doc_expiry_reason" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Reason for Expiry Change</label>
                                <textarea id="edit_doc_expiry_reason" name="expiry_change_reason" class="form-control field-input" rows="2" maxlength="500" placeholder="Required when expiry date is changed."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2 bg-white">
                        <button type="button" class="btn btn-light fw-semibold text-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent-red fw-semibold shadow-sm btn-submit-loading">
                            <i class="fas fa-save me-1"></i> Save Details
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
                        enctype="multipart/form-data" id="upload-form" x-data="{ dragging: false, files: [], uploadMode: 'standard', showExpiry: false, receivedDate: '{{ old('date_received', date('Y-m-d')) }}', expiryDate: '{{ old('expiry_date', '') }}' }"
                        x-init="$nextTick(() => { const select = $refs.typeSelect; if (select && select.selectedIndex >= 0) showExpiry = select.options[select.selectedIndex].dataset.hasExpiry === '1'; })" data-loading-target>
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                        <input type="hidden" name="document_folder_id" value="{{ $currentFolderId ? (int) $currentFolderId : '' }}">

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
                                        @if($currentFolder && $currentFolder->document_location_id)
                                            <input type="text" class="form-control field-input bg-light" 
                                                value="{{ $currentFolder->documentLocation?->name ?? 'Unknown' }}" readonly>
                                            <input type="hidden" name="document_location_id" value="{{ $currentFolder->document_location_id }}">
                                            <div class="form-text small text-muted"><i class="fas fa-lock me-1"></i>Inherited from folder.</div>
                                        @else
                                            <select id="upload_location" name="document_location_id"
                                                class="form-select field-input" required>
                                                <option value="">Select location</option>
                                                @foreach ($documentLocations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted">Selected Folder</label>
                                        <input type="text" class="form-control field-input bg-light" value="{{ $currentFolder ? ($folderPathMaps[$currentFolder->id]['display_path'] ?? $currentFolder->name) : 'No folder selected' }}" readonly>
                                        <div class="form-text small text-muted">Uploads are only allowed inside a folder.</div>
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
                                            class="form-control field-input" value="{{ old('date_received', date('Y-m-d')) }}" x-model="receivedDate" :max="expiryDate || null" required>
                                    </div>

                                    <div class="col-md-6" x-show="showExpiry" x-cloak>
                                        <label for="upload_expiry_date"
                                            class="form-label small fw-bold text-uppercase text-muted">Expiry
                                            Date</label>
                                        <input type="date" id="upload_expiry_date" name="expiry_date"
                                            class="form-control field-input" value="{{ old('expiry_date') }}" x-model="expiryDate" :required="showExpiry" :min="receivedDate || null">
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

            .folder-grid-card {
                transition: transform 0.2s ease-out, box-shadow 0.2s ease-out, border-color 0.2s ease-out;
                background-color: #fff;
                border-radius: 12px;
            }

            .folder-grid-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08) !important;
                border-color: var(--company-primary-border) !important;
            }
            
            .folder-grid-card:active {
                transform: translateY(0);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05) !important;
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

            .badge-soft-danger {
                background: #fee2e2;
                color: #b91c1c;
                border: 1px solid #fecaca;
                font-weight: 600;
            }

            .badge-soft-warning {
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #fde68a;
                font-weight: 600;
            }

            .badge-soft-success {
                background: #dcfce7;
                color: #166534;
                border: 1px solid #bbf7d0;
                font-weight: 600;
            }

            .badge-soft-secondary {
                background: #e5e7eb;
                color: #374151;
                border: 1px solid #d1d5db;
                font-weight: 600;
            }

            .doc-command-search {
                position: relative;
                width: 100%;
                max-width: 760px;
                flex: 1 1 680px;
            }

            .doc-command-search__shell {
                position: relative;
                margin: 0 4px;
            }

            .doc-command-search-row {
                display: flex;
                align-items: flex-start;
                flex-wrap: nowrap;
                gap: 0.5rem;
            }

            .doc-sidebar-department {
                font-size: 2rem;
                font-weight: 706;
                color: #000;
                line-height: 1.2;
                margin-bottom: 2rem;
            }

            .doc-upload-btn {
                min-height: 2.5rem;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
                align-self: flex-start;
                margin-top: 0.25rem;
                margin-left: auto;
                flex: 0 0 auto;
            }

            .explorer-main.gap-4 {
                gap: 0.5rem !important;
            }

            #department-document-explorer {
                height: calc(100dvh - 56px - 4.5rem);
                min-height: 520px;
                overflow: hidden;
                margin-bottom: 0 !important;
            }

            #department-document-explorer .explorer-sidebar,
            #department-document-explorer .explorer-main {
                min-height: 0;
            }

            #department-document-explorer .explorer-main {
                overflow: hidden;
            }

            .explorer-main .doc-list-card {
                display: flex;
                flex-direction: column;
                flex: 0 1 auto;
                min-height: 0;
                max-height: 100%;
                overflow: hidden !important;
            }

            .explorer-main .doc-table-wrapper {
                flex: 1 1 auto;
                min-height: 0;
                overflow-x: auto !important;
                overflow-y: scroll !important;
                scrollbar-gutter: stable;
                position: relative;
            }

            .explorer-main .doc-table thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                background: #f8fafc;
            }

            .explorer-main .doc-table-wrapper::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }

            .explorer-main .doc-table-wrapper::-webkit-scrollbar-track {
                background: #eef1f4;
                border-radius: 999px;
            }

            .explorer-main .doc-table-wrapper::-webkit-scrollbar-thumb {
                background: #b0b7c3;
                border-radius: 999px;
            }

            .explorer-main .doc-table-wrapper::-webkit-scrollbar-thumb:hover {
                background: #8f99a8;
            }

            .doc-table {
                margin-bottom: 0;
                font-size: 1rem;
            }

            .doc-table th, 
            .doc-table td {
                padding: 0.5rem 0.75rem !important;
                vertical-align: middle;
            }

            .doc-table-footer {
                margin-top: 0 !important;
                position: relative;
                flex: 0 0 auto;
                min-height: 0 !important;
                height: auto !important;
                border-top: 1px solid #e5e7eb !important;
            }

            .doc-table-wrapper.is-empty {
                overflow: hidden;
            }

            .doc-table-context {
                padding: 1.25rem 1rem !important;
            }

            .doc-table-empty-overlay {
                position: absolute;
                top: 44px;
                right: 0;
                bottom: 0;
                left: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                pointer-events: none;
            }

            .doc-table-empty-state {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
            }

            .doc-table-empty-state p {
                margin-bottom: 0;
            }

            .explorer-sidebar {
                min-height: 0;
                background: transparent;
                display: flex;
                flex-direction: column;
                overflow-y: auto;
                overflow-x: hidden;
                scrollbar-gutter: stable;
            }

            .explorer-sidebar::-webkit-scrollbar {
                width: 6px;
            }

            .explorer-sidebar::-webkit-scrollbar-track {
                background: transparent;
            }

            .explorer-sidebar::-webkit-scrollbar-thumb {
                background: #e2e8f0;
                border-radius: 999px;
            }

            .explorer-sidebar::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1;
            }

            @media (max-width: 992px) {
                .explorer-sidebar {
                    max-height: 350px;
                    border-bottom: 1px solid #eef2f7;
                    padding-bottom: 1rem;
                    margin-bottom: 0.5rem;
                    min-height: 0;
                }
            }

            .explorer-sidebar__inner {
                padding: 0.5rem 0.25rem;
            }

            .doc-command-search__bar {
                display: flex;
                align-items: center;
                gap: 0.4rem;
                background: #e9eef6;
                border: 1px solid transparent;
                border-radius: 1.5rem;
                padding: 0.25rem 0.65rem;
                min-height: 3rem;
                position: relative;
                z-index: 1091;
                transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
            }

            .doc-command-search__shell.is-open .doc-command-search__bar {
                border-radius: 1.5rem 1.5rem 0 0;
                background: #fff;
                box-shadow: 0 1px 2px 0 rgba(60,64,67,0.1), 0 1px 3px 0 rgba(60,64,67,0.15);
            }

            .doc-command-search__bar:hover {
                background: #fff;
                box-shadow: 0 1px 2px 0 rgba(60,64,67,0.05), 0 1px 3px 1px rgba(60,64,67,0.08);
            }

            .doc-command-search__bar:focus-within {
                background: #fff;
                box-shadow: 0 1px 2px 0 rgba(60,64,67,0.1), 0 1px 3px 0 rgba(60,64,67,0.15);
            }

            .doc-command-search__icon {
                color: #6b7280;
                margin-left: 0.2rem;
                font-size: 0.85rem;
            }

            .doc-command-search__input {
                flex: 1;
                border: none;
                background: transparent;
                outline: none;
                min-width: 160px;
                color: #111827;
                font-size: 0.86rem;
            }

            .doc-command-search__input:focus {
                box-shadow: none;
            }

            .doc-command-search__global {
                border: none;
                background: transparent;
                color: #64748b;
                padding: 0.1rem 0.2rem;
                line-height: 1;
                font-size: 0.95rem;
            }

            .doc-command-search__clear {
                border: none;
                background: transparent;
                color: #6b7280;
                padding: 0.1rem 0.25rem;
                line-height: 1;
                font-size: 0.85rem;
            }

            .doc-command-search__clear:hover,
            .doc-command-search__clear:focus-visible {
                color: #374151;
                outline: none;
            }

            .doc-command-search__global:hover,
            .doc-command-search__global:focus-visible {
                color: #334155;
                outline: none;
            }

            .doc-command-search__global.is-active {
                color: #dc2626;
            }

            .doc-command-search__scope {
                font-size: 0.75rem;
                margin-top: 0.35rem;
                padding-left: 0.75rem;
            }

            .doc-command-search__results {
                position: absolute;
                z-index: 1092;
                top: 100%;
                left: 0;
                right: 0;
                background: #fff;
                border: none;
                border-top: 1px solid #e2e8f0;
                border-radius: 0 0 1.5rem 1.5rem;
                box-shadow: 0 4px 6px 0 rgba(60,64,67,0.08), 0 2px 4px -1px rgba(60,64,67,0.05);
                overflow: hidden;
            }


            .doc-command-search__results-inner {
                max-height: 340px;
                overflow-y: auto;
                padding: 0.25rem 0;
            }

            .doc-command-search__result-item {
                width: 100%;
                border: none;
                background: transparent;
                padding: 0.52rem 0.95rem;
                text-align: left;
                display: flex;
                align-items: flex-start;
                gap: 0.7rem;
            }

            .doc-command-search__result-item:hover,
            .doc-command-search__result-item:focus {
                background: #eef2f7;
                outline: none;
            }

            .doc-command-search__result-item.is-active {
                background: #e5eaf1;
                outline: none;
            }

            .doc-command-search__result-icon {
                color: #6b7280;
                font-size: 0.78rem;
                width: 1rem;
                margin-top: 0.22rem;
                flex: 0 0 auto;
            }

            .doc-command-search__result-title {
                font-weight: 500;
                color: #111827;
                line-height: 1.25;
                font-size: 1.02rem;
            }

            .doc-command-search__result-meta {
                font-size: 0.75rem;
                color: #697586;
                margin-top: 0.12rem;
                line-height: 1.2;
            }

            .doc-detail-trigger {
                font: inherit;
                border: none;
            }

            .doc-detail-trigger:hover,
            .doc-detail-trigger:focus-visible {
                color: var(--company-primary) !important;
                text-decoration: underline !important;
            }

            .doc-details-panel {
                position: fixed;
                inset: 0;
                z-index: 2060;
                pointer-events: none;
            }

            .doc-details-panel__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            .doc-details-panel__sheet {
                position: absolute;
                top: 0;
                right: 0;
                width: min(440px, 100%);
                height: 100%;
                background: #fff;
                box-shadow: -10px 0 30px rgba(15, 23, 42, 0.2);
                transform: translateX(100%);
                transition: transform 0.2s ease;
                display: flex;
                flex-direction: column;
            }

            .doc-details-panel.is-open {
                pointer-events: auto;
            }

            .doc-details-panel.is-open .doc-details-panel__backdrop {
                opacity: 1;
            }

            .doc-details-panel.is-open .doc-details-panel__sheet {
                transform: translateX(0);
            }

            body.doc-details-open {
                overflow: hidden;
            }

            .doc-details-panel__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1rem 0.85rem;
                border-bottom: 1px solid #e5e7eb;
            }

            .doc-details-panel__body {
                overflow-y: auto;
                padding: 1rem;
            }

            .doc-details-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.85rem;
            }

            .doc-detail-item {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 0.7rem;
                padding: 0.6rem 0.7rem;
            }

            .doc-detail-item__label {
                font-size: 0.68rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                margin-bottom: 0.25rem;
            }

            .doc-detail-item__value {
                font-size: 0.83rem;
                color: #111827;
                word-break: break-word;
            }

            .doc-details-last-updated {
                padding: 0.75rem;
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                background: #f8fafc;
                font-size: 0.82rem;
                color: #374151;
            }

            body.doc-details-open .modal-backdrop.show {
                z-index: 2080;
            }

            body.doc-details-open .modal.show {
                z-index: 2090;
            }

            #documentUpdateHistoryTable thead th {
                font-size: 0.7rem;
                letter-spacing: 0.08em;
                padding-top: 12px;
                padding-bottom: 12px;
                background-color: #f9fafb;
            }

            #documentUpdateHistoryTable tbody td {
                padding-top: 14px;
                padding-bottom: 14px;
                font-size: 0.85rem;
            }

            .history-user-avatar {
                width: 32px;
                height: 32px;
                background: #f3f4f6;
                color: #6b7280;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
            }

            @media (max-width: 768px) {
                .doc-command-search-row {
                    flex-wrap: wrap;
                }

                #department-document-explorer {
                    height: calc(100dvh - 56px - 2rem);
                    min-height: 420px;
                }

                .explorer-main .doc-table-wrapper {
                    height: 100%;
                    min-height: 0;
                }

                .doc-upload-btn {
                    min-height: 2.75rem;
                    margin-left: 0;
                }

                .doc-command-search__bar {
                    border-radius: 1rem;
                    flex-wrap: wrap;
                }

                .doc-details-panel__sheet {
                    width: 100%;
                }

                .doc-details-grid {
                    grid-template-columns: 1fr;
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
                const detailsPanel = document.querySelector('[data-doc-details-panel]');
                const detailsName = detailsPanel?.querySelector('[data-doc-detail-name]');
                const detailsDepartment = detailsPanel?.querySelector('[data-doc-detail-department]');
                const detailsType = detailsPanel?.querySelector('[data-doc-detail-type]');
                const detailsFolder = detailsPanel?.querySelector('[data-doc-detail-folder]');
                const detailsFolderCode = detailsPanel?.querySelector('[data-doc-detail-folder-code]');
                const detailsLocation = detailsPanel?.querySelector('[data-doc-detail-location]');
                const detailsExt = detailsPanel?.querySelector('[data-doc-detail-ext]');
                const detailsSize = detailsPanel?.querySelector('[data-doc-detail-size]');
                const detailsUploader = detailsPanel?.querySelector('[data-doc-detail-uploader]');
                const detailsReceived = detailsPanel?.querySelector('[data-doc-detail-received]');
                const detailsExpiry = detailsPanel?.querySelector('[data-doc-detail-expiry]');
                const detailsLastUpdated = detailsPanel?.querySelector('[data-doc-detail-last-updated]');
                const detailsHistoryLink = detailsPanel?.querySelector('[data-doc-detail-history-link]');
                const historyModalElement = document.getElementById('documentUpdateHistoryModal');
                const historyModal = historyModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal
                    ? new bootstrap.Modal(historyModalElement)
                    : null;
                const historyContent = document.getElementById('documentUpdateHistoryContent');
                const folderHistoryModalElement = document.getElementById('folderUpdateHistoryModal');
                const folderHistoryModal = folderHistoryModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal
                    ? new bootstrap.Modal(folderHistoryModalElement)
                    : null;
                const folderHistoryContent = document.getElementById('folderUpdateHistoryContent');
                const folderHistoryName = document.getElementById('folderUpdateHistoryFolderName');
                let detailsOpenTimer = null;
                let currentDocumentHistoryUrl = '';

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

                const editDocumentDetailsModal = document.getElementById('editDocumentDetailsModal');
                if (editDocumentDetailsModal) {
                    const detailsForm = editDocumentDetailsModal.querySelector('#editDocumentDetailsForm');
                    const nameInput = editDocumentDetailsModal.querySelector('#rename_doc_name');
                    const typeInput = editDocumentDetailsModal.querySelector('#edit_doc_type');
                    const locationInput = editDocumentDetailsModal.querySelector('#edit_doc_location');
                    const folderInput = editDocumentDetailsModal.querySelector('#edit_doc_folder');
                    const receivedInput = editDocumentDetailsModal.querySelector('#edit_doc_received');
                    const expiryWrap = editDocumentDetailsModal.querySelector('#edit_doc_expiry_wrap');
                    const expiryInput = editDocumentDetailsModal.querySelector('#edit_doc_expiry');
                    const expiryReasonWrap = editDocumentDetailsModal.querySelector('#edit_doc_expiry_reason_wrap');
                    const expiryReasonInput = editDocumentDetailsModal.querySelector('#edit_doc_expiry_reason');
                    let initialExpiryValue = '';

                    const syncExpiryVisibility = () => {
                        if (!typeInput || !expiryWrap || !expiryInput || !expiryReasonWrap || !expiryReasonInput) {
                            return;
                        }

                        const selected = typeInput.options[typeInput.selectedIndex];
                        const hasExpiry = selected?.getAttribute('data-has-expiry') === '1';

                        expiryWrap.classList.toggle('d-none', !hasExpiry);
                        expiryInput.required = hasExpiry;

                        if (!hasExpiry) {
                            expiryInput.value = '';
                        }

                        if (receivedInput) {
                            receivedInput.max = expiryInput.value || '';
                        }

                        expiryInput.min = receivedInput?.value || '';
                        syncExpiryReasonVisibility();
                    };

                    const syncExpiryReasonVisibility = () => {
                        if (!expiryReasonWrap || !expiryReasonInput || !expiryInput) {
                            return;
                        }

                        const changed = (expiryInput.value || '') !== (initialExpiryValue || '');
                        expiryReasonWrap.classList.toggle('d-none', !changed);
                        expiryReasonInput.required = changed;

                        if (!changed) {
                            expiryReasonInput.value = '';
                        }
                    };

                    const syncEditLocationVisibility = () => {
                        if (!folderInput || !locationInput) {
                            return;
                        }

                        const selectedFolder = folderInput.options[folderInput.selectedIndex];
                        const inheritedLocationId = selectedFolder?.getAttribute('data-location-id');
                        const inheritedLocationName = selectedFolder?.getAttribute('data-location-name');

                        const manualWrap = document.getElementById('edit_doc_location_manual_wrap');
                        const inheritedWrap = document.getElementById('edit_doc_location_inherited_wrap');
                        const inheritedDisplay = document.getElementById('edit_doc_location_inherited_display');

                        if (inheritedLocationId && inheritedLocationId !== '') {
                            manualWrap?.classList.add('d-none');
                            inheritedWrap?.classList.remove('d-none');
                            if (inheritedDisplay) {
                                inheritedDisplay.value = inheritedLocationName || 'Unknown';
                            }
                            
                            locationInput.value = inheritedLocationId;
                            locationInput.disabled = true;

                            let hiddenInput = document.getElementById('edit_doc_location_hidden');
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.id = 'edit_doc_location_hidden';
                                hiddenInput.name = 'document_location_id';
                                inheritedWrap?.appendChild(hiddenInput);
                            }
                            hiddenInput.value = inheritedLocationId;
                            hiddenInput.disabled = false;
                        } else {
                            manualWrap?.classList.remove('d-none');
                            inheritedWrap?.classList.add('d-none');
                            locationInput.disabled = false;
                            
                            const hiddenInput = document.getElementById('edit_doc_location_hidden');
                            if (hiddenInput) {
                                hiddenInput.disabled = true;
                            }
                        }
                    };

                    typeInput?.addEventListener('change', syncExpiryVisibility);
                    folderInput?.addEventListener('change', syncEditLocationVisibility);
                    receivedInput?.addEventListener('change', () => {
                        if (expiryInput) {
                            expiryInput.min = receivedInput.value || '';
                        }
                    });

                    expiryInput?.addEventListener('change', () => {
                        if (receivedInput) {
                            receivedInput.max = expiryInput.value || '';
                        }
                        syncExpiryReasonVisibility();
                    });

                    editDocumentDetailsModal.addEventListener('show.bs.modal', (event) => {
                        const button = event.relatedTarget;
                        if (!button || !detailsForm || !nameInput || !typeInput || !locationInput || !folderInput || !receivedInput || !expiryInput || !expiryReasonInput) {
                            return;
                        }

                        detailsForm.action = button.getAttribute('data-doc-action') || '';
                        nameInput.value = button.getAttribute('data-doc-name') || '';
                        typeInput.value = button.getAttribute('data-doc-type-id') || '';
                        folderInput.value = button.getAttribute('data-doc-folder-id') || '';
                        locationInput.value = button.getAttribute('data-doc-location-id') || '';
                        receivedInput.value = button.getAttribute('data-doc-date-received') || '';
                        expiryInput.value = button.getAttribute('data-doc-expiry-date') || '';
                        initialExpiryValue = expiryInput.value || '';
                        expiryReasonInput.value = '';

                        syncExpiryVisibility();
                        syncEditLocationVisibility();
                        syncExpiryReasonVisibility();
                        setTimeout(() => nameInput.select(), 250);
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
                    wrapper.innerHTML = `
                        ${type === 'success' ? '<i class="fas fa-check-circle me-2"></i>' : '<i class="fas fa-exclamation-circle me-2"></i>'}
                        <span>${message}</span>
                        <button type="button" class="btn-close-flash" onclick="this.parentElement.remove()" title="Close">
                            <i class="fas fa-times"></i>
                        </button>`;
                    explorer.parentElement.insertBefore(wrapper, explorer);

                    // Auto-fade after 4 seconds
                    setTimeout(() => {
                        if (!wrapper.parentElement) return;
                        wrapper.classList.remove('animate-fade-in');
                        wrapper.classList.add('animate-fade-out');
                        setTimeout(() => wrapper.remove(), 400);
                    }, 4000);
                };

                const disableSubmit = (form, disabled) => {
                    form.querySelectorAll('button[type="submit"]').forEach((button) => {
                        button.disabled = disabled;
                        if (!disabled && button.dataset.originalHTML) {
                            button.innerHTML = button.dataset.originalHTML;
                        }
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
                    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((toggle) => {
                        if (toggle.dataset.rowDropdownBound === '1') {
                            return;
                        }

                        toggle.dataset.rowDropdownBound = '1';
                        
                        // Handle Table rows (z-index fix)
                        const row = toggle.closest('tr');
                        if (row) {
                            toggle.addEventListener('show.bs.dropdown', () => {
                                row.classList.add('row-dropdown-open');
                            });

                            toggle.addEventListener('hide.bs.dropdown', () => {
                                row.classList.remove('row-dropdown-open');
                            });
                        }

                        // Handle Tree actions (visibility fix)
                        const treeActions = toggle.closest('.ui-tree-actions');
                        if (treeActions) {
                            toggle.addEventListener('show.bs.dropdown', () => {
                                treeActions.classList.add('show');
                            });

                            toggle.addEventListener('hide.bs.dropdown', () => {
                                treeActions.classList.remove('show');
                            });
                        }
                    });
                };

                const openDetailsPanel = () => {
                    if (!detailsPanel) {
                        return;
                    }

                    detailsPanel.classList.add('is-open');
                    detailsPanel.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('doc-details-open');
                };

                const closeDetailsPanel = () => {
                    if (!detailsPanel) {
                        return;
                    }

                    detailsPanel.classList.remove('is-open');
                    detailsPanel.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('doc-details-open');
                };

                const setDetailValue = (node, value) => {
                    if (!node) {
                        return;
                    }
                    node.textContent = value && String(value).trim() !== '' ? String(value) : '—';
                };

                const fillDetailsFromTrigger = (trigger) => {
                    if (!trigger) {
                        return;
                    }

                    const name = trigger.getAttribute('data-doc-name') || '—';
                    const department = trigger.getAttribute('data-doc-department') || '—';
                    const type = trigger.getAttribute('data-doc-type') || '—';
                    const folder = trigger.getAttribute('data-doc-folder') || '—';
                    const folderCode = trigger.getAttribute('data-doc-folder-code') || '—';
                    const location = trigger.getAttribute('data-doc-location') || '—';
                    const ext = trigger.getAttribute('data-doc-ext') || '—';
                    const size = trigger.getAttribute('data-doc-size') || '0';
                    const uploader = trigger.getAttribute('data-doc-uploader') || 'Unknown';
                    const received = trigger.getAttribute('data-doc-received') || '—';
                    const expiryStatus = trigger.getAttribute('data-doc-expiry-status') || 'N/A';
                    const expiryDate = trigger.getAttribute('data-doc-expiry-date') || 'N/A';
                    const updatedBy = trigger.getAttribute('data-doc-updated-by') || 'Unknown';
                    const updatedDate = trigger.getAttribute('data-doc-updated-date') || '—';
                    const updatedTime = trigger.getAttribute('data-doc-updated-time') || '—';
                    currentDocumentHistoryUrl = trigger.getAttribute('data-doc-history-url') || '';

                    setDetailValue(detailsName, name);
                    setDetailValue(detailsDepartment, department);
                    setDetailValue(detailsType, type);
                    setDetailValue(detailsFolder, folder);
                    setDetailValue(detailsFolderCode, folderCode);
                    setDetailValue(detailsLocation, location);
                    setDetailValue(detailsExt, ext);
                    setDetailValue(detailsSize, `${size} KB`);
                    setDetailValue(detailsUploader, uploader);
                    setDetailValue(detailsReceived, received);
                    setDetailValue(detailsExpiry, `${expiryStatus}${expiryDate !== 'N/A' ? ` (${expiryDate})` : ''}`);

                    if (detailsLastUpdated) {
                        detailsLastUpdated.innerHTML = `<span class="fw-bold text-danger text-uppercase">${escapeHtml(name)}</span>'s data was last updated by <span class="fw-bold text-danger text-uppercase">${escapeHtml(updatedBy)}</span> on <span class="fw-bold text-danger">${escapeHtml(updatedDate)}</span> at <span class="fw-bold text-danger">${escapeHtml(updatedTime)}</span>.`;
                    }

                    if (detailsHistoryLink) {
                        detailsHistoryLink.setAttribute('data-doc-id', trigger.getAttribute('data-doc-id') || '');
                    }
                };

                const renderHistoryChanges = (changes) => {
                    if (!changes || typeof changes !== 'object') {
                        return '<span class="text-muted small">—</span>';
                    }

                    if (changes.before && changes.after) {
                        const keys = Array.from(new Set([
                            ...Object.keys(changes.before || {}),
                            ...Object.keys(changes.after || {}),
                        ]));

                        let html = '<div class="mt-2" style="font-size: 0.75rem; border-left: 2px solid #fecaca; padding-left: 12px; margin-left: 4px;">';
                        let changedCount = 0;

                        keys.forEach((key) => {
                            const beforeVal = changes.before[key];
                            const afterVal = changes.after[key];
                            const bStr = (beforeVal === null || beforeVal === '') ? 'NONE' : String(beforeVal);
                            const aStr = (afterVal === null || afterVal === '') ? 'NONE' : String(afterVal);
                            if (bStr !== aStr) {
                                html += `<div class="mb-1"><span class="fw-semibold text-secondary" style="font-size: 0.65rem;">${escapeHtml(key.replace(/_/g, ' ').toUpperCase())}:</span> <span class="text-decoration-line-through text-muted small">${escapeHtml(bStr)}</span> <i class="fas fa-arrow-right mx-1 text-danger opacity-50" style="font-size: 0.6rem;"></i> <span class="text-danger fw-semibold">${escapeHtml(aStr)}</span></div>`;
                                changedCount += 1;
                            }
                        });

                        html += '</div>';
                        return changedCount > 0 ? html : '<span class="text-muted small">—</span>';
                    }

                    const fallback = Object.entries(changes)
                        .map(([key, value]) => `<div class="mb-1"><span class="fw-semibold text-secondary" style="font-size: 0.65rem;">${escapeHtml(key.replace(/_/g, ' ').toUpperCase())}:</span> <span class="text-danger fw-semibold">${escapeHtml(String(value ?? 'NONE'))}</span></div>`)
                        .join('');

                    return fallback ? `<div class="mt-2" style="font-size: 0.75rem; border-left: 2px solid #fecaca; padding-left: 12px; margin-left: 4px;">${fallback}</div>` : '<span class="text-muted small">—</span>';
                };

                const renderHistoryTable = (targetBody, data, emptyMessage) => {
                    if (!targetBody) {
                        return;
                    }

                    if (!Array.isArray(data) || data.length === 0) {
                        targetBody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted">${escapeHtml(emptyMessage)}</td></tr>`;
                        return;
                    }

                    targetBody.innerHTML = data.map((log) => `
                        <tr>
                            <td class="ps-3" style="width: 15%; vertical-align: top;">
                                <div class="d-flex align-items-center">
                                    <div class="history-user-avatar me-2" style="width: 28px; height: 28px; flex-shrink: 0;">
                                        <i class="fas fa-user" style="font-size: 0.7rem;"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size: 0.8rem;" title="${escapeHtml(log.user_name || 'System')}">${escapeHtml(log.user_name || 'System')}</div>
                                        <div class="text-muted text-uppercase" style="font-size: 0.55rem; font-weight: 700; letter-spacing: 0.5px;">${escapeHtml(log.user_role || 'System')}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="width: 30%; vertical-align: top;">
                                <div class="text-dark fw-semibold" style="font-size: 0.8rem;">${escapeHtml(log.description || 'Updated')}</div>
                            </td>
                            <td style="width: 35%; vertical-align: top;">
                                ${renderHistoryChanges(log.changes)}
                            </td>
                            <td class="pe-3 text-end" style="width: 20%; vertical-align: top;">
                                <div class="text-dark fw-semibold" style="font-size: 0.8rem;">${escapeHtml(log.date || '-')}</div>
                                <div class="small text-muted" style="font-size: 0.7rem;">${escapeHtml(log.time || '-')}</div>
                            </td>
                        </tr>
                    `).join('');
                };

                const bindDocumentDetails = () => {
                    document.querySelectorAll('[data-doc-detail-trigger]').forEach((trigger) => {
                        if (trigger.dataset.docDetailBound === '1') {
                            return;
                        }

                        trigger.dataset.docDetailBound = '1';

                        trigger.addEventListener('click', (event) => {
                            event.preventDefault();
                            event.stopPropagation();

                            if (detailsOpenTimer) {
                                clearTimeout(detailsOpenTimer);
                            }

                            detailsOpenTimer = window.setTimeout(() => {
                                fillDetailsFromTrigger(trigger);
                                openDetailsPanel();
                            }, 220);
                        });

                        trigger.addEventListener('dblclick', () => {
                            if (detailsOpenTimer) {
                                clearTimeout(detailsOpenTimer);
                                detailsOpenTimer = null;
                            }
                        });
                    });

                    if (detailsPanel && detailsPanel.dataset.detailsCloseBound !== '1') {
                        detailsPanel.dataset.detailsCloseBound = '1';
                        detailsPanel.querySelectorAll('[data-doc-details-close]').forEach((closeBtn) => {
                            closeBtn.addEventListener('click', () => {
                                closeDetailsPanel();
                            });
                        });
                    }

                    if (detailsHistoryLink && detailsHistoryLink.dataset.historyBound !== '1') {
                        detailsHistoryLink.dataset.historyBound = '1';
                        detailsHistoryLink.addEventListener('click', (event) => {
                            event.preventDefault();
                            if (!currentDocumentHistoryUrl || !historyContent) {
                                return;
                            }

                            historyContent.innerHTML = `
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2 text-danger" role="status"></div>
                                        Loading history...
                                    </td>
                                </tr>
                            `;

                            if (historyModal) {
                                closeDetailsPanel();
                                window.setTimeout(() => {
                                    historyModal.show();
                                }, 120);
                            }

                            fetch(currentDocumentHistoryUrl, {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            }).then((response) => response.json()).then((data) => {
                                renderHistoryTable(historyContent, data, 'No update history found.');
                            }).catch(() => {
                                historyContent.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error loading history.</td></tr>';
                            });
                        });
                    }
                };

                const bindFolderHistory = () => {
                    if (document.body.dataset.folderHistoryBound === '1') {
                        return;
                    }

                    document.body.dataset.folderHistoryBound = '1';
                    document.addEventListener('click', (event) => {
                        const trigger = event.target.closest('[data-folder-history-trigger]');
                        if (!trigger) {
                            return;
                        }

                        event.preventDefault();

                        const historyUrl = trigger.getAttribute('data-folder-history-url') || '';
                        const folderName = trigger.getAttribute('data-folder-name') || 'Folder';
                        if (!historyUrl || !folderHistoryContent) {
                            return;
                        }

                        if (folderHistoryName) {
                            folderHistoryName.textContent = folderName;
                        }

                        folderHistoryContent.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2 text-danger" role="status"></div>
                                    Loading history...
                                </td>
                            </tr>
                        `;

                        if (folderHistoryModal) {
                            folderHistoryModal.show();
                        }

                        fetch(historyUrl, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        }).then((response) => response.json()).then((data) => {
                            renderHistoryTable(folderHistoryContent, data, 'No folder activity history found.');
                        }).catch(() => {
                            folderHistoryContent.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error loading history.</td></tr>';
                        });
                    });
                };


                const parseDocumentRowsFromHtml = (htmlText) => {
                    const parser = new DOMParser();
                    const nextDoc = parser.parseFromString(htmlText, 'text/html');
                    const nextRows = Array.from(nextDoc.querySelectorAll('.doc-table tbody tr[data-document-row-id]'));
                    const nextCountText = nextDoc.querySelector('.doc-table-footer .text-muted.small')?.textContent || '';
                    const nextLoadMoreLink = nextDoc.querySelector('[data-doc-load-more]');

                    return {
                        rows: nextRows,
                        countText: nextCountText,
                        hasLoadMore: Boolean(nextLoadMoreLink),
                        nextLoadMoreHref: nextLoadMoreLink?.getAttribute('href') || '',
                    };
                };

                const getCurrentPerPageCount = () => {
                    const rows = document.querySelectorAll('.doc-table tbody tr[data-document-row-id]');
                    const count = Number(rows.length || 0);
                    return Math.max(15, count);
                };

                const buildNextLoadMoreHref = () => {
                    const url = new URL(window.location.href);
                    const nextPerPage = getCurrentPerPageCount() + 10;
                    url.searchParams.set('per_page', String(nextPerPage));
                    url.searchParams.delete('page');
                    url.searchParams.delete('document_id');
                    return `${url.pathname}${url.search}${url.hash}`;
                };

                const bindLoadMore = () => {
                    const loadMoreBtn = document.querySelector('[data-doc-load-more]');
                    if (!loadMoreBtn || loadMoreBtn.dataset.loadMoreBound === '1') {
                        return;
                    }

                    loadMoreBtn.dataset.loadMoreBound = '1';

                    loadMoreBtn.addEventListener('click', async (event) => {
                        event.preventDefault();

                        const href = buildNextLoadMoreHref();
                        if (!href) {
                            return;
                        }

                        const tableBody = document.querySelector('.doc-table tbody');
                        if (!tableBody) {
                            window.location.assign(href);
                            return;
                        }

                        if (loadMoreBtn.dataset.loading === '1') {
                            return;
                        }

                        loadMoreBtn.dataset.loading = '1';
                        const originalHtml = loadMoreBtn.innerHTML;
                        loadMoreBtn.classList.add('disabled');
                        loadMoreBtn.setAttribute('aria-disabled', 'true');
                        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

                        try {
                            const response = await fetch(href, {
                                headers: {
                                    Accept: 'text/html,application/xhtml+xml',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });

                            if (!response.ok) {
                                throw new Error('Load more request failed');
                            }

                            const html = await response.text();
                            const parsed = parseDocumentRowsFromHtml(html);
                            const existingIds = new Set(Array.from(tableBody.querySelectorAll('tr[data-document-row-id]'))
                                .map((row) => row.getAttribute('data-document-row-id')));

                            const fragment = document.createDocumentFragment();
                            parsed.rows.forEach((row) => {
                                const rowId = row.getAttribute('data-document-row-id');
                                if (!rowId || existingIds.has(rowId)) {
                                    return;
                                }

                                existingIds.add(rowId);
                                fragment.appendChild(row);
                            });

                            if (fragment.childNodes.length > 0) {
                                tableBody.appendChild(fragment);
                                bindPreviewButtons();
                                bindPreviewDoubleClick();
                                bindActionDropdownZIndexFix();
                                bindDocumentDetails();
                            }

                            const footerCountNode = document.querySelector('.doc-table-footer .text-muted.small');
                            if (footerCountNode && parsed.countText.trim() !== '') {
                                footerCountNode.textContent = parsed.countText.trim().replace(/\s+/g, ' ');
                            }

                            if (parsed.hasLoadMore) {
                                const updatedHref = buildNextLoadMoreHref();
                                loadMoreBtn.setAttribute('href', updatedHref);
                                window.history.replaceState({}, '', updatedHref);
                                loadMoreBtn.classList.remove('disabled');
                                loadMoreBtn.removeAttribute('aria-disabled');
                                loadMoreBtn.innerHTML = originalHtml;
                            } else {
                                loadMoreBtn.remove();
                            }
                        } catch (error) {
                            loadMoreBtn.classList.remove('disabled');
                            loadMoreBtn.removeAttribute('aria-disabled');
                            loadMoreBtn.innerHTML = originalHtml;
                            window.location.assign(href);
                        } finally {
                            delete loadMoreBtn.dataset.loading;
                        }
                    });
                };

                const bindLiveSearch = () => {
                    const form = document.querySelector('[data-doc-live-search-form]');
                    const input = form?.querySelector('[data-doc-live-search-input]');
                    const searchShell = form?.querySelector('.doc-command-search__shell');
                    const clearSearchButton = form?.querySelector('[data-doc-search-clear]');
                    const globalSearchInput = form?.querySelector('[data-doc-global-search]');
                    const globalSearchToggle = form?.querySelector('[data-doc-global-search-toggle]');
                    const scopeLabel = form?.querySelector('[data-doc-search-scope]');
                    const suggestionPanel = form?.querySelector('[data-doc-search-suggestions]');
                    const suggestionResults = form?.querySelector('[data-doc-search-results]');
                    const suggestionUrl = form?.getAttribute('data-doc-suggest-url') || '';
                    let suggestionRequestId = 0;
                    let activeSuggestionRequestId = 0;
                    let suggestionAbortController = null;
                    let isFetchingSuggestions = false;
                    let activeSuggestionIndex = 0;

                    if (!form || !input || input.dataset.liveSearchBound === '1') {
                        return;
                    }

                    input.dataset.liveSearchBound = '1';
                    let suggestionTimer = null;

                    const hideSuggestions = () => {
                        if (suggestionPanel) {
                            suggestionPanel.classList.add('d-none');
                        }
                        if (searchShell) {
                            searchShell.classList.remove('is-open');
                        }
                        if (suggestionResults) {
                            suggestionResults.innerHTML = '';
                        }
                        activeSuggestionIndex = 0;
                    };

                    const showSuggestions = () => {
                        if (suggestionPanel) {
                            suggestionPanel.classList.remove('d-none');
                        }
                        if (searchShell) {
                            searchShell.classList.add('is-open');
                        }
                    };

                    const getSuggestionButtons = () => Array.from(suggestionResults?.querySelectorAll('[data-doc-search-url]') || []);

                    const syncClearButtonState = () => {
                        if (!clearSearchButton) {
                            return;
                        }

                        const hasValue = input.value.trim() !== '';
                        clearSearchButton.classList.toggle('d-none', !hasValue);
                        clearSearchButton.disabled = !hasValue;
                    };

                    const paintActiveSuggestion = () => {
                        const items = getSuggestionButtons();
                        if (items.length === 0) {
                            activeSuggestionIndex = 0;
                            return;
                        }

                        const maxIndex = items.length - 1;
                        activeSuggestionIndex = Math.max(0, Math.min(activeSuggestionIndex, maxIndex));

                        items.forEach((item, index) => {
                            const isActive = index === activeSuggestionIndex;
                            item.classList.toggle('is-active', isActive);
                            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        });
                    };

                    const renderSuggestions = (items) => {
                        if (!suggestionResults) {
                            return;
                        }

                        if (!Array.isArray(items) || items.length === 0) {
                            activeSuggestionIndex = 0;
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
                            const isFolder = item?.type === 'folder';
                            const ext = (item?.extension || '').toLowerCase();
                            let iconClass = 'fas fa-file-alt text-secondary';

                            if (isFolder) {
                                iconClass = 'fas fa-folder text-danger';
                            } else if (ext === 'pdf') {
                                iconClass = 'fas fa-file-pdf text-danger';
                            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                                iconClass = 'fas fa-file-image text-primary';
                            } else if (['doc', 'docx'].includes(ext)) {
                                iconClass = 'fas fa-file-word text-primary';
                            } else if (['xls', 'xlsx'].includes(ext)) {
                                iconClass = 'fas fa-file-excel text-success';
                            } else if (ext === 'csv') {
                                iconClass = 'fas fa-file-csv text-success';
                            } else if (['zip', 'rar'].includes(ext)) {
                                iconClass = 'fas fa-file-archive text-warning';
                            }

                            return `
                                <button type="button" class="doc-command-search__result-item" data-doc-search-url="${targetUrl}">
                                    <i class="${iconClass} doc-command-search__result-icon" aria-hidden="true"></i>
                                    <div class="min-w-0">
                                        <div class="doc-command-search__result-title text-truncate">${title}</div>
                                        <div class="doc-command-search__result-meta text-truncate">${department} &bull; ${folder}${updatedAt ? ` &bull; ${updatedAt}` : ''}</div>
                                    </div>
                                </button>
                            `;
                        }).join('');

                        activeSuggestionIndex = 0;
                        paintActiveSuggestion();
                        showSuggestions();
                    };

                    const fetchSuggestions = async () => {
                        const query = input.value.trim();
                        if (query.length < 1 || !suggestionUrl) {
                            hideSuggestions();
                            isFetchingSuggestions = false;
                            return;
                        }

                        isFetchingSuggestions = true;

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

                            if (requestId === activeSuggestionRequestId) {
                                isFetchingSuggestions = false;
                            }
                        }
                    };

                    const syncGlobalToggleState = () => {
                        if (!globalSearchInput || !globalSearchToggle) {
                            return;
                        }

                        const enabled = globalSearchInput.value === '1';
                        globalSearchToggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
                        globalSearchToggle.classList.toggle('is-active', enabled);

                        if (scopeLabel) {
                            scopeLabel.textContent = enabled
                                ? 'Quick suggestions across all accessible departments'
                                : 'Searching within this department';
                        }
                    };

                    syncGlobalToggleState();
                    syncClearButtonState();

                    if (globalSearchToggle && globalSearchInput && globalSearchToggle.dataset.liveSearchBound !== '1') {
                        globalSearchToggle.dataset.liveSearchBound = '1';
                        globalSearchToggle.addEventListener('click', () => {
                            globalSearchInput.value = globalSearchInput.value === '1' ? '0' : '1';
                            syncGlobalToggleState();
                            input.focus({
                                preventScroll: true,
                            });
                            clearTimeout(suggestionTimer);
                            suggestionTimer = setTimeout(fetchSuggestions, 100);
                        });
                    }

                    input.addEventListener('input', () => {
                        clearTimeout(suggestionTimer);
                        syncClearButtonState();

                        if (input.value.trim() === '') {
                            hideSuggestions();
                            return;
                        }

                        suggestionTimer = setTimeout(fetchSuggestions, 100);
                    });

                    input.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            hideSuggestions();
                        }

                        if (event.key === 'Enter') {
                            event.preventDefault();
                            clearTimeout(suggestionTimer);

                            if (input.value.trim() !== '') {
                                fetchSuggestions();
                                return;
                            }
                        }

                        if (event.key === 'ArrowDown') {
                            const firstItem = suggestionResults?.querySelector('[data-doc-search-url]');
                            if (firstItem) {
                                event.preventDefault();
                                const items = getSuggestionButtons();
                                activeSuggestionIndex = Math.min(activeSuggestionIndex + 1, Math.max(items.length - 1, 0));
                                paintActiveSuggestion();
                                (items[activeSuggestionIndex] || firstItem).focus();
                            }
                        } else if (event.key === 'ArrowUp') {
                            const firstItem = suggestionResults?.querySelector('[data-doc-search-url]');
                            if (firstItem) {
                                event.preventDefault();
                                const items = getSuggestionButtons();
                                activeSuggestionIndex = Math.max(activeSuggestionIndex - 1, 0);
                                paintActiveSuggestion();
                                (items[activeSuggestionIndex] || firstItem).focus();
                            }
                        }
                    });

                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        clearTimeout(suggestionTimer);
                        if (input.value.trim() !== '') {
                            fetchSuggestions();
                        } else {
                            hideSuggestions();
                        }
                    });

                    if (clearSearchButton && clearSearchButton.dataset.liveSearchBound !== '1') {
                        clearSearchButton.dataset.liveSearchBound = '1';
                        clearSearchButton.addEventListener('click', () => {
                            input.value = '';
                            clearTimeout(suggestionTimer);
                            syncClearButtonState();
                            hideSuggestions();
                            input.focus({
                                preventScroll: true,
                            });
                        });
                    }

                    if (suggestionResults && suggestionResults.dataset.liveSearchBound !== '1') {
                        suggestionResults.dataset.liveSearchBound = '1';

                        suggestionResults.addEventListener('click', (event) => {
                            const button = event.target.closest('[data-doc-search-url]');
                            if (!button) {
                                return;
                            }

                            const items = getSuggestionButtons();
                            const clickedIndex = items.indexOf(button);
                            if (clickedIndex >= 0) {
                                activeSuggestionIndex = clickedIndex;
                                paintActiveSuggestion();
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
                                const items = getSuggestionButtons();
                                const currentIndex = items.indexOf(current);
                                const nextIndex = items.indexOf(next);
                                activeSuggestionIndex = nextIndex >= 0 ? nextIndex : Math.max(currentIndex, 0);
                                paintActiveSuggestion();
                                next.focus();
                            } else if (event.key === 'ArrowUp') {
                                event.preventDefault();
                                const prev = current.previousElementSibling?.matches('[data-doc-search-url]')
                                    ? current.previousElementSibling
                                    : null;
                                if (prev) {
                                    const items = getSuggestionButtons();
                                    const prevIndex = items.indexOf(prev);
                                    activeSuggestionIndex = prevIndex >= 0 ? prevIndex : 0;
                                    paintActiveSuggestion();
                                    prev.focus();
                                } else {
                                    activeSuggestionIndex = 0;
                                    paintActiveSuggestion();
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
                            if (button && button.hasAttribute('data-folder-location-id')) {
                                document.getElementById('rename_folder_location').value = button.getAttribute(
                                    'data-folder-location-id') || '';
                            } else {
                                document.getElementById('rename_folder_location').value = '';
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
                bindLoadMore();
                bindDocumentDetails();
                bindFolderHistory();

                // Loading State Handlers
                document.querySelectorAll('form[data-loading-target], #archiveDocumentForm').forEach(form => {
                    form.addEventListener('submit', function() {
                        const btn = this.querySelector('.btn-submit-loading');
                        if (btn && !btn.disabled) {
                            if (!btn.dataset.originalHTML) btn.dataset.originalHTML = btn.innerHTML;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
                            btn.disabled = true;
                            // Note: Reversion is now handled by the AJAX 'finally' block or standard page reload
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
                        bindLoadMore();
                        bindDocumentDetails();
                        bindFolderHistory();

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
                    const isDelete = form.id === 'global-delete-folder-form';

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
                            if (isDelete) {
                                closeAllFolderModals();
                            }
                            return;
                        }

                        closeAllFolderModals();
                        showFlash(payload.message || 'Folder updated successfully.');
                        await reloadExplorerFromUrl(payload.redirect_url || window.location.href);
                    } catch (error) {
                        showFlash('Folder action failed. Please try again.', 'error');
                        if (isDelete) {
                            closeAllFolderModals();
                        }
                    } finally {
                        disableSubmit(form, false);
                    }
                };

                bindFolderCreateUi();
                bindFolderAjaxForms();
                bindPreviewButtons();
                bindPreviewDoubleClick();
                bindLoadMore();
                bindDocumentDetails();
                bindFolderHistory();
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
                        closeDetailsPanel();
                        closePreview();
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
