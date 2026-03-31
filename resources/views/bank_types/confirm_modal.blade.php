{{-- ── Reusable Confirmation Modal ── --}}

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true" x-cloak>
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <form :method="confirmMethod === 'GET' ? 'GET' : 'POST'" :action="confirmActionUrl">
                @csrf
                <template x-if="confirmMethod !== 'POST' && confirmMethod !== 'GET'">
                    <input type="hidden" name="_method" :value="confirmMethod">
                </template>

                <div class="modal-body p-4 text-center">
                    {{-- Dynamic Icon --}}
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm"
                         :class="{
                             'bg-danger text-white': confirmTheme === 'danger',
                             'bg-success text-white': confirmTheme === 'success',
                             'bg-brand text-white': confirmTheme === 'brand' || !confirmTheme
                         }"
                         style="width: 64px; height: 64px; font-size: 1.5rem; transition: all 0.2s;">
                        <i class="fas" :class="confirmIcon"></i>
                    </div>

                    {{-- Dynamic Title & Message --}}
                    <h5 class="fw-bold mb-2" style="color: #111827; font-size: 1.25rem;" x-text="confirmTitle"></h5>
                    <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;" x-html="confirmMessage"></p>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light w-50" style="font-weight: 600; border-radius: 8px; color: #4b5563; background-color: #f3f4f6; border: none;" data-bs-dismiss="modal">Cancel</button>

                        <button type="submit" class="btn text-white w-50 d-inline-flex align-items-center justify-content-center gap-2"
                                :class="{
                                    'btn-danger': confirmTheme === 'danger',
                                    'btn-success': confirmTheme === 'success',
                                    'btn-brand': confirmTheme === 'brand' || !confirmTheme
                                }"
                                style="font-weight: 600; border-radius: 8px; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <span x-text="confirmButtonText"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
