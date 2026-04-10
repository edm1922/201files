<div x-data="documentArchiveManager()">
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

                            $mime = strtolower((string) ($doc->mime_type ?? ''));
                            $previewKind = null;

                            if (str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                                $previewKind = 'image';
                            } elseif ($mime === 'application/pdf' || $ext === 'pdf') {
                                $previewKind = 'pdf';
                            } elseif ($ext === 'docx' || $mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                                $previewKind = 'docx';
                            } elseif (in_array($ext, ['xls', 'xlsx', 'csv']) || in_array($mime, [
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                                'application/csv',
                            ])) {
                                $previewKind = 'sheet';
                            }

                            $previewUrl = null;
                            if (in_array($previewKind, ['image', 'pdf'], true)) {
                                $previewUrl = route('department-documents.preview', $doc);
                            } elseif (in_array($previewKind, ['docx', 'sheet'], true)) {
                                $previewUrl = route('department-documents.download', $doc);
                            }
                            $previewable = $previewUrl !== null;
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
                                    {{ $doc->documentLocation?->name ?? '—' }}
                                </div>
                            </td>
                            <td class="border-bottom-0" style="padding: 16px 24px;">
                                <div class="fw-medium text-dark">{{ $doc->deleted_at?->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">{{ $doc->deleted_at?->format('h:i A') }}</div>
                            </td>
                            <td class="border-bottom-0 text-center" style="padding: 16px 24px;">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Preview Button --}}
                                    @if ($previewable)
                                        <button type="button" class="btn btn-sm"
                                                title="Preview Document"
                                                style="border-radius: 6px; padding: 6px 12px; background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; font-weight: 500; transition: all 0.2s; border: none;"
                                                onmouseover="this.style.backgroundColor='rgba(59, 130, 246, 0.2)'"
                                                onmouseout="this.style.backgroundColor='rgba(59, 130, 246, 0.1)'"
                                                data-preview-trigger
                                                data-preview-url="{{ $previewUrl }}"
                                                data-preview-kind="{{ $previewKind }}"
                                                data-preview-mime="{{ $doc->mime_type }}"
                                                data-preview-name="{{ $doc->original_filename }}"
                                                data-preview-ext="{{ $ext }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif

                                    {{-- Restore Button --}}
                                    @can('restore', $doc)
                                        <button type="button" class="btn btn-sm"
                                                title="Restore Document"
                                                style="border-radius: 6px; padding: 6px 12px; background-color: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 500; transition: all 0.2s; border: none;"
                                                onmouseover="this.style.backgroundColor='rgba(16, 185, 129, 0.2)'"
                                                onmouseout="this.style.backgroundColor='rgba(16, 185, 129, 0.1)'"
                                                @click="openRestoreModal('{{ route('department-documents.restore', $doc->id) }}', '{{ addslashes($doc->original_filename) }}', '{{ $doc->documentFolder?->folder_code ?? 'ROOT' }}')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endcan

                                    {{-- Permanently Delete Button (Admin only) --}}
                                    @if(Auth::user()->isAdmin())
                                        <button type="button" class="btn btn-sm"
                                                title="Permanently Delete"
                                                style="border-radius: 6px; padding: 6px 12px; background-color: #fef2f2; color: #ef4444; font-weight: 500; transition: all 0.2s; border: none;"
                                                onmouseover="this.style.backgroundColor='#fee2e2'"
                                                onmouseout="this.style.backgroundColor='#fef2f2'"
                                                @click="openConfirmModal('{{ route('department-documents.forceDelete', $doc->id) }}', '{{ addslashes($doc->original_filename) }}', '{{ $doc->documentFolder?->folder_code ?? 'ROOT' }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
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
        </div>
    @endif
    </div>

    {{-- Confirm Restore Modal --}}
    <template x-teleport="body">
    <div class="modal fade" id="confirmDocRestoreModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: #0f172a;">
                        <i class="fas fa-undo text-success me-2"></i>Restore Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="color: #475569;">
                    <p class="mb-2">Are you sure you want to <strong class="text-success">restore</strong> this document?</p>
                    <div class="p-3 rounded-3" style="background-color: #f0fdf4; border: 1px solid #dcfce7;">
                        <div class="fw-semibold text-break" style="color: #166534;" x-text="restoreName"></div>
                        <div class="text-muted mt-1" style="font-size: 0.85rem;">Original Context: <span x-text="restoreContext" class="font-monospace fw-bold" style="color: #15803d;"></span></div>
                    </div>
                    <p class="mt-3 mb-0 text-muted" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        The document will be restored back to its active folder.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light fw-medium hover-bg-light" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <form :action="restoreActionUrl" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success fw-medium shadow-sm transition-colors duration-200" style="border-radius: 8px;">
                            <i class="fas fa-undo me-1"></i> Yes, Restore
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Confirm Delete Modal --}}
    <template x-teleport="body">
    <div class="modal fade" id="confirmDocDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: #0f172a;">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Permanently Delete Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="color: #475569;">
                    <p class="mb-2">Are you sure you want to <strong class="text-danger">permanently delete</strong> this document?</p>
                    <div class="p-3 rounded-3" style="background-color: #fef2f2; border: 1px solid #fee2e2;">
                        <div class="fw-semibold text-break" style="color: #991b1b;" x-text="confirmName"></div>
                        <div class="text-muted mt-1" style="font-size: 0.85rem;">Original Context: <span x-text="confirmContext" class="font-monospace fw-bold" style="color: #dd270d;"></span></div>
                    </div>
                    <p class="mt-3 mb-0 text-muted" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        This action <strong>cannot be undone</strong> and the file will be permanently removed from storage.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light fw-medium hover-bg-light" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <form :action="confirmActionUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger fw-medium shadow-sm transition-colors duration-200" style="border-radius: 8px;">
                            <i class="fas fa-trash-alt me-1"></i> Yes, Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        if (!Alpine.data('documentArchiveManager')) {
            Alpine.data('documentArchiveManager', () => ({
                restoreActionUrl: '',
                restoreName: '',
                restoreContext: '',
                confirmActionUrl: '',
                confirmName: '',
                confirmContext: '',

                openRestoreModal(url, name, context) {
                    this.restoreActionUrl = url;
                    this.restoreName = name;
                    this.restoreContext = context || '—';
                    var modal = new bootstrap.Modal(document.getElementById('confirmDocRestoreModal'));
                    modal.show();
                },

                openConfirmModal(url, name, context) {
                    this.confirmActionUrl = url;
                    this.confirmName = name;
                    this.confirmContext = context || '—';
                    var modal = new bootstrap.Modal(document.getElementById('confirmDocDeleteModal'));
                    modal.show();
                }
            }));
        }
    });
</script>
