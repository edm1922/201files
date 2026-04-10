{{-- Create User Modal --}}
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content border-0 shadow" action="{{ route('settings.users.store') }}" method="POST">
            @csrf
            
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="createUserModalLabel">
                    <i class="fas fa-user-plus text-brand me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body py-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="e.g. John">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="middle_name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Middle Name</label>
                        <input type="text" class="form-control @error('middle_name') is-invalid @enderror" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder="e.g. Smith">
                        @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="last_name" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="e.g. Doe">
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="suffix" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Suffix</label>
                        <input type="text" class="form-control @error('suffix') is-invalid @enderror" id="suffix" name="suffix" value="{{ old('suffix') }}" placeholder="e.g. Jr., Sr.">
                        @error('suffix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required placeholder="e.g. jdoe">
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Role <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required
                            onchange="document.getElementById('createDeptSection').style.display = this.value === 'admin' ? 'none' : 'block'">
                        <option value="viewer" {{ old('role') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                        <option value="encoder" {{ old('role') === 'encoder' ? 'selected' : '' }}>Encoder</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                        <label class="form-check-label fw-semibold text-muted" for="is_active" style="font-size: 0.85rem; margin-bottom: 0;">Account Active</label>
                        <div style="margin-top: -2px;">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input ms-0" type="checkbox" name="is_active" id="is_active" 
                                   value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.4em; cursor: pointer;">
                        </div>
                    </div>
                </div>

                {{-- Department Access Section --}}
                <div id="createDeptSection" style="{{ old('role') === 'admin' ? 'display:none;' : '' }}">
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
                                           {{ in_array($dept->id, old('department_ids', [])) ? 'checked' : '' }}>
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
                
                <div class="alert alert-info mt-3" role="alert" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle me-1"></i> A default password will be automatically generated as <strong>{last_name}csc</strong>. The user will be required to change this upon their first login.
                </div>
            </div>
            
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-brand px-4">Create User</button>
            </div>
        </form>
    </div>
</div>
