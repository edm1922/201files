{{-- ── Create Physical Location Modal ── --}}
<div class="modal fade" id="createLocationModal" tabindex="-1" aria-labelledby="createLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" action="{{ route('settings.physical-locations.store') }}">
                @csrf
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold" id="createLocationModalLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Add Physical Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-size: 0.8em; opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pt-2">
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">Define a new rack inside a physical cabinet.</p>

                    <div class="row g-3 mb-3">
                        {{-- Cabinet ID --}}
                        <div class="col-md-6">
                            <label for="cabinet_id" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                                Cabinet ID <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="cabinet_id"
                                   name="cabinet_id"
                                   class="form-control field-input @error('cabinet_id') is-invalid @enderror"
                                   value="{{ old('cabinet_id') }}"
                                   placeholder="e.g. Cabinet 1"
                                   maxlength="50"
                                   required
                                   autofocus
                                   style="background-color: #f9fafb;">
                            @error('cabinet_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Rack ID --}}
                        <div class="col-md-6">
                            <label for="rack_id" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                                Rack ID <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="rack_id"
                                   name="rack_id"
                                   class="form-control field-input @error('rack_id') is-invalid @enderror"
                                   value="{{ old('rack_id') }}"
                                   placeholder="e.g. A1"
                                   maxlength="50"
                                   required
                                   style="background-color: #f9fafb;">
                            @error('rack_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-text mb-2" style="font-size: 0.78rem; color: #6b7280;">
                        <i class="fas fa-info-circle text-primary me-1"></i> The combination of Cabinet ID and Rack ID must be <strong>unique</strong>.
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2" style="background-color: #ffffff;">
                    <button type="button" class="btn btn-light" style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 18px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" data-bs-dismiss="modal" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='#f3f4f6'">Cancel</button>
                    <button type="submit" class="btn text-white d-inline-flex align-items-center gap-2" style="background-color: #dd270d; font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 20px; border: none; box-shadow: 0 4px 6px -1px rgba(221, 39, 13, 0.2), 0 2px 4px -1px rgba(221, 39, 13, 0.1); transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px rgba(221, 39, 13, 0.3), 0 4px 6px -1px rgba(221, 39, 13, 0.15)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px rgba(221, 39, 13, 0.2), 0 2px 4px -1px rgba(221, 39, 13, 0.1)'">
                        <i class="fas fa-save"></i> Save Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any() && !old('_method'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var createModal = new bootstrap.Modal(document.getElementById('createLocationModal'));
            createModal.show();
        });
    </script>
@endif
