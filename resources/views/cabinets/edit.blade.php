{{-- ── Edit Rack Modal ── --}}
<div class="modal fade" id="editRackModal" tabindex="-1" aria-labelledby="editRackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" :action="editUrl">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold" id="editRackModalLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Edit Rack</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-size: 0.8em; opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pt-2">
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">Update details for <strong x-text="editData.cabinet_name + ' › ' + editData.rack_code" style="color: #111827;"></strong>.</p>

                    <div class="row g-3 mb-3">
                        {{-- Cabinet Name --}}
                        <div class="col-md-6">
                            <label for="edit_cabinet_name" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                                Cabinet Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="edit_cabinet_name"
                                   name="cabinet_name"
                                   class="form-control field-input @error('cabinet_name') is-invalid @enderror"
                                   x-model="editData.cabinet_name"
                                   placeholder="e.g. Cabinet 1"
                                   maxlength="100"
                                   required
                                   style="background-color: #f9fafb;">
                            @error('cabinet_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Rack Code --}}
                        <div class="col-md-6">
                            <label for="edit_rack_code" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                                Rack Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="edit_rack_code"
                                   name="rack_code"
                                   class="form-control field-input @error('rack_code') is-invalid @enderror"
                                   x-model="editData.rack_code"
                                   placeholder="e.g. A1"
                                   maxlength="50"
                                   required
                                   style="background-color: #f9fafb;">
                            @error('rack_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-text mb-2" style="font-size: 0.78rem; color: #6b7280;">
                        <i class="fas fa-info-circle text-primary me-1"></i> The combination of Cabinet Name and Rack Code must be <strong>unique</strong>.
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2 d-flex justify-content-between align-items-center" style="background-color: #ffffff;">
                    <div class="text-muted" style="font-size: 0.75rem; display: flex; align-items: center; gap: 4px;" x-show="editData.updated_at">
                        <i class="far fa-clock"></i> <span x-text="'Updated ' + editData.updated_at"></span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 18px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" data-bs-dismiss="modal" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='#f3f4f6'">Cancel</button>
                        <button type="submit" class="btn text-white d-inline-flex align-items-center gap-2" style="background-color: #dd270d; font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 20px; border: none; box-shadow: 0 4px 6px -1px rgba(221, 39, 13, 0.2), 0 2px 4px -1px rgba(221, 39, 13, 0.1); transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px rgba(221, 39, 13, 0.3), 0 4px 6px -1px rgba(221, 39, 13, 0.15)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px rgba(221, 39, 13, 0.2), 0 2px 4px -1px rgba(221, 39, 13, 0.1)'">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any() && old('_method') === 'PUT')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var editModal = new bootstrap.Modal(document.getElementById('editRackModal'));
            editModal.show();
        });
    </script>
@endif
