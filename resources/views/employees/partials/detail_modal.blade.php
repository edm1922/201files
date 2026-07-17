<!-- Employee Details Modal -->
<div class="modal fade" id="employeeDetailModal" tabindex="-1" aria-labelledby="employeeDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);">
            <div class="modal-header text-white px-4 py-3" style="background-color: #b91c1c; border-bottom: none;">
                <h5 class="modal-title fw-bold" id="employeeDetailModalLabel">
                    <i class="fas fa-user-circle me-2"></i>Employee Profile Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8fafc;">
                <!-- Profile Header -->
                <div class="profile-header mb-4" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; align-items: center; gap: 20px;">
                    <div class="profile-avatar" id="modal-avatar" style="width: 80px; height: 80px; font-size: 2rem; background-color: #f0d6d6; color: #b91c1c; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); flex-shrink: 0;"></div>
                    <div class="profile-info">
                        <h2 class="text-uppercase mb-1 fw-extrabold text-dark" id="modal-name" style="font-size: 1.5rem; letter-spacing: -0.02em; margin: 0; font-weight: 800;"></h2>
                        <div class="profile-meta d-flex align-items-center gap-2 flex-wrap" style="color: #6b7280; font-size: 0.9rem; font-weight: 600;">
                            <span>Folder code:</span>
                            <span class="profile-field__value profile-field__value--red profile-field__value--mono fw-bold" id="modal-folder-code" style="color: {{ config('brand.primary_color') }}; font-family: monospace;"></span>
                            <span class="dot" style="width: 4px; height: 4px; background: #d1d5db; border-radius: 50%;"></span>
                            <span class="emp-status-badge" id="modal-status-badge" style="font-size: 0.72rem; padding: 3px 12px; border-radius: 20px; font-weight: 700; text-transform: uppercase;"></span>
                        </div>
                    </div>
                </div>

                <div class="profile-grid d-flex flex-wrap gap-3">
                    <!-- IDENTIFICATION CARD -->
                    <div class="profile-card flex-fill" style="min-width: 280px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                        <div class="profile-card__header" style="background: #f9fafb; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #374151; letter-spacing: 0.05em;">Identification</div>
                        <div class="profile-card__body p-3">
                            <div class="profile-field mb-2 d-flex justify-content-between">
                                <span class="profile-field__label text-dark" style="font-size: 0.8rem;">System No.</span>
                                <span class="profile-field__value profile-field__value--mono fw-semibold" id="modal-system-id" style="font-family: monospace; font-size: 0.85rem;"></span>
                            </div>
                            <div class="profile-field mb-0 d-flex justify-content-between">
                                <span class="profile-field__label text-dark" style="font-size: 0.8rem;">Barcode ID</span>
                                <span class="profile-field__value profile-field__value--red fw-semibold" id="modal-barcode-id" style="font-family: monospace; font-size: 0.85rem;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- ASSIGNMENT CARD -->
                    <div class="profile-card flex-fill" style="min-width: 280px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                        <div class="profile-card__header" style="background: #f9fafb; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #374151; letter-spacing: 0.05em;">Assignment</div>
                        <div class="profile-card__body p-3">
                            <div class="profile-field mb-2 d-flex justify-content-between">
                                <span class="profile-field__label text-dark" style="font-size: 0.8rem;">Company</span>
                                <span class="profile-field__value fw-semibold text-end" id="modal-company" style="font-size: 0.85rem;"></span>
                            </div>
                            <div class="profile-field mb-0 d-flex justify-content-between">
                                <span class="profile-field__label text-dark" style="font-size: 0.8rem;">Date Hired</span>
                                <span class="profile-field__value fw-semibold" id="modal-date-hired" style="font-size: 0.85rem;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- BANK INFORMATION CARD -->
                    <div class="profile-card flex-fill" style="min-width: 280px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                        <div class="profile-card__header" style="background: #f9fafb; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #374151; letter-spacing: 0.05em;">Bank Information</div>
                        <div class="profile-card__body p-3">
                            <div class="profile-field mb-2 d-flex justify-content-between">
                                <span class="profile-field__label text-dark" style="font-size: 0.8rem;">ATM Status</span>
                                <span class="profile-field__value fw-semibold" id="modal-atm-status" style="font-size: 0.85rem;"></span>
                            </div>
                            <div class="profile-field mb-0 d-flex justify-content-between">
                                <span class="profile-field__label text-dark" style="font-size: 0.8rem;">Bank Name</span>
                                <span class="profile-field__value fw-semibold" id="modal-bank-name" style="font-size: 0.85rem;"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOLDER LOCATION CARD -->
                <div class="profile-card mt-3" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                    <div class="profile-card__header" style="background: #f9fafb; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #374151; letter-spacing: 0.05em;">Folder Location</div>
                    <div class="profile-card__body p-3">
                        <div class="profile-field d-flex justify-content-between">
                            <span class="profile-field__label text-dark" style="font-size: 0.8rem;">Location</span>
                            <span class="profile-field__value profile-field__value--red profile-field__value--mono fw-bold" id="modal-location" style="color: {{ config('brand.primary_color') }}; font-family: monospace; font-size: 0.85rem;"></span>
                        </div>
                    </div>
                </div>

                <!-- LATEST UPDATED LOG -->
                <div class="mt-3 p-2 rounded-2 shadow-sm" id="modal-update-log-wrapper" style="background-color: #f3f4f6; border-left: 5px solid #dc2626; display: none; align-items: center;">
                    <div class="ps-3 py-1">
                        <span style="font-size: 0.82rem; color: #374151;">
                            <span class="fw-bold text-danger text-uppercase" id="modal-update-emp-name"></span>'s data was last updated by 
                            <span class="fw-bold text-danger text-uppercase" id="modal-update-user"></span> on 
                            <span class="fw-bold text-danger" id="modal-update-date"></span> at 
                            <span class="fw-bold text-danger" id="modal-update-time"></span>.
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light d-flex justify-content-between px-4 py-3">
                <div>
                    <!-- Edit Button for Admins/Encoders -->
                    <a href="#" id="modal-edit-btn" class="btn btn-danger px-4" style="border-radius: 6px; font-size: 0.85rem; font-weight: 600; background-color: {{ config('brand.primary_color') }}; border: none; display: none;">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 6px; font-size: 0.85rem; font-weight: 500;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openEmployeeDetailModal(employeeId) {
        // Fetch employee details via AJAX
        const base = document.querySelector('meta[name="app-base-url"]')?.getAttribute('content') || '';
        fetch(`${base}/employees/${employeeId}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch details');
                }
                return response.json();
            })
            .then(data => {
                // Populate Modal contents
                document.getElementById('modal-avatar').textContent = data.initials;
                document.getElementById('modal-name').textContent = data.name;
                document.getElementById('modal-folder-code').textContent = data.folder_code;
                
                // Status badge styling
                const statusBadge = document.getElementById('modal-status-badge');
                statusBadge.textContent = data.status_label;
                statusBadge.className = 'emp-status-badge emp-status-badge--' + data.status;

                document.getElementById('modal-system-id').textContent = data.system_id;
                document.getElementById('modal-barcode-id').textContent = data.barcode_id;
                document.getElementById('modal-company').textContent = data.company;
                document.getElementById('modal-date-hired').textContent = data.date_hired;

                // ATM Status and Bank Name
                const atmStatusSpan = document.getElementById('modal-atm-status');
                atmStatusSpan.textContent = data.atm_status_label;
                
                // Reset classes and set the correct one
                atmStatusSpan.className = 'fw-semibold';
                if (data.atm_status) {
                    atmStatusSpan.classList.add('atm-status--' + data.atm_status.replace('_', '-'));
                }

                document.getElementById('modal-bank-name').textContent = data.bank_name;
                document.getElementById('modal-location').textContent = data.location;

                // Update Log
                const logWrapper = document.getElementById('modal-update-log-wrapper');
                if (data.latest_update) {
                    document.getElementById('modal-update-emp-name').textContent = data.name;
                    document.getElementById('modal-update-user').textContent = data.latest_update.user_name;
                    document.getElementById('modal-update-date').textContent = data.latest_update.date;
                    document.getElementById('modal-update-time').textContent = data.latest_update.time;
                    logWrapper.style.display = 'flex';
                } else {
                    logWrapper.style.display = 'none';
                }

                // Edit Button link
                const editBtn = document.getElementById('modal-edit-btn');
                if (data.is_encoder_or_admin) {
                    editBtn.href = `/employees/${data.id}?edit=1`;
                    editBtn.style.display = 'inline-block';
                } else {
                    editBtn.style.display = 'none';
                }

                // Show Modal
                const myModal = new bootstrap.Modal(document.getElementById('employeeDetailModal'));
                myModal.show();
            })
            .catch(error => {
                console.error('Error fetching employee details:', error);
                alert('Could not load employee details.');
            });
    }
</script>
