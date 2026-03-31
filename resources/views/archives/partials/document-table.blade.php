<div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
    @if($documents->count() > 0)
        <div class="p-0 table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size: 0.9rem;">
                <thead style="background-color: #f9fafb;">
                    <tr>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Resource</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Department & Type</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Original Context</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Physical Location</th>
                        <th class="border-0 text-uppercase text-muted" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px;">Archived On</th>
                        <th class="border-0 text-uppercase text-muted text-center" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; padding: 12px 24px; width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                        @php
                            $ext = strtolower(pathinfo($doc->system_filename, PATHINFO_EXTENSION));
                            $iconClass = 'file-icon--generic';
                            if ($ext === 'pdf') {
                                $iconClass = 'file-icon--pdf';
                            } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                $iconClass = 'file-icon--img';
                            } elseif ($ext === 'docx') {
                                $iconClass = 'file-icon--doc';
                            } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                $iconClass = 'file-icon--xls';
                            } elseif ($ext === 'csv') {
                                $iconClass = 'file-icon--csv';
                            }
                        @endphp
                        <tr>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                <div class="d-flex align-items-center">
                                    <div class="file-icon-wrapper {{ $iconClass }} me-3">
                                        <i class="fas fa-{{ $ext === 'pdf' ? 'file-pdf' : (in_array($ext, ['jpg', 'jpeg', 'png']) ? 'file-image' : 'file-lines') }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $doc->original_filename }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">
                                            {{ strtoupper($ext) }} &bull; {{ number_format(($doc->file_size_bytes ?? 0) / 1024, 2) }} KB
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="border-bottom-0 text-uppercase" style="padding: 16px 24px; font-size: 0.85rem;">
                                <div class="fw-bold" style="color: #475569;">{{ $doc->department->name }}</div>
                                <div class="text-muted">{{ $doc->documentType->name }}</div>
                            </td>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                <div class="fw-semibold font-monospace" style="color: #dd270d;">
                                    {{ $doc->documentFolder?->folder_code ?? 'ROOT' }}
                                </div>
                                <div class="text-muted text-truncate" style="max-width: 200px; font-size: 0.85rem;" title="{{ $doc->documentFolder?->name ?? 'Root Department Level' }}">
                                    {{ $doc->documentFolder?->name ?? 'Root Department Level' }}
                                </div>
                            </td>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                <div class="text-muted font-monospace" style="font-size: 0.85rem;">
                                    {{ $doc->folderLocation->full_location }}
                                </div>
                            </td>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                <div class="fw-medium text-dark">{{ $doc->deleted_at?->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">{{ $doc->deleted_at?->format('h:i A') }}</div>
                            </td>
                            <td class="border-bottom-0 text-center" style="padding: 16px 24px;">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Preview Button --}}
                                    <button type="button" class="btn btn-sm"
                                            title="Preview Document"
                                            style="border-radius: 6px; padding: 6px 12px; background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; font-weight: 500; transition: all 0.2s; border: none;"
                                            onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.2)'"
                                            onmouseout="this.style.backgroundColor='rgba(59, 130, 246, 0.1)'"
                                            onclick="window.open('{{ route('department-documents.preview', $doc->id) }}', '_blank')">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Restore Button --}}
                                    @can('restore', $doc)
                                        <form action="{{ route('department-documents.restore', $doc->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm"
                                                    title="Restore Document"
                                                    style="border-radius: 6px; padding: 6px 12px; background-color: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 500; transition: all 0.2s; border: none;"
                                                    onmouseover="this.style.backgroundColor='rgba(16, 185, 129, 0.2)'"
                                                    onmouseout="this.style.backgroundColor='rgba(16, 185, 129, 0.1)'"
                                                    onclick="return confirm('Restore this document back to its active folder?')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @endcan

                                    {{-- Permanently Delete Button (Admin only) --}}
                                    @if(Auth::user()->isAdmin())
                                        <form action="{{ route('department-documents.forceDelete', $doc->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm"
                                                    title="Permanently Delete"
                                                    style="border-radius: 6px; padding: 6px 12px; background-color: #fef2f2; color: #ef4444; font-weight: 500; transition: all 0.2s; border: none;"
                                                    onmouseover="this.style.backgroundColor='#fee2e2'"
                                                    onmouseout="this.style.backgroundColor='#fef2f2'"
                                                    onclick="return confirm('Permanently delete this document? This cannot be undone.')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if ($documents->hasPages())
            <div class="card-footer bg-light border-top p-3 text-center">
                {{ $documents->links('pagination::bootstrap-5') }}
            </div>
        @endif
        
    @else
        <div class="card-body text-center py-5">
            <i class="fas fa-file-excel mb-3" style="font-size: 2.5rem; color: #cbd5e1;"></i>
            <h4 class="h5 fw-bold text-dark mb-1">No Archived Documents</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Archived department documents will appear here.</p>
        </div>
    @endif
</div>
