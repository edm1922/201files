<!-- Update History Modal -->
<div class="modal fade" id="updateHistoryModal" tabindex="-1" aria-labelledby="updateHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="updateHistoryModalLabel">
                    <i class="fas fa-history me-2 text-danger"></i>Update History
                </h5>
               
            </div>
            <div class="modal-body pt-4">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="updateHistoryTable">
                        <thead class="bg-light sticky-top" style="z-index: 1;">
                            <tr>
                                <th class="border-0 text-muted small text-uppercase fw-bold ps-3">User</th>
                                <th class="border-0 text-muted small text-uppercase fw-bold">Description</th>
                                <th class="border-0 text-muted small text-uppercase fw-bold">Changes</th>
                                <th class="border-0 text-muted small text-uppercase fw-bold pe-3">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody id="updateHistoryContent">
                            <!-- Content loaded via AJAX -->
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2 text-danger" role="status"></div>
                                    Loading history...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
<div class="modal-footer border-top-0 pt-0">
    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius:6px; font-weight: 500;">Close</button>
</div>
        </div>
    </div>
</div>

<style>
    #updateHistoryTable thead th {
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        padding-top: 12px;
        padding-bottom: 12px;
        background-color: #f9fafb;
    }
    #updateHistoryTable tbody td {
        padding-top: 14px;
        padding-bottom: 14px;
        font-size: 0.85rem;
    }
    .history-user-avatar {
        width: 32px;
        height: 32px;
        background: #f3f4f6;
        color: #6b7280;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
</style>
