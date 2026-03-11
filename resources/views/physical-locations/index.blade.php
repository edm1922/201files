<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Physical Locations</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage the physical cabinets and racks where physical documents are stored.</p>
        </div>
        <a href="{{ route('settings.physical-locations.create') }}" class="btn btn-brand d-inline-flex align-items-center gap-2">
            <i class="fas fa-plus"></i> Add Location
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
            <form method="GET" action="{{ route('settings.physical-locations.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-5">
                    <label for="search" class="form-label" style="font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Search</label>
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text"
                               id="search"
                               name="search"
                               class="search-input"
                               placeholder="Search by cabinet, rack, or label…"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-brand flex-grow-1">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    <a href="{{ route('settings.physical-locations.index') }}" class="btn btn-outline-secondary" title="Clear search">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Locations Table ── --}}
    <div class="card shadow-sm">
        <div class="doc-table-wrapper" style="border: none;">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">Cabinet ID</th>
                        <th style="width: 150px;">Rack ID</th>
                        <th>Label (Optional)</th>
                        <th style="width: 150px; text-align: center;">Stored Documents</th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($physicalLocations as $location)
                        <tr>
                            <td class="fw-medium" style="color: #1e2328;">{{ $location->cabinet_id }}</td>
                            <td class="fw-medium" style="color: #1e2328;">{{ $location->rack_id }}</td>
                            <td class="text-muted" style="font-size: 0.85rem;">
                                {{ $location->label ?: '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 5px 12px; font-weight: 600;">
                                    <i class="fas fa-file-alt me-1" style="font-size: 0.6rem;"></i> {{ $location->documents_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    {{-- Edit --}}
                                    <a href="{{ route('settings.physical-locations.edit', $location) }}"
                                       class="btn-doc-action"
                                       title="Edit location">
                                        <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-archive mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mb-0 mt-2">No physical locations found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($physicalLocations->hasPages())
            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 0 0 10px 10px;">
                <div class="text-muted" style="font-size: 0.8rem;">
                    Showing {{ $physicalLocations->firstItem() }}–{{ $physicalLocations->lastItem() }} of {{ $physicalLocations->total() }}
                </div>
                {{ $physicalLocations->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</x-app-layout>
