<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Edit Department</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Update details for <strong>{{ $department->name }}</strong>.</p>
        </div>
        <a href="{{ route('settings.departments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- ── Form Card ── --}}
    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <div class="panel-section-title mb-3">Department Details</div>

            <form method="POST" action="{{ route('settings.departments.update', $department) }}">
                @csrf
                @method('PUT')

                {{-- Department Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Department Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control field-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $department->name) }}"
                           placeholder="e.g. Finance, Human Resource"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Description
                    </label>
                    <textarea id="description"
                              name="description"
                              class="form-control field-input @error('description') is-invalid @enderror"
                              rows="3"
                              placeholder="Brief description of this department's function.">{{ old('description', $department->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" style="font-size: 0.78rem;">Optional. Maximum 1000 characters.</div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Update Department
                    </button>
                    <a href="{{ route('settings.departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
        
        {{-- ── Meta Info ── --}}
        <div class="card-footer bg-white border-top px-4 py-3" style="border-radius: 0 0 10px 10px;">
            <div class="d-flex gap-4" style="font-size: 0.78rem; color: #9ca3af;">
                <span><i class="far fa-clock me-1"></i> Created: {{ $department->created_at->format('M d, Y h:i A') }}</span>
                <span><i class="far fa-edit me-1"></i> Updated: {{ $department->updated_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

</x-app-layout>
