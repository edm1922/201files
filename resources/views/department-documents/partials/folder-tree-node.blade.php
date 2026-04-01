@php
    $children = $foldersByParent->get((int) $folder->id, collect());
    $isActive = (int) $folder->id === (int) $currentFolderId;
    $isOpen = in_array((int) $folder->id, $activePathIds ?? []) || $isActive;
@endphp

<li class="ui-tree-node">
    <div
        class="ui-tree-item group d-flex align-items-center gap-2 py-1 px-2 mb-1 rounded-2 transition-all {{ $isActive ? 'bg-light active' : '' }}">

        {{-- Chevron --}}
        @if($children->isNotEmpty())
            <a data-bs-toggle="collapse" href="#folder-collapse-{{ $folder->id }}" role="button" aria-expanded="true"
                class="text-decoration-none d-flex align-items-center justify-content-center" style="width: 16px;">
                <i class="fas fa-chevron-down text-muted opacity-50 flex-shrink-0 collapse-icon"
                    style="font-size: 0.65rem;"></i>
            </a>
        @else
            <div style="width: 16px;" class="d-flex align-items-center justify-content-center">
                <i class="fas fa-circle text-muted opacity-25 flex-shrink-0" style="font-size: 0.3rem;"></i>
            </div>
        @endif

        {{-- Main Link --}}
        @if($isActive && $children->isNotEmpty())
            <a data-bs-toggle="collapse" href="#folder-collapse-{{ $folder->id }}" role="button" aria-expanded="true"
                class="d-flex align-items-center gap-2 text-decoration-none flex-grow-1 overflow-hidden"
                style="min-width: 0;">
        @else
                <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId, 'document_folder_id' => $folder->id]) }}"
                    class="d-flex align-items-center gap-2 text-decoration-none flex-grow-1 overflow-hidden"
                    style="min-width: 0;">
            @endif

                <i class="fas {{ $isOpen ? 'fa-folder-open text-danger' : 'fa-folder text-secondary opacity-75' }}"
                    style="font-size: 1.1rem; width: 20px; text-align: center;"></i>

                <div class="d-flex flex-column overflow-hidden flex-grow-1" style="min-width: 0;">
                    <span class="text-truncate {{ $isActive ? 'text-dark fw-bold' : 'text-secondary' }}"
                        style="font-size: 0.85rem;" title="{{ $folder->name }}">
                        {{ $folder->name }}
                    </span>
                    @if ($folder->folder_code)
                        <span class="text-muted text-truncate x-small"
                            style="font-size: 0.7rem; margin-top: -1px; opacity: 0.8;" title="{{ $folder->folder_code }}">
                            {{ $folder->folder_code }}
                        </span>
                    @endif
                </div>
            </a>

            {{-- Actions --}}
            @if (($canManageFolders ?? false) || ($canEditDeleteFolders ?? false))
                <div class="ui-tree-actions d-flex align-items-center gap-1 ms-auto ps-2">
                    @if ($canManageFolders ?? false)
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none"
                            title="Add Subfolder" data-bs-toggle="modal" data-bs-target="#createDepartmentFolderModal"
                            data-folder-department-id="{{ $selectedDepartmentId }}" data-folder-parent-id="{{ $folder->id }}"
                            data-folder-create-scope="New folder inside {{ $folder->name }}">
                            <i class="fas fa-plus bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 18px; height: 18px; font-size: 0.6rem;"></i>
                        </button>
                    @endif

                    @if ($canEditDeleteFolders ?? false)
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none shadow-none"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Folder actions">
                                <i class="fas fa-ellipsis-v px-1"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        data-bs-toggle="modal" data-bs-target="#renameFolderModal"
                                        data-folder-name="{{ $folder->name }}" data-folder-code="{{ $folder->folder_code }}"
                                        data-folder-action="{{ route('department-documents.folders.update', $folder) }}">
                                        <i class="fas fa-pen text-secondary" style="width: 16px;"></i><span
                                            class="fw-medium">Rename</span>
                                    </button>
                                </li>
                                <li>
                                    <hr class="dropdown-divider opacity-50 my-1">
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2"
                                        data-bs-toggle="modal" data-bs-target="#deleteFolderModal"
                                        data-folder-name="{{ $folder->name }}"
                                        data-folder-action="{{ route('department-documents.folders.destroy', $folder) }}">
                                        <i class="fas fa-trash" style="width: 16px;"></i><span class="fw-medium">Delete</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
    </div>

    @if ($children->isNotEmpty())
        <ul class="ui-tree-children collapse show" id="folder-collapse-{{ $folder->id }}">
            @foreach ($children as $child)
                @include('department-documents.partials.folder-tree-node', [
                    'folder' => $child,
                    'foldersByParent' => $foldersByParent,
                    'selectedDepartmentId' => $selectedDepartmentId,
                    'currentFolderId' => $currentFolderId,
                    'activePathIds' => $activePathIds ?? [],
                    'depth' => $depth + 1,
                    'canManageFolders' => $canManageFolders ?? false,
                    'canEditDeleteFolders' => $canEditDeleteFolders ?? false,
                ])
            @endforeach
                        </ul>
    @endif
</li>
