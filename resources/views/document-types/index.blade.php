<x-app-layout>

    <div x-data="documentTypeManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Document Types</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage the classification types used to categorize uploaded documents.</p>
            </div>
            <button class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createDocumentTypeModal">
                <i class="fas fa-plus"></i> Add Document Type
            </button>
        </div>

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

    {{-- ── Filters Card ── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('settings.document-types.index') }}" class="row g-3 align-items-end">
                {{-- Search --}}
                <div class="col-md-4">
                    <label for="search" class="form-label" style="font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Search</label>
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text"
                               id="search"
                               name="search"
                               class="search-input"
                               placeholder="Search by name or code…"
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Department Filter --}}
                <div class="col-md-3">
                    <label for="department_id" class="form-label" style="font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Department</label>
                    <select id="department_id" name="department_id" class="form-select field-input" style="font-size: 0.85rem;">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>



                {{-- Buttons --}}
                <div class="col-md-3 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-brand flex-grow-1">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('settings.document-types.index') }}" class="btn btn-outline-secondary" title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Document Types Table ── --}}
    <div class="card shadow-sm">
        <div class="doc-table-wrapper" style="border: none;">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 200px;">Name</th>
                        <th style="width: 100px; text-align: center;">Code</th>

                        <th style="width: 150px;">Department</th>
                        <th style="width: 90px; text-align: center;">Expiry?</th>

                        <th style="width: 90px; text-align: center;">Max Pages</th>
                        <th style="width: 100px; text-align: center;">Documents</th>
                        <th style="width: 80px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentTypes as $docType)
                        <tr>
                            <td class="fw-medium" style="color: #1e2328;">{{ $docType->name }}</td>
                            <td class="text-center">
                                <code style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.78rem; font-weight: 600;">
                                    {{ $docType->code }}
                                </code>
                            </td>

                            <td class="text-muted" style="font-size: 0.8rem;">
                                {{ $docType->department?->name ?? '—' }}
                            </td>
                            <td class="text-center">
                                @if($docType->has_expiry)
                                    <i class="fas fa-check-circle text-success" title="Has expiry tracking"></i>
                                @else
                                    <i class="fas fa-minus-circle" style="color: #d1d5db;" title="No expiry"></i>
                                @endif
                            </td>

                            <td class="text-center" style="font-size: 0.85rem;">{{ $docType->max_pages }}</td>
                            <td class="text-center">
                                <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 5px 10px; font-weight: 600;">
                                    {{ $docType->documents_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" 
                                       class="btn-doc-action"
                                       title="Edit document type"
                                       @click="openEditModal({{ json_encode([
                                            'id' => $docType->id,
                                            'name' => $docType->name,
                                            'code' => $docType->code,
                                            'department_id' => $docType->department_id,
                                            'has_expiry' => (bool)$docType->has_expiry,
                                            'max_pages' => $docType->max_pages
                                       ]) }})">
                                        <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                    </button>

                                    @if($docType->documents_count == 0)
                                        <button type="button"
                                                class="btn-doc-action"
                                                title="Delete document type"
                                                style="border-color: #ef4444; color: #ef4444;"
                                                @click="openConfirmModal(
                                                    '{{ route('settings.document-types.destroy', $docType) }}',
                                                    'DELETE',
                                                    'Delete Document Type',
                                                    'Are you sure you want to permanently delete &lt;strong&gt;{{ addslashes($docType->name) }}&lt;/strong&gt;? This action cannot be undone.',
                                                    'Delete',
                                                    'danger',
                                                    'fa-trash'
                                                )">
                                            <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn-doc-action"
                                                title="Cannot delete document types with active documents"
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
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-file-alt mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mb-0 mt-2">No document types found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($documentTypes->hasPages())
            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 0 0 10px 10px;">
                <div class="text-muted" style="font-size: 0.8rem;">
                    Showing {{ $documentTypes->firstItem() }}–{{ $documentTypes->lastItem() }} of {{ $documentTypes->total() }}
                </div>
                {{ $documentTypes->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @include('document-types.create')
    @include('document-types.edit')
    @include('companies.confirm_modal')

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('documentTypeManager', () => ({
                // Edit Modal Data
                editUrl: '',
                editData: {
                    id: '',
                    name: '',
                    code: '',
                    department_id: '',
                    has_expiry: false,
                    max_pages: 1
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
                    // Reopen edit modal with old inputs if validation fails on update
                    @if($errors->any() && old('_method') === 'PUT')
                        this.editUrl = '{{ url("settings/document-types") }}/{{ old("id") }}';
                        this.editData = {
                            id: '{{ old("id") }}',
                            name: '{!! addslashes(old("name")) !!}',
                            code: '{!! addslashes(old("code")) !!}',
                            department_id: '{{ old("department_id") }}',
                            has_expiry: {{ old('has_expiry') ? 'true' : 'false' }},
                            max_pages: {{ old('max_pages', 1) }}
                        };
                        setTimeout(() => {
                            var modal = new bootstrap.Modal(document.getElementById('editDocumentTypeModal'));
                            modal.show();
                        }, 100);
                    @endif
                },

                openEditModal(docType) {
                    this.editData = { ...docType };
                    this.editUrl = `{{ url('settings/document-types') }}/${docType.id}`;
                    var modal = new bootstrap.Modal(document.getElementById('editDocumentTypeModal'));
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
