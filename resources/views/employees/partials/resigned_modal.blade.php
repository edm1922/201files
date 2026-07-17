{{-- ── Resigned Warning Modal ── --}}
<div class="modal fade" id="resignedWarningModal" tabindex="-1" aria-labelledby="resignedWarningModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
                <!-- Warning Icon -->
                <div class="mx-auto mb-4 bg-danger bg-opacity-10 text-danger" style="width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem;"></i>
                </div>
                
                <h5 class="fw-bold mb-2" id="resignedWarningModalLabel" style="color: #111827; letter-spacing: -0.025em; font-size: 1.25rem;">Move to Archive?</h5>
                
                <p class="mb-4" style="font-size: 0.95rem; color: #4b5563; line-height: 1.5;">
                    The employee's record is currently being moved to the archive. These files will remain in the archive until an administrator reviews and permanently deletes them. 
                    <br><br>
                    <strong>Are you sure you want to proceed with this action?</strong>
                </p>
            </div>

            <div class="modal-footer border-top-0 px-4 pb-4 pt-0 d-flex gap-2">
                <button type="button" class="btn btn-light w-100 m-0" 
                        style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px; color: #4b5563; background-color: #f3f4f6; border: none; transition: background 0.2s;" 
                        @click="cancelResignation()">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger w-100 m-0 d-inline-flex align-items-center justify-content-center gap-2"
                        style="font-weight: 600; font-size: 0.875rem; border-radius: 8px; padding: 10px; border: none; background-color: {{ config('brand.primary_color') }}; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);"
                        @click="proceedWithResignation()">
                    Proceed
                </button>
            </div>
        </div>
    </div>
</div>
