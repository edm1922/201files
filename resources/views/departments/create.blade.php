{{-- ── Create Department Modal ── --}}
<div class="modal fade" id="createDepartmentModal" tabindex="-1" aria-labelledby="createDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" action="{{ route('settings.departments.store') }}">
                @csrf
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold" id="createDepartmentModalLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-size: 0.8em; opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pt-2">
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">Register a new department to categorize documents.</p>

                    {{-- Department Code --}}
                    <div class="mb-4">
                        <label for="code" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Department Code <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="code"
                               name="code"
                               class="form-control field-input @error('code') is-invalid @enderror"
                               value="{{ old('code') }}"
                               placeholder="e.g. HR, FIN"
                               required
                               maxlength="10"
                               autofocus
                               style="background-color: #f9fafb; text-transform: uppercase;">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Department Name --}}
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Department Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control field-input @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Finance, Human Resource"
                               required
                               style="background-color: #f9fafb;">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-2">
                        <label for="description" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Description
                        </label>
                        <textarea id="description"
                                  name="description"
                                  class="form-control field-input @error('description') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Brief description of this department's function."
                                  style="background-color: #f9fafb;">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" style="font-size: 0.78rem; color: #6b7280; margin-top: 6px;">Optional. Maximum 1000 characters.</div>
                    </div>


                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2" style="background-color: #ffffff;">
                    <button type="button" class="btn btn-light" style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 18px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" data-bs-dismiss="modal" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='#f3f4f6'">Cancel</button>
                    <button type="submit" class="btn text-white d-inline-flex align-items-center gap-2" style="background-color: {{ config('brand.primary_color') }}; font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 20px; border: none; box-shadow: 0 4px 6px -1px var(--company-primary-light), 0 2px 4px -1px var(--company-primary-light); transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px var(--company-primary-border), 0 4px 6px -1px var(--company-primary-light)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px var(--company-primary-light), 0 2px 4px -1px var(--company-primary-light)'">
                        <i class="fas fa-save"></i> Save Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any() && old('_method') !== 'PUT')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var createModal = new bootstrap.Modal(document.getElementById('createDepartmentModal'));
            createModal.show();
        });
    </script>
@endif
