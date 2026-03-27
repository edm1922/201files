@php
    $children = $foldersByParent->get((int) $folder->id, collect());
    $isActive = (int) $folder->id === (int) $currentFolderId;
@endphp

<div class="folder-node" style="padding-left: {{ $depth * 14 }}px;">
    <a href="{{ route('department-documents.index', ['department_id' => $selectedDepartmentId, 'document_folder_id' => $folder->id]) }}"
       class="folder-node__link {{ $isActive ? 'active' : '' }}">
        <i class="fas {{ $isActive ? 'fa-folder-open' : 'fa-folder' }} me-1"></i>
        <span>{{ $folder->name }}</span>
    </a>
</div>

@foreach($children as $child)
    @include('department-documents.partials.folder-tree-node', [
        'folder' => $child,
        'foldersByParent' => $foldersByParent,
        'selectedDepartmentId' => $selectedDepartmentId,
        'currentFolderId' => $currentFolderId,
        'depth' => $depth + 1,
    ])
@endforeach
