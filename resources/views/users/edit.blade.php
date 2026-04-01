{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content border-0 shadow" :action="editUrl" method="POST">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="id" x-model="editData.id">
            
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="editUserModalLabel">
                    <i class="fas fa-user-edit text-brand me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body py-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_first_name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="edit_first_name" name="first_name" x-model="editData.first_name" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="edit_middle_name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Middle Name</label>
                        <input type="text" class="form-control @error('middle_name') is-invalid @enderror" id="edit_middle_name" name="middle_name" x-model="editData.middle_name">
                        @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="edit_last_name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="edit_last_name" name="last_name" x-model="editData.last_name" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="edit_suffix" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Suffix</label>
                        <input type="text" class="form-control @error('suffix') is-invalid @enderror" id="edit_suffix" name="suffix" x-model="editData.suffix">
                        @error('suffix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="edit_username" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="edit_username" name="username" x-model="editData.username" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="edit_role" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Role <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="edit_role" name="role" x-model="editData.role" required
                            @change="editData.role = $event.target.value">
                        <option value="viewer">Viewer</option>
                        <option value="encoder">Encoder</option>
                        <option value="admin">Admin</option>
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Department Access Section --}}
                <div x-show="editData.role !== 'admin'" x-transition>
                    <div class="dept-access-panel">
                        <div class="dept-access-header">
                            <i class="fas fa-building me-2"></i>Department Access
                        </div>
                        <p class="text-muted mb-2" style="font-size: 0.8rem;">
                            Select which departments this user can access. Leave empty for no access.
                        </p>
                        @error('department_ids')<div class="text-danger mb-2" style="font-size: 0.8rem;">{{ $message }}</div>@enderror
                        <div class="dept-checkbox-grid">
                            @foreach($departments as $dept)
                                <label class="dept-checkbox-item">
                                    <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}"
                                           :checked="editData.department_ids && editData.department_ids.includes({{ $dept->id }})">
                                    <span class="dept-checkbox-label">{{ $dept->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if($departments->isEmpty())
                            <p class="text-muted text-center py-2 mb-0" style="font-size: 0.8rem;">
                                <i class="fas fa-info-circle me-1"></i>No active departments found. Create departments first.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Password Reset Notice --}}
                <div class="mt-4 p-3 bg-light rounded-2 border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 fw-bold text-dark" style="font-size: 0.85rem;">Reset User Password</p>
                            <p class="mb-0 text-muted" style="font-size: 0.8rem;">Forces the user to use the default password <code>{lastname}csc</code> and change it on next login.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger shadow-sm"
                                @click="openConfirmModal(
                                    '{{ url('settings/users') }}/' + editData.id + '/reset-password',
                                    'POST',
                                    'Reset Password to Default',
                                    'Are you sure you want to reset the password for <strong>' + editData.first_name + ' ' + editData.last_name + '</strong> back to the default?',
                                    'Reset Password',
                                    'danger',
                                    'fa-key'
                                )">
                            <i class="fas fa-undo-alt me-1"></i> Reset
                        </button>
                    </div>
                </div>
                
            </div>
            
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-brand px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>
