<x-app-layout>

    {{-- ── Main Container ── --}}
    <div class="px-4 py-3">

        {{-- ── Header & Tabs Container ── --}}
        <div class="mb-4 border-bottom pb-3">
            <div class="mb-3">
                <h2 class="h4 mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-box-archive text-muted"></i> System Archives
                </h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage and restore archived records across the
                    system.</p>
            </div>

            {{-- ── Tabs ── --}}
            <ul class="nav nav-tabs border-0" style="margin-bottom: -16px;">
                <li class="nav-item">
                    <a href="{{ route('archives.index', ['tab' => 'employees']) }}"
                        class="nav-link border-0 text-decoration-none fw-semibold transition-colors duration-200"
                        style="font-size: 0.95rem; padding: 10px 20px; border-bottom: 3px solid {{ $tab === 'employees' ? config('brand.primary_color') : 'transparent' }} !important; color: {{ $tab === 'employees' ? config('brand.primary_color') : '#64748b' }};">
                        <i class="fas fa-users me-2"></i>201 Files
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('archives.index', ['tab' => 'documents']) }}"
                        class="nav-link border-0 text-decoration-none fw-semibold transition-colors duration-200"
                        style="font-size: 0.95rem; padding: 10px 20px; border-bottom: 3px solid {{ $tab === 'documents' ? config('brand.primary_color') : 'transparent' }} !important; color: {{ $tab === 'documents' ? config('brand.primary_color') : '#64748b' }};">
                        <i class="fas fa-file-contract me-2"></i>Department Documents
                    </a>
                </li>
            </ul>
        </div>

        {{-- ── Flash Messages ── --}}
        @if (session('success'))
            <div class="alert-flash alert-flash--success mb-4" data-flash-success>
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert-flash alert-flash--error mb-4" data-flash-error>
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- ── Dynamic Content ── --}}
        <div class="animate-fade-in stagger-1">
            @if ($tab === 'employees')
                @include('archives.partials.employee-table')
            @elseif($tab === 'documents')
                @include('archives.partials.document-table')
            @endif
        </div>

    </div>

    @if($tab === 'documents')
        @include('archives.partials.preview')
    @endif

    @push('styles')
        <style>
            .transition-colors {
                transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            }

            .duration-200 {
                transition-duration: 200ms;
            }

            .nav-tabs .nav-link:hover {
                color: {{ config('brand.primary_color') }} !important;
                background-color: transparent !important;
                border-color: transparent !important;
                border-bottom: 3px solid rgba(221, 39, 13, 0.3) !important;
            }
        </style>
    @endpush

</x-app-layout>
