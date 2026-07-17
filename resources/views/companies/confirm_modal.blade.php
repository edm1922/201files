{{-- ── Confirmation Modal (Toggle / Delete) ── --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form method="POST" :action="confirmActionUrl">
                @csrf
                <input type="hidden" name="_method" :value="confirmMethod">
                
                <div class="modal-body p-4 text-center">
                    <!-- Icon container -->
                    <div class="mx-auto mb-4" style="width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"
                         :class="confirmTheme === 'danger' ? 'bg-danger bg-opacity-10 text-danger' : (confirmTheme === 'success' ? 'bg-success bg-opacity-10 text-success' : 'bg-primary bg-opacity-10 text-primary')">
                        <i class="fas" :class="confirmIcon" style="font-size: 1.5rem;"></i>
                    </div>
                    
                    <h5 class="fw-bold mb-2" id="confirmModalLabel" x-text="confirmTitle" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Confirm Action</h5>
                    
                    <p class="mb-4" style="font-size: 0.95rem; color: #4b5563; line-height: 1.5;" x-html="confirmMessage"></p>
                </div>

                <div class="modal-footer border-top-0 px-4 pb-4 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light w-100 m-0" style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" data-bs-dismiss="modal" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='#f3f4f6'">Cancel</button>
                    <button type="submit" class="btn text-white w-100 m-0 d-inline-flex align-items-center justify-content-center gap-2"
                            :class="confirmTheme === 'danger' ? 'btn-danger' : (confirmTheme === 'success' ? 'btn-success' : '')"
                            :style="confirmTheme !== 'danger' && confirmTheme !== 'success' ? 'background-color: {{ config('brand.primary_color') }};' : ''"
                            style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <span x-text="confirmButtonText">Confirm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
