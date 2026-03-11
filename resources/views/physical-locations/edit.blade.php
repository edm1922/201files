<x-app-layout>

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold">Edit Physical Location</h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Update details for <strong>{{ $physicalLocation->display_name }}</strong>.</p>
        </div>
        <a href="{{ route('settings.physical-locations.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- ── Form Card ── --}}
    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <div class="panel-section-title mb-3">Location Details</div>

            <form method="POST" action="{{ route('settings.physical-locations.update', $physicalLocation) }}">
                @csrf
                @method('PUT')

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
                               value="{{ old('cabinet_id', $physicalLocation->cabinet_id) }}"
                               maxlength="50"
                               required>
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
                               value="{{ old('rack_id', $physicalLocation->rack_id) }}"
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
                           value="{{ old('label', $physicalLocation->label) }}"
                           maxlength="255">
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
                        <i class="fas fa-save"></i> Update Location
                    </button>
                    <a href="{{ route('settings.physical-locations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>

        {{-- ── Meta Info ── --}}
        <div class="card-footer bg-white border-top px-4 py-3" style="border-radius: 0 0 10px 10px;">
            <div class="d-flex gap-4" style="font-size: 0.78rem; color: #9ca3af;">
                <span><i class="far fa-clock me-1"></i> Created: {{ $physicalLocation->created_at->format('M d, Y h:i A') }}</span>
                <span><i class="far fa-edit me-1"></i> Updated: {{ $physicalLocation->updated_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

</x-app-layout>
