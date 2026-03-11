<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Add Physical Location</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Define a new physical cabinet and rack for document storage.</p>
        </div>
        <a href="{{ route('settings.physical-locations.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- ── Form Card ── --}}
    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <div class="panel-section-title mb-3">Location Details</div>

            <form method="POST" action="{{ route('settings.physical-locations.store') }}">
                @csrf

                <div class="row g-3 mb-3">
                    {{-- Cabinet ID --}}
                    <div class="col-md-6">
                        <label for="cabinet_id" class="form-label fw-semibold" style="font-size: 0.85rem;">
                            Cabinet ID <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="cabinet_id"
                               name="cabinet_id"
                               class="form-control field-input @error('cabinet_id') is-invalid @enderror"
                               value="{{ old('cabinet_id') }}"
                               placeholder="e.g. CAB-01"
                               maxlength="50"
                               required
                               autofocus>
                        @error('cabinet_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Rack ID --}}
                    <div class="col-md-6">
                        <label for="rack_id" class="form-label fw-semibold" style="font-size: 0.85rem;">
                            Rack ID <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="rack_id"
                               name="rack_id"
                               class="form-control field-input @error('rack_id') is-invalid @enderror"
                               value="{{ old('rack_id') }}"
                               placeholder="e.g. RACK-A"
                               maxlength="50"
                               required>
                        @error('rack_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-text mb-4" style="font-size: 0.78rem;">
                    <i class="fas fa-info-circle text-primary me-1"></i> The combination of Cabinet ID and Rack ID must be <strong>unique</strong>.
                </div>

                {{-- Label --}}
                <div class="mb-4">
                    <label for="label" class="form-label fw-semibold" style="font-size: 0.85rem;">
                        Label / Description
                    </label>
                    <input type="text"
                           id="label"
                           name="label"
                           class="form-control field-input @error('label') is-invalid @enderror"
                           value="{{ old('label') }}"
                           placeholder="Optional descriptive label (e.g. HR Archives 2020-2025)"
                           maxlength="255">
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Save Location
                    </button>
                    <a href="{{ route('settings.physical-locations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
