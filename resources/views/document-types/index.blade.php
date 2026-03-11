<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Document Types</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage the classification types used to categorize uploaded documents.</p>
        </div>
        <a href="{{ route('settings.document-types.create') }}" class="btn btn-brand d-inline-flex align-items-center gap-2">
            <i class="fas fa-plus"></i> Add Document Type
        </a>
    </div>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-left: 4px solid #27ae60; border-radius: 8px;">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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

                {{-- Target Filter --}}
                <div class="col-md-2">
                    <label for="target" class="form-label" style="font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Target</label>
                    <select id="target" name="target" class="form-select field-input" style="font-size: 0.85rem;">
                        <option value="">All Targets</option>
                        <option value="employee" {{ request('target') === 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="department" {{ request('target') === 'department' ? 'selected' : '' }}>Department</option>
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
                        <th style="width: 110px; text-align: center;">Target</th>
                        <th style="width: 150px;">Department</th>
                        <th style="width: 90px; text-align: center;">Expiry?</th>
                        <th style="width: 90px; text-align: center;">Required?</th>
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
                            <td class="text-center">
                                <span class="badge" style="background: {{ $docType->target === 'employee' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(139, 92, 246, 0.1)' }}; color: {{ $docType->target === 'employee' ? '#3b82f6' : '#8b5cf6' }}; padding: 5px 10px; font-weight: 600;">
                                    {{ ucfirst($docType->target) }}
                                </span>
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
                            <td class="text-center">
                                @if($docType->is_required)
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 4px 8px; font-weight: 600;">Required</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.8rem;">Optional</span>
                                @endif
                            </td>
                            <td class="text-center" style="font-size: 0.85rem;">{{ $docType->max_pages }}</td>
                            <td class="text-center">
                                <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 5px 10px; font-weight: 600;">
                                    {{ $docType->documents_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('settings.document-types.edit', $docType) }}"
                                       class="btn-doc-action"
                                       title="Edit document type">
                                        <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                    </a>
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

</x-app-layout>
