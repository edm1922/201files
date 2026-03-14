{{-- Create Folder Location Modal --}}
<div class="modal fade" id="createLocationModal" tabindex="-1" aria-labelledby="createLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="createLocationModalLabel" style="font-size: 1.25rem;">Add Folder Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('settings.folder-locations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="row_name" class="form-label text-muted fw-semibold small">Row Name (e.g. A)</label>
                            <input type="text" name="row_name" id="row_name" class="form-control @error('row_name') is-invalid @enderror" 
                                value="{{ old('row_name') }}" placeholder="A" required>
                            @error('row_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="column_code" class="form-label text-muted fw-semibold small">Column Code (e.g. 1)</label>
                            <input type="text" name="column_code" id="column_code" class="form-control @error('column_code') is-invalid @enderror" 
                                value="{{ old('column_code') }}" placeholder="1" required>
                            @error('column_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="folder_code" class="form-label text-muted fw-semibold small">Folder Code (e.g. CSC-HR-0001)</label>
                            <input type="text" name="folder_code" id="folder_code" class="form-control @error('folder_code') is-invalid @enderror" 
                                value="{{ old('folder_code') }}" placeholder="Unique code" required>
                            @error('folder_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-brand px-4" style="border-radius: 8px;">Create Location</button>
                </div>
            </form>
        </div>
    </div>
</div>
