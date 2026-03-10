<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Add Department</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Create a new department to categorize documents.</p>
        </div>
        <a href="{{ route('settings.departments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- ── Form Card ── --}}
    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <div class="panel-section-title mb-3">Department Details</div>

            <form method="POST" action="{{ route('settings.departments.store') }}">
                @csrf

                {{-- Department Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Department Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control field-input @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="e.g. Finance, Human Resource"
                           required
                           autofocus>
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
                              placeholder="Brief description of this department's function.">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" style="font-size: 0.78rem;">Optional. Maximum 1000 characters.</div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Save Department
                    </button>
                    <a href="{{ route('settings.departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
