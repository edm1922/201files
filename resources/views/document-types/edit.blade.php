<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Edit Document Type</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Update details for <strong>{{ $documentType->name }}</strong>.</p>
        </div>
        <a href="{{ route('settings.document-types.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- ── Form Card ── --}}
    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <div class="panel-section-title mb-3">Document Type Details</div>

            <form method="POST" action="{{ route('settings.document-types.update', $documentType) }}">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control field-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $documentType->name) }}"
                           placeholder="e.g. SSS E1 Form, NBI Clearance"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Code --}}
                <div class="mb-3">
                    <label for="code" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Code <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="code"
                           name="code"
                           class="form-control field-input @error('code') is-invalid @enderror"
                           value="{{ old('code', $documentType->code) }}"
                           placeholder="e.g. SSS, NBI, PAGIBIG"
                           maxlength="20"
                           style="text-transform: uppercase;"
                           required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" style="font-size: 0.78rem;">Unique short code. Max 20 characters. Letters, numbers, dashes, and underscores only.</div>
                </div>

                <div class="row g-3 mb-3">
                    {{-- Target --}}
                    <div class="col-md-6">
                        <label for="target" class="form-label fw-semibold" style="font-size: 0.85rem;">
                            Target <span class="text-danger">*</span>
                        </label>
                        <select id="target"
                                name="target"
                                class="form-select field-input @error('target') is-invalid @enderror"
                                required>
                            <option value="employee" {{ old('target', $documentType->target) === 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="department" {{ old('target', $documentType->target) === 'department' ? 'selected' : '' }}>Department</option>
                        </select>
                        @error('target')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div class="col-md-6">
                        <label for="department_id" class="form-label fw-semibold" style="font-size: 0.85rem;">
                            Department
                        </label>
                        <select id="department_id"
                                name="department_id"
                                class="form-select field-input @error('department_id') is-invalid @enderror">
                            <option value="">— None —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $documentType->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" style="font-size: 0.78rem;">Optional. Categorize this type under a department.</div>
                    </div>
                </div>

                {{-- Max Pages --}}
                <div class="mb-3">
                    <label for="max_pages" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Max Pages <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           id="max_pages"
                           name="max_pages"
                           class="form-control field-input @error('max_pages') is-invalid @enderror"
                           value="{{ old('max_pages', $documentType->max_pages) }}"
                           min="1"
                           max="100"
                           style="max-width: 120px;"
                           required>
                    @error('max_pages')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" style="font-size: 0.78rem;">Maximum number of pages allowed for this document type.</div>
                </div>

                {{-- Boolean Toggles --}}
                <div class="row g-3 mb-4">
                    {{-- Has Expiry --}}
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="has_expiry"
                                   name="has_expiry"
                                   value="1"
                                   {{ old('has_expiry', $documentType->has_expiry) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="has_expiry" style="font-size: 0.85rem;">
                                <i class="fas fa-calendar-times me-1 text-muted"></i> Has Expiry Date
                            </label>
                        </div>
                        <div class="form-text ms-4" style="font-size: 0.78rem;">Enable if this document type can expire (e.g. NBI Clearance).</div>
                    </div>

                    {{-- Is Required --}}
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_required"
                                   name="is_required"
                                   value="1"
                                   {{ old('is_required', $documentType->is_required) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_required" style="font-size: 0.85rem;">
                                <i class="fas fa-asterisk me-1 text-danger" style="font-size: 0.6rem;"></i> Globally Required
                            </label>
                        </div>
                        <div class="form-text ms-4" style="font-size: 0.78rem;">If checked, every employee must have this document type on file.</div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Update Document Type
                    </button>
                    <a href="{{ route('settings.document-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>

        {{-- ── Meta Info ── --}}
        <div class="card-footer bg-white border-top px-4 py-3" style="border-radius: 0 0 10px 10px;">
            <div class="d-flex gap-4" style="font-size: 0.78rem; color: #9ca3af;">
                <span><i class="far fa-clock me-1"></i> Created: {{ $documentType->created_at->format('M d, Y h:i A') }}</span>
                <span><i class="far fa-edit me-1"></i> Updated: {{ $documentType->updated_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

</x-app-layout>
