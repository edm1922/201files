<x-app-layout>
    <div x-data="userManager()">
        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold">User Management</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage system access, roles, and department permissions.</p>
            </div>
            <button class="btn btn-brand d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-plus"></i> Add User
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
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- ── Users Table ── --}}
        <div class="card shadow-sm">
            <div class="doc-table-wrapper" style="border: none;">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Name</th>
                            <th style="width: 130px;">Username</th>
                            <th style="width: 100px;">Role</th>
                            <th style="min-width: 200px;">Department Access</th>
                            <th style="width: 100px; text-align: center;">Status</th>
                            <th style="width: 140px; text-align: center;">Last Active</th>
                            <th style="width: 150px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-medium" style="color: #1e2328;">{{ $user->name }}
                                    @if(auth()->id() === $user->id) 
                                        <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">You</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted" style="font-size: 0.85rem; font-family: monospace;">{{ $user->username }}</div>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge" style="background: rgba(220, 38, 38, 0.1); color: #dc2626; padding: 5px 10px; font-weight: 600;">Admin</span>
                                    @elseif($user->role === 'encoder')
                                        <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; padding: 5px 10px; font-weight: 600;">Encoder</span>
                                    @else
                                        <span class="badge" style="background: rgba(75, 85, 99, 0.1); color: #4b5563; padding: 5px 10px; font-weight: 600;">Viewer</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="dept-badge dept-badge--all">
                                            <i class="fas fa-globe-americas me-1" style="font-size: 0.65rem;"></i>All Departments
                                        </span>
                                    @elseif($user->authorizedDepartments->isNotEmpty())
                                        <div class="dept-badges-wrap">
                                            @foreach($user->authorizedDepartments as $dept)
                                                <span class="dept-badge">{{ $dept->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.8rem; font-style: italic;">
                                            <i class="fas fa-exclamation-circle me-1" style="color: #f59e0b;"></i>No departments assigned
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($user->is_active)
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 5px 10px; font-weight: 600;">Active</span>
                                    @else
                                        <span class="badge" style="background: rgba(107, 114, 128, 0.1); color: #6b7280; padding: 5px 10px; font-weight: 600;">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-muted" style="font-size: 0.8rem;">
                                        {{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Edit --}}
                                        <button class="btn-doc-action"
                                                title="Edit user"
                                                @click="openEditModal({{ json_encode([
                                                    'id' => $user->id,
                                                    'first_name' => $user->first_name,
                                                    'middle_name' => $user->middle_name,
                                                    'last_name' => $user->last_name,
                                                    'suffix' => $user->suffix,
                                                    'username' => $user->username,
                                                    'role' => $user->role,
                                                    'department_ids' => $user->authorizedDepartments->pluck('id')->map(fn($id) => (int) $id)->values()->toArray(),
                                                ]) }})">
                                            <i class="fas fa-pen" style="font-size: 0.7rem;"></i>
                                        </button>

                                        {{-- Toggle Status --}}
                                        @if(auth()->id() !== $user->id)
                                            <button class="btn-doc-action"
                                                    title="{{ $user->is_active ? 'Deactivate user' : 'Activate user' }}"
                                                    style="border-color: {{ $user->is_active ? '#f59e0b' : '#10b981' }}; color: {{ $user->is_active ? '#f59e0b' : '#10b981' }};"
                                                    @click="openConfirmModal(
                                                        '{{ route('settings.users.toggle-status', $user) }}',
                                                        'PATCH',
                                                        '{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}',
                                                        'Are you sure you want to &lt;strong&gt;{{ $user->is_active ? 'deactivate' : 'activate' }}&lt;/strong&gt; user &lt;strong&gt;{{ addslashes($user->name) }}&lt;/strong&gt;?',
                                                        '{{ $user->is_active ? 'Deactivate' : 'Activate' }}',
                                                        '{{ $user->is_active ? 'warning' : 'success' }}',
                                                        '{{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}'
                                                    )">
                                                <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}" style="font-size: 0.7rem;"></i>
                                            </button>
                                        @endif

                                        {{-- Delete --}}
                                        @if(auth()->id() !== $user->id)
                                            <button class="btn-doc-action"
                                                    title="Delete user"
                                                    style="border-color: #ef4444; color: #ef4444;"
                                                    @click="openConfirmModal(
                                                        '{{ route('settings.users.destroy', $user) }}',
                                                        'DELETE',
                                                        'Delete User',
                                                        'Are you sure you want to delete user &lt;strong&gt;{{ addslashes($user->name) }}&lt;/strong&gt;? This action cannot be undone.',
                                                        'Delete',
                                                        'danger',
                                                        'fa-trash'
                                                    )">
                                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                            </button>
                                        @else
                                            <button class="btn-doc-action"
                                                    title="Cannot delete yourself"
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
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-users mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mb-0 mt-2">No users found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 0 0 10px 10px;">
                    <div class="text-muted" style="font-size: 0.8rem;">
                        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
                    </div>
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

        @include('users.create')
        @include('users.edit')
        @include('companies.confirm_modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManager', () => ({
                editUrl: '',
                editData: {
                    id: '',
                    first_name: '',
                    middle_name: '',
                    last_name: '',
                    suffix: '',
                    username: '',
                    role: '',
                    is_active: true,
                    department_ids: []
                },

                // Confirmation Modal Data for Delete
                confirmActionUrl: '',
                confirmMethod: 'POST',
                confirmTitle: 'Confirm',
                confirmMessage: 'Are you sure?',
                confirmButtonText: 'Confirm',
                confirmTheme: 'danger',
                confirmIcon: 'fa-exclamation-triangle',

                init() {
                    @if($errors->any() && old('_method') === 'PUT')
                        this.editUrl = '{{ url("settings/users") }}/{{ old("id") }}';
                        this.editData = {
                            id: '{{ old("id") }}',
                            first_name: '{!! addslashes(old("first_name")) !!}',
                            middle_name: '{!! addslashes(old("middle_name")) !!}',
                            last_name: '{!! addslashes(old("last_name")) !!}',
                            suffix: '{!! addslashes(old("suffix")) !!}',
                            username: '{!! addslashes(old("username")) !!}',
                            role: '{!! addslashes(old("role")) !!}',
                            is_active: {{ old('is_active', 1) ? 'true' : 'false' }},
                            department_ids: @json(old('department_ids', []))
                        };
                        var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                        modal.show();
                    @elseif($errors->any() && old('_method') !== 'PUT')
                        var modal = new bootstrap.Modal(document.getElementById('createUserModal'));
                        modal.show();
                    @endif
                },

                openEditModal(user) {
                    this.editData = { ...user };
                    this.editUrl = `{{ url('settings/users') }}/${user.id}`;
                    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
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
