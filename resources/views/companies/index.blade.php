<x-app-layout>

    <div x-data="companyManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Companies</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage client companies where employees are deployed.</p>
            </div>
            <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
                <i class="fas fa-plus"></i> Add Company
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
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-left: 4px solid #e74c3c; border-radius: 8px;">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ── Companies Table ── --}}
        <div class="card shadow-sm">
            <div class="doc-table-wrapper" style="border: none;">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Code</th>
                            <th>Company Name</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 140px; text-align: center;">Active Employees</th>
                            <th style="width: 150px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr>
                                <td>
                                    <span class="doc-type-cell" style="cursor: default;">{{ $company->code }}</span>
                                </td>
                                <td class="fw-medium">{{ $company->name }}</td>
                                <td class="text-center">
                                    @if($company->is_active)
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
                                    <span class="fw-semibold" style="color: #374151;">{{ $company->active_employees_count }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Edit --}}
                                        <button type="button" 
                                                class="btn-doc-action"
                                                title="Edit company"
                                                @click="openEditModal({{ json_encode([
                                                    'id' => $company->id,
                                                    'name' => $company->name,
                                                    'code' => $company->code,
                                                    'is_active' => $company->is_active,
                                                    'updated_at' => $company->updated_at->format('M d, Y h:i A')
                                                ]) }})">
                                            <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                        </button>

                                        {{-- Toggle Active --}}
                                        <button type="button"
                                                class="btn-doc-action"
                                                title="{{ $company->is_active ? 'Deactivate' : 'Reactivate' }}"
                                                style="border-color: {{ $company->is_active ? '#e74c3c' : '#27ae60' }}; color: {{ $company->is_active ? '#e74c3c' : '#27ae60' }};"
                                                @click="openConfirmModal(
                                                    '{{ route('settings.companies.toggle-active', $company) }}',
                                                    'PATCH',
                                                    '{{ $company->is_active ? 'Deactivate Company' : 'Reactivate Company' }}',
                                                    'Are you sure you want to {{ $company->is_active ? 'deactivate' : 'reactivate' }} &lt;strong&gt;{{ addslashes($company->name) }}&lt;/strong&gt;? {{ $company->is_active ? 'It will no longer appear as an option for new assignments.' : 'It will be available for new assignments again.' }}',
                                                    '{{ $company->is_active ? 'Deactivate' : 'Reactivate' }}',
                                                    '{{ $company->is_active ? 'danger' : 'success' }}',
                                                    '{{ $company->is_active ? 'fa-ban' : 'fa-check' }}'
                                                )">
                                            <i class="fas {{ $company->is_active ? 'fa-ban' : 'fa-check' }}" style="font-size: 0.7rem;"></i>
                                        </button>

                                        {{-- Delete --}}
                                        @if($company->active_employees_count == 0)
                                            <button type="button"
                                                    class="btn-doc-action"
                                                    title="Delete company"
                                                    style="border-color: #ef4444; color: #ef4444;"
                                                    @click="openConfirmModal(
                                                        '{{ route('settings.companies.destroy', $company) }}',
                                                        'DELETE',
                                                        'Delete Company',
                                                        'Are you sure you want to permanently delete &lt;strong&gt;{{ addslashes($company->name) }}&lt;/strong&gt;? This action cannot be undone.',
                                                        'Delete',
                                                        'danger',
                                                        'fa-trash'
                                                    )">
                                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn-doc-action"
                                                    title="Cannot delete companies with active employees"
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
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-briefcase mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mb-0 mt-2">No companies found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($companies->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 0 0 10px 10px;">
                    <div class="text-muted" style="font-size: 0.8rem;">
                        Showing {{ $companies->firstItem() }}–{{ $companies->lastItem() }} of {{ $companies->total() }}
                    </div>
                    {{ $companies->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

        @include('companies.create')
        @include('companies.edit')
        @include('companies.confirm_modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('companyManager', () => ({
                // Edit Modal Data
                editUrl: '',
                editData: {
                    id: '',
                    name: '',
                    code: '',
                    is_active: false,
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
                        this.editUrl = '{{ url("settings/companies") }}/{{ old("id") }}';
                        this.editData = {
                            id: '{{ old("id") }}',
                            name: '{!! addslashes(old("name")) !!}',
                            code: '{!! addslashes(old("code")) !!}',
                            is_active: {{ old('is_active') ? 'true' : 'false' }},
                            updated_at: ''
                        };
                    @endif
                },

                openEditModal(company) {
                    this.editData = { ...company };
                    this.editUrl = `{{ url('settings/companies') }}/${company.id}`;
                    var modal = new bootstrap.Modal(document.getElementById('editCompanyModal'));
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
