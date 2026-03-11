{{-- ── Edit Company Modal ── --}}
<div class="modal fade" id="editCompanyModal" tabindex="-1" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" :action="editUrl">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold" id="editCompanyModalLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Edit Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-size: 0.8em; opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pt-2">
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">Update details for <strong x-text="editData.name" style="color: #111827;"></strong>.</p>

                    {{-- Company Name --}}
                    <div class="mb-4">
                        <label for="edit_name" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Company Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="edit_name"
                               name="name"
                               class="form-control field-input @error('name') is-invalid @enderror"
                               x-model="editData.name"
                               placeholder="e.g. ABC Corporation"
                               required
                               style="background-color: #f9fafb;">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Company Code --}}
                    <div class="mb-4">
                        <label for="edit_code" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Company Code <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="edit_code"
                               name="code"
                               class="form-control field-input @error('code') is-invalid @enderror"
                               x-model="editData.code"
                               placeholder="e.g. COMP-ABC"
                               maxlength="20"
                               required
                               style="text-transform: uppercase; background-color: #f9fafb;">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" style="font-size: 0.78rem; color: #6b7280; margin-top: 6px;">A unique short code. Auto-uppercased.</div>
                    </div>

                    {{-- Status --}}
                    <div class="mb-2 p-3" style="background-color: #f3f4f6; border-radius: 10px; border: 1px solid #e5e7eb;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="fw-semibold mb-0" for="edit_is_active" style="font-size: 0.85rem; color: #1f2937;">Active Status</label>
                                <div class="form-text mt-0" style="font-size: 0.75rem; color: #6b7280;">Inactive companies won't appear in assignments.</div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="edit_is_active"
                                       name="is_active"
                                       value="1"
                                       x-model="editData.is_active"
                                       role="switch"
                                       style="cursor: pointer; width: 2.5em; height: 1.25em;">
                            </div>
                        </div>
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
            var editModal = new bootstrap.Modal(document.getElementById('editCompanyModal'));
            editModal.show();
        });
    </script>
@endif
