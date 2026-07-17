{{-- ── Edit Bank Type Modal ── --}}

<div class="modal fade" id="editBankTypeModal" tabindex="-1" aria-labelledby="editBankTypeModalLabel" aria-hidden="true" x-cloak>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" :action="editUrl">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold" id="editBankTypeModalLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">
                        Edit Bank Type
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-size: 0.8em; opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pt-2">
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">Update bank type details.</p>

                    <input type="hidden" name="id" x-model="editData.id">

                    {{-- Bank Type Name --}}
                    <div class="mb-4">
                        <label for="edit_name" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Bank Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="edit_name"
                               name="name"
                               class="form-control field-input"
                               x-model="editData.name"
                               required
                               style="background-color: #f9fafb;">
                        @error('name')
                            <div class="text-danger mt-1" style="font-size: 0.875em;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-2 p-3" style="background-color: #f3f4f6; border-radius: 10px; border: 1px solid #e5e7eb;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="fw-semibold mb-0" for="edit_is_active" style="font-size: 0.85rem; color: #1f2937;">Active Status</label>
                                <div class="form-text mt-0" style="font-size: 0.75rem; color: #6b7280;">Inactive bank types won't appear in assignments.</div>
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
                    {{-- Last Updated Meta Info --}}
                    <div class="text-muted" style="font-size: 0.75rem;">
                        <span x-show="editData.updated_at">
                            <i class="fas fa-clock me-1 opacity-50"></i> Last updated: <span x-text="editData.updated_at" class="fw-medium"></span>
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 18px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn text-white d-inline-flex align-items-center gap-2" style="background-color: {{ config('brand.primary_color') }}; font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 20px; border: none;">
                            <i class="fas fa-save"></i> Update Bank Type
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
            var editModal = new bootstrap.Modal(document.getElementById('editBankTypeModal'));
            editModal.show();
        });
    </script>
@endif
