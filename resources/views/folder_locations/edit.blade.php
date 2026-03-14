{{-- Edit Folder Location Modal --}}
<div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="editLocationModalLabel" style="font-size: 1.25rem;">Edit Folder Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form :action="editUrl" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" :value="editData.id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_row_name" class="form-label text-muted fw-semibold small">Row Name</label>
                            <input type="text" name="row_name" id="edit_row_name" class="form-control @error('row_name') is-invalid @enderror" 
                                x-model="editData.row_name" required>
                            @error('row_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="edit_column_code" class="form-label text-muted fw-semibold small">Column Code</label>
                            <input type="text" name="column_code" id="edit_column_code" class="form-control @error('column_code') is-invalid @enderror" 
                                x-model="editData.column_code" required>
                            @error('column_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="edit_folder_code" class="form-label text-muted fw-semibold small">Folder Code</label>
                            <input type="text" name="folder_code" id="edit_folder_code" class="form-control @error('folder_code') is-invalid @enderror" 
                                x-model="editData.folder_code" required>
                            @error('folder_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold small d-block">Availability</label>
                            <div class="form-check form-switch p-0 ms-4">
                                <input type="hidden" name="is_available" value="0">
                                <input class="form-check-input" type="checkbox" name="is_available" id="is_available" 
                                    value="1" x-model="editData.is_available" style="cursor: pointer; width: 3em; height: 1.5em;">
                                <label class="form-check-label ms-2" for="is_available">
                                    <span x-text="editData.is_available ? 'Available' : 'Occupied'"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-brand px-4" style="border-radius: 8px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
