<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Edit Company</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Update details for <strong>{{ $company->name }}</strong>.</p>
        </div>
        <a href="{{ route('settings.companies.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- ── Form Card ── --}}
    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <div class="panel-section-title mb-3">Company Details</div>

            <form method="POST" action="{{ route('settings.companies.update', $company) }}">
                @csrf
                @method('PUT')

                {{-- Company Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Company Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control field-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $company->name) }}"
                           placeholder="e.g. ABC Corporation"
                           required
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Company Code --}}
                <div class="mb-3">
                    <label for="code" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Company Code <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="code"
                           name="code"
                           class="form-control field-input @error('code') is-invalid @enderror"
                           value="{{ old('code', $company->code) }}"
                           placeholder="e.g. COMP-ABC"
                           maxlength="20"
                           required
                           style="text-transform: uppercase;">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" style="font-size: 0.78rem;">A unique short code for this company (max 20 characters). Auto-uppercased.</div>
                </div>

                {{-- Status --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input"
                               type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                               role="switch"
                               style="cursor: pointer;">
                        <label class="form-check-label fw-semibold" for="is_active" style="font-size: 0.85rem; cursor: pointer;">
                            Active
                        </label>
                    </div>
                    <div class="form-text" style="font-size: 0.78rem;">Inactive companies won't appear in deployment dropdowns.</div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Update Company
                    </button>
                    <a href="{{ route('settings.companies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>

        {{-- ── Meta Info ── --}}
        <div class="card-footer bg-white border-top px-4 py-3" style="border-radius: 0 0 10px 10px;">
            <div class="d-flex gap-4" style="font-size: 0.78rem; color: #9ca3af;">
                <span><i class="far fa-clock me-1"></i> Created: {{ $company->created_at->format('M d, Y h:i A') }}</span>
                <span><i class="far fa-edit me-1"></i> Updated: {{ $company->updated_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

</x-app-layout>
