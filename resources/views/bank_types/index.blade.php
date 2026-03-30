<x-app-layout>

    <div x-data="bankTypeManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">Bank Types</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage bank types available for employees.</p>
            </div>
            <button type="button" class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createBankTypeModal">
                <i class="fas fa-plus"></i> Add Bank Type
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

        {{-- ── Bank Types Table ── --}}
        <div class="card shadow-sm">
            <div class="doc-table-wrapper" style="border: none;">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 150px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bankTypes as $bankType)
                            <tr>
                                <td class="fw-medium">{{ $bankType->name }}</td>
                                <td class="text-center">
                                    @if($bankType->is_active)
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
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Edit --}}
                                        <button type="button" 
                                                class="btn-doc-action"
                                                title="Edit bank type"
                                                @click="openEditModal({{ json_encode([
                                                    'id' => $bankType->id,
                                                    'name' => $bankType->name,
                                                    'is_active' => $bankType->is_active,
                                                    'updated_at' => $bankType->updated_at->format('M d, Y h:i A')
                                                ]) }})">
                                            <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                        </button>

                                        {{-- Toggle Active --}}
                                        <button type="button"
                                                class="btn-doc-action"
                                                title="{{ $bankType->is_active ? 'Deactivate' : 'Reactivate' }}"
                                                style="border-color: {{ $bankType->is_active ? '#e74c3c' : '#27ae60' }}; color: {{ $bankType->is_active ? '#e74c3c' : '#27ae60' }};"
                                                @click="openConfirmModal(
                                                    '{{ route('settings.bank-types.toggle-active', $bankType) }}',
                                                    'PATCH',
                                                    '{{ $bankType->is_active ? 'Deactivate Bank Type' : 'Reactivate Bank Type' }}',
                                                    'Are you sure you want to {{ $bankType->is_active ? 'deactivate' : 'reactivate' }} &lt;strong&gt;{{ addslashes($bankType->name) }}&lt;/strong&gt;? {{ $bankType->is_active ? 'It will no longer appear as an option for employees.' : 'It will be available for employee assignment again.' }}',
                                                    '{{ $bankType->is_active ? 'Deactivate' : 'Reactivate' }}',
                                                    '{{ $bankType->is_active ? 'danger' : 'success' }}',
                                                    '{{ $bankType->is_active ? 'fa-ban' : 'fa-check' }}'
                                                )">
                                            <i class="fas {{ $bankType->is_active ? 'fa-ban' : 'fa-check' }}" style="font-size: 0.7rem;"></i>
                                        </button>

                                        {{-- Delete --}}
                                        <button type="button"
                                                class="btn-doc-action"
                                                title="Delete bank type"
                                                style="border-color: #ef4444; color: #ef4444;"
                                                @click="openConfirmModal(
                                                    '{{ route('settings.bank-types.destroy', $bankType) }}',
                                                    'DELETE',
                                                    'Delete Bank Type',
                                                    'Are you sure you want to permanently delete &lt;strong&gt;{{ addslashes($bankType->name) }}&lt;/strong&gt;? This action cannot be undone.',
                                                    'Delete',
                                                    'danger',
                                                    'fa-trash'
                                                )">
                                            <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">
                                    <i class="fas fa-university mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mb-0 mt-2">No bank types found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($bankTypes->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 0 0 10px 10px;">
                    <div class="text-muted" style="font-size: 0.8rem;">
                        Showing {{ $bankTypes->firstItem() }}–{{ $bankTypes->lastItem() }} of {{ $bankTypes->total() }}
                    </div>
                    {{ $bankTypes->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

        @include('bank_types.create')
        @include('bank_types.edit')
        @include('bank_types.confirm_modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bankTypeManager', () => ({
                // Edit Modal Data
                editUrl: '',
                editData: {
                    id: '',
                    name: '',
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
                    @if($errors->any() && old('_method') === 'PUT')
                        this.editUrl = '{{ url("settings/bank-types") }}/{{ old("id") }}';
                        this.editData = {
                            id: '{{ old("id") }}',
                            name: '{!! addslashes(old("name")) !!}',
                            is_active: {{ old('is_active') ? 'true' : 'false' }},
                            updated_at: ''
                        };
                    @endif
                },

                openEditModal(bankType) {
                    this.editData = { ...bankType };
                    this.editUrl = `{{ url('settings/bank-types') }}/${bankType.id}`;
                    var modal = new bootstrap.Modal(document.getElementById('editBankTypeModal'));
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
