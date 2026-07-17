{{-- ── Create Company Modal ── --}}

<div class="modal fade" id="createCompanyModal" tabindex="-1" aria-labelledby="createCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" action="{{ route('settings.companies.store') }}">
                @csrf
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold" id="createCompanyModalLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Add Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-size: 0.8em; opacity: 0.5;"></button>
                </div>
                <div class="modal-body px-4 pt-2">
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">Register a new client company into the system.</p>

                    {{-- Company Name --}}
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Company Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control field-input @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. ABC Corporation"
                               required
                               autofocus
                               style="background-color: #f9fafb;">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Company Code --}}
                    <div class="mb-4">
                        <label for="code" class="form-label fw-semibold" style="font-size: 0.85rem; color: #374151;">
                            Company Code <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="code"
                               name="code"
                               class="form-control field-input @error('code') is-invalid @enderror"
                               value="{{ old('code') }}"
                               placeholder="e.g. COMP-ABC"
                               maxlength="20"
                               required
                               style="text-transform: uppercase; background-color: #f9fafb;">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" style="font-size: 0.78rem; color: #6b7280; margin-top: 6px;">A unique short code (max 20 characters). Auto-uppercased.</div>
                    </div>

                    {{-- Status --}}
                    <div class="mb-2 p-3" style="background-color: #f3f4f6; border-radius: 10px; border: 1px solid #e5e7eb;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="fw-semibold mb-0" for="is_active" style="font-size: 0.85rem; color: #1f2937;">Active Status</label>
                                <div class="form-text mt-0" style="font-size: 0.75rem; color: #6b7280;">Inactive companies won't appear in assignments.</div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', '1') ? 'checked' : '' }}
                                       role="switch"
                                       style="cursor: pointer; width: 2.5em; height: 1.25em;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2" style="background-color: #ffffff;">
                    <button type="button" class="btn btn-light" style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 18px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" data-bs-dismiss="modal" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='#f3f4f6'">Cancel</button>
                    <button type="submit" class="btn text-white d-inline-flex align-items-center gap-2" style="background-color: {{ config('brand.primary_color') }}; font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px 20px; border: none; box-shadow: 0 4px 6px -1px var(--company-primary-light), 0 2px 4px -1px var(--company-primary-light); transition: transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px var(--company-primary-border), 0 4px 6px -1px var(--company-primary-light)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px var(--company-primary-light), 0 2px 4px -1px var(--company-primary-light)'">
                        <i class="fas fa-save"></i> Save Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any() && old('_method') !== 'PUT')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var createModal = new bootstrap.Modal(document.getElementById('createCompanyModal'));
            createModal.show();
        });
    </script>
@endif
