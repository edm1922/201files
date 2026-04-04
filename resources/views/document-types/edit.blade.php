{{-- ── Edit Document Type Modal ── --}}
<div class="modal fade" id="editDocumentTypeModal" tabindex="-1" aria-labelledby="editDocTypeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" :action="editUrl">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" x-model="editData.id">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold" id="editDocTypeLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Edit Document Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-size: 0.8em; opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">Update the details for this document classification.</p>

                    <div class="row g-4">
                        {{-- Name --}}
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                                Document Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="edit_name"
                                   name="name"
                                   class="form-control field-input"
                                   x-model="editData.name"
                                   required
                                   style="background-color: #f9fafb;">
                            <div class="form-text" style="font-size: 0.78rem;">The full, readable name.</div>
                        </div>

                        {{-- Code --}}
                        <div class="col-md-6">
                            <label for="edit_code" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                                Short Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="edit_code"
                                   name="code"
                                   class="form-control field-input"
                                   x-model="editData.code"
                                   required
                                   style="background-color: #f9fafb; font-family: monospace;">
                            <div class="form-text" style="font-size: 0.78rem;">A unique, URL-safe identifier. No spaces.</div>
                        </div>

                        {{-- Department --}}
                        <div class="col-md-6">
                            <label for="edit_department_id" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                                Department <span class="text-danger">*</span>
                            </label>
                            <select id="edit_department_id"
                                    name="department_id"
                                    class="form-select field-input"
                                    x-model="editData.department_id"
                                    required
                                    style="background-color: #f9fafb;">
                                <option value="" disabled>Select a department...</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text" style="font-size: 0.78rem;">Department that owns this document type.</div>
                        </div>


                        <hr class="mt-4 mb-2">

                        {{-- Has Expiry --}}
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="edit_has_expiry"
                                       name="has_expiry"
                                       value="1"
                                       x-bind:checked="editData.has_expiry">
                                <label class="form-check-label fw-semibold" for="edit_has_expiry" style="font-size: 0.85rem;">
                                    <i class="fas fa-calendar-times me-1 text-warning" style="font-size: 0.8rem;"></i> Has Expiry Date
                                </label>
                            </div>
                            <div class="form-text ms-4" style="font-size: 0.78rem;">Enable if this document type can expire (e.g. NBI Clearance).</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-4" style="background-color: #ffffff;">
                    <button type="button" class="btn btn-light" style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 18px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white d-inline-flex align-items-center gap-2" style="background-color: #dd270d; font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 20px; border: none;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
