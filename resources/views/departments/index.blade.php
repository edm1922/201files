<x-app-layout>

    <div x-data="departmentManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Departments</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage cooperative-internal departments for categorizing document types.</p>
            </div>
            <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createDepartmentModal">
                <i class="fas fa-plus"></i> Add Department
            </button>
        </div>

        {{-- ── Flash Messages ── --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-left: 4px solid #27ae60; border-radius: 8px;">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ── Departments Table ── --}}
        <div class="card shadow-sm">
            <div class="doc-table-wrapper" style="border: none;">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th style="width: 250px;">Department Name</th>
                            <th style="width: 150px;">Code / Folder ID</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 160px; text-align: center;">Document Types</th>
                            <th style="width: 140px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr>
                                <td class="fw-medium" style="color: #1e2328;">{{ $department->name }}</td>
                                <td>
                                    <div class="fw-bold" style="font-size: 0.85rem; color: #374151;">{{ $department->code ?? '—' }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem; font-family: monospace;">{{ $department->folder_code ?? '—' }}</div>
                                </td>
                                <td>
                                    @if($department->folderLocation)
                                        <div class="text-danger fw-semibold" style="font-size: 0.8rem; font-family: monospace;">
                                            {{ $department->folderLocation->full_location }}
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.8rem;">— No Location —</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 0.8rem;">
                                    {{ Str::limit($department->description ?: 'No description provided.', 80) }}
                                </td>
                                <td class="text-center">
                                    @if($department->is_active)
                                        <span class="badge rounded-pill" style="background: #e8f5e9; color: #2e7d32; font-size: 0.75rem; padding: 5px 12px;">
                                            <i class="fas fa-circle me-1" style="font-size: 0.45rem; vertical-align: middle;"></i> Active
                                        </span>
                                    @else
                                        <span class="badge rounded-pill" style="background: #fce4ec; color: #c62828; font-size: 0.75rem; padding: 5px 12px;">
                                            <i class="fas fa-circle me-1" style="font-size: 0.45rem; vertical-align: middle;"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 5px 10px; font-weight: 600;">
                                        {{ $department->document_types_count }} Types
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Edit --}}
                                        <button type="button" 
                                                class="btn-doc-action"
                                                title="Edit department"
                                                @click="openEditModal({{ json_encode([
                                                    'id' => $department->id,
                                                    'name' => $department->name,
                                                    'code' => $department->code,
                                                    'folder_code' => $department->folder_code,
                                                    'folder_location_id' => $department->folder_location_id,
                                                    'description' => $department->description,
                                                    'is_active' => $department->is_active,
                                                    'updated_at' => $department->updated_at->format('M d, Y h:i A')
                                                ]) }})">
                                            <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                        </button>

                                        {{-- Toggle Active --}}
                                        <button type="button"
                                                class="btn-doc-action"
                                                title="{{ $department->is_active ? 'Deactivate' : 'Reactivate' }}"
                                                style="border-color: {{ $department->is_active ? '#e74c3c' : '#27ae60' }}; color: {{ $department->is_active ? '#e74c3c' : '#27ae60' }};"
                                                @click="openConfirmModal(
                                                    '{{ route('settings.departments.toggle-active', $department) }}',
                                                    'PATCH',
                                                    '{{ $department->is_active ? 'Deactivate Department' : 'Reactivate Department' }}',
                                                    'Are you sure you want to {{ $department->is_active ? 'deactivate' : 'reactivate' }} &lt;strong&gt;{{ addslashes($department->name) }}&lt;/strong&gt;? {{ $department->is_active ? 'It will no longer appear as an option for new documents.' : 'It will be available for new documents again.' }}',
                                                    '{{ $department->is_active ? 'Deactivate' : 'Reactivate' }}',
                                                    '{{ $department->is_active ? 'danger' : 'success' }}',
                                                    '{{ $department->is_active ? 'fa-ban' : 'fa-check' }}'
                                                )">
                                            <i class="fas {{ $department->is_active ? 'fa-ban' : 'fa-check' }}" style="font-size: 0.7rem;"></i>
                                        </button>

                                        {{-- Delete --}}
                                        @if($department->document_types_count == 0 && $department->documents_count == 0)
                                            <button type="button"
                                                    class="btn-doc-action"
                                                    title="Delete department"
                                                    style="border-color: #ef4444; color: #ef4444;"
                                                    @click="openConfirmModal(
                                                        '{{ route('settings.departments.destroy', $department) }}',
                                                        'DELETE',
                                                        'Delete Department',
                                                        'Are you sure you want to permanently delete &lt;strong&gt;{{ addslashes($department->name) }}&lt;/strong&gt;? This action cannot be undone.',
                                                        'Delete',
                                                        'danger',
                                                        'fa-trash'
                                                    )">
                                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn-doc-action"
                                                    title="Cannot delete departments with active documents or types"
                                                    style="border-color: #d1d5db; color: #9ca3af; cursor: not-allowed;"
                                                    disabled>
                                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fas fa-sitemap mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mb-0 mt-2">No departments found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($departments->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 0 0 10px 10px;">
                    <div class="text-muted" style="font-size: 0.8rem;">
                        Showing {{ $departments->firstItem() }}–{{ $departments->lastItem() }} of {{ $departments->total() }}
                    </div>
                    {{ $departments->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

        @include('departments.create')
        @include('departments.edit')
        @include('companies.confirm_modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('departmentManager', () => ({
                // Edit Modal Data
                editUrl: '',
                editData: {
                    id: '',
                    name: '',
                    code: '',
                    folder_code: '',
                    folder_location_id: '',
                    description: '',
                    updated_at: ''
                },

                // Confirmation Modal Data (Toggle/Delete)
                confirmActionUrl: '',
                confirmMethod: 'POST',
                confirmTitle: 'Confirm',
                confirmMessage: 'Are you sure?',
                confirmButtonText: 'Confirm',
                confirmTheme: 'brand', // brand, danger, success
                confirmIcon: 'fa-exclamation-triangle',

                init() {
                    // Check for edit form validation errors via spatie/laravel-html or old inputs later if needed
                    @if($errors->any() && old('_method') === 'PUT')
                        this.editUrl = '{{ url("settings/departments") }}/{{ old("id") }}';
                        this.editData = {
                            id: '{{ old("id") }}',
                            name: '{!! addslashes(old("name")) !!}',
                            code: '{!! addslashes(old("code")) !!}',
                            folder_location_id: '{{ old("folder_location_id") }}',
                            description: '{!! addslashes(old("description")) !!}',
                            is_active: {{ old('is_active') ? 'true' : 'false' }},
                            updated_at: ''
                        };
                    @endif
                },

                openEditModal(department) {
                    this.editData = { ...department };
                    this.editUrl = `{{ url('settings/departments') }}/${department.id}`;
                    var modal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
                    modal.show();
                },

                openConfirmModal(url, method, title, message, btnText, theme, icon) {
                    this.confirmActionUrl = url;
                    this.confirmMethod = method;
                    this.confirmTitle = title;
                    this.confirmMessage = message;
                    this.confirmButtonText = btnText;
                    this.confirmTheme = theme;
                    this.confirmIcon = icon;
                    var modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    modal.show();
                }
            }));
        });
    </script>
</x-app-layout>
