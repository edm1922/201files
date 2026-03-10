<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Departments</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage cooperative-internal departments for categorizing document types.</p>
        </div>
        <a href="{{ route('settings.departments.create') }}" class="btn btn-brand d-inline-flex align-items-center gap-2">
            <i class="fas fa-plus"></i> Add Department
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
            <form method="GET" action="{{ route('settings.departments.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-5">
                    <label for="search" class="form-label" style="font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Search</label>
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text"
                               id="search"
                               name="search"
                               class="search-input"
                               placeholder="Search by department name…"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-brand flex-grow-1">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    <a href="{{ route('settings.departments.index') }}" class="btn btn-outline-secondary" title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Departments Table ── --}}
    <div class="card shadow-sm">
        <div class="doc-table-wrapper" style="border: none;">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 250px;">Department Name</th>
                        <th>Description</th>
                        <th style="width: 160px; text-align: center;">Document Types</th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td class="fw-medium" style="color: #1e2328;">{{ $department->name }}</td>
                            <td class="text-muted" style="font-size: 0.8rem;">
                                {{ Str::limit($department->description ?: 'No description provided.', 80) }}
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 5px 10px; font-weight: 600;">
                                    {{ $department->document_types_count }} Types
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    {{-- Edit --}}
                                    <a href="{{ route('settings.departments.edit', $department) }}"
                                       class="btn-doc-action"
                                       title="Edit department">
                                        <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                    </a>
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

</x-app-layout>
