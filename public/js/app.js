// Sidebar Drawer — plain JS, no bundler needed
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('sidebar-toggle');
    var drawer = document.getElementById('sidebar-drawer');
    var backdrop = document.getElementById('sidebar-backdrop');

    if (toggle && drawer && backdrop) {
        function openDrawer() {
            drawer.classList.add('open');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            drawer.classList.remove('open');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', function () {
            drawer.classList.contains('open') ? closeDrawer() : openDrawer();
        });

        backdrop.addEventListener('click', closeDrawer);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDrawer();
        });
    }
});


// Milli-Search Functionality
$(document).ready(function () {
    let milliTimer;
    let selectedIndex = -1; // Track currently highlighted index

    function performSearch(query, autoSelectFirst = false) {
        selectedIndex = -1; // reset on new search
        if (query.length < 1) {
            $('#milliResults').hide().empty();
            return;
        }

        const milliSearchUrl = document.querySelector('meta[name="milli-search-url"]')?.getAttribute('content');
        if (!milliSearchUrl) return;

        $.ajax({
            url: milliSearchUrl,
            type: 'GET',
            data: { query: query },
            success: function (data) {
                const container = $('#milliResults');
                container.empty().show();

                if (data.length > 0) {
                    if (autoSelectFirst) {
                        navigateToEmployee(data[0]);
                        return;
                    }

                    // --- milliSearchUrl result ---
                    // data.forEach(emp => {
                    //     const item = $(`

                    data.forEach(emp => {
                        // Check if middle_name exists; if not, use an empty string
                        const middleName = emp.middle_name && emp.middle_name !== 'NULL' ? emp.middle_name : '';
                        const item = $(`
        
                            <div class="milli-item">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-uppercase">${emp.last_name}, ${emp.first_name} ${middleName}</span>
                                    <span class="milli-folder-code"> ${emp.folder?.folder_code || emp.folder_code || ''}</span>
                                </div>
                            </div>
                        `);

                        item.on('click', function () {
                            navigateToEmployee(emp);
                        });
                        container.append(item);
                    });

                } else {
                    container.append('<div class="p-2 text-muted">No results found.</div>');
                }
            },
            error: function (xhr) {
                console.error('Milli-search failed:', xhr.status, xhr.responseText);
            }
        });
    }

    // Navigate to the employee's profile page
    function navigateToEmployee(emp) {
        if (emp.id) {
            window.location.href = '/employees/' + emp.id;
        }
    }

    // Typing search (debounced)
    $('#employeeSearch').on('input', function () {
        clearTimeout(milliTimer);
        const query = $(this).val();

        if (query.length < 1) {
            $('#milliResults').hide().empty();
            return;
        }

        milliTimer = setTimeout(function () {
            performSearch(query, false);
        }, 150);
    });

    // Keyboard navigation (Up, Down, Enter)
    $('#employeeSearch').on('keydown', function (e) {
        const items = $('.milli-item');

        if (items.length === 0) return; // ignore if no results open

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            updateHighlight(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(milliTimer);

            if (selectedIndex >= 0 && selectedIndex < items.length) {
                // An item is highlighted, click it
                items.eq(selectedIndex).click();
            } else {
                // No item highlighted, just auto-select the first result
                performSearch($(this).val(), true);
            }
        }
    });

    function updateHighlight(items) {
        items.removeClass('active');
        if (selectedIndex >= 0) {
            const activeItem = items.eq(selectedIndex);
            activeItem.addClass('active');

            // Auto scroll container if needed
            const container = $('#milliResults');
            const itemTop = activeItem.position().top;
            const itemBottom = itemTop + activeItem.outerHeight();
            const containerScroll = container.scrollTop();
            const containerHeight = container.height();

            if (itemTop < 0) {
                container.scrollTop(containerScroll + itemTop);
            } else if (itemBottom > containerHeight) {
                container.scrollTop(containerScroll + itemBottom - containerHeight);
            }
        }
    }

    // Hide dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            $('#milliResults').hide();
        }
    });

    // Select2 Initialization
    $('.basic-select').select2({
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 0
    });

    $('.tagging-select').select2({
        width: '100%',
        tags: true,
        allowClear: true,
        minimumResultsForSearch: 0
    });

    // Auto-hide success and error messages after 5 seconds
    setTimeout(function () {
        $('.alert, .alert-flash').fadeOut('slow');
    }, 3000);
});

/**
 * 201 Files View - Scripts
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('statusManager', (initialStatus = '') => ({
        currentStatus: initialStatus,
        previousStatus: initialStatus,
        modal: null,

        init() {
            // Universal uppercase for inputs with .text-uppercase
            document.querySelectorAll('.text-uppercase').forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });

            const modalEl = document.getElementById('resignedWarningModal');
            if (modalEl) {
                this.modal = new bootstrap.Modal(modalEl);
            }
            
            // Listen for Select2 change
            $('#statusSelect').on('change', (e) => {
                this.handleStatusChange(e.target.value);
            });
        },

        handleStatusChange(newStatus) {
            if (newStatus === 'resigned') {
                this.modal.show();
            } else {
                this.previousStatus = newStatus;
                this.currentStatus = newStatus;
            }
        },

        proceedWithResignation() {
            // Just submit the form - the server handles the archive logic
            const form = document.getElementById('employeeForm');
            if (form) form.submit();
        },

        cancelResignation() {
            // Revert the Select2 value
            this.currentStatus = this.previousStatus;
            $('#statusSelect').val(this.previousStatus).trigger('change.select2');
            this.modal.hide();
        }
    }));
});

document.addEventListener('DOMContentLoaded', function() {
    // Sync toolbar company dropdown → hidden form field
    const companySelect = document.getElementById('companySelect');
    if (companySelect) {
        companySelect.addEventListener('change', function () {
            const hiddenField = document.getElementById('companyIdHidden');
            if (hiddenField) hiddenField.value = this.value;
        });
    }

    // Folder Code: Restrict to digits only
    const folderCodeInput = document.getElementById('folderCodeInput');
    if (folderCodeInput) {
        folderCodeInput.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    // Update Folder Code input when Available Code Select changes
    const availableSelect = document.getElementById('availableCodeSelect');
    if (availableSelect && folderCodeInput) {
        availableSelect.addEventListener('change', function () {
            if (this.value) {
                folderCodeInput.value = this.value;
            }
        });
    }
});

/**
 * Format audit log changes for the update history modal.
 */
function formatChanges(changes) {
    if (!changes || typeof changes !== 'object' || !changes.before || !changes.after) {
        return '<div class="text-muted small" style="font-style: italic;">No specific field changes recorded.</div>';
    }
    
    const relevantKeys = Object.keys(changes.before);
    let html = '<div class="mt-2" style="font-size: 0.75rem; border-left: 2px solid #fecaca; padding-left: 12px; margin-left: 4px;">';
    let changedCount = 0;
    
    relevantKeys.forEach(key => {
        const beforeVal = changes.before[key];
        const afterVal = changes.after[key];
        
        const bStr = (beforeVal === null || beforeVal === '') ? 'NONE' : String(beforeVal);
        const aStr = (afterVal === null || afterVal === '') ? 'NONE' : String(afterVal);
        
        if (bStr !== aStr) {
            const label = key.replace(/_/g, ' ').toUpperCase();
            html += `
                <div class="mb-1">
                    <span class="fw-semibold text-secondary" style="font-size: 0.65rem;">${label}:</span> 
                    <span class="text-decoration-line-through text-muted small">${bStr}</span> 
                    <i class="fas fa-arrow-right mx-1 text-danger opacity-50" style="font-size: 0.6rem;"></i> 
                    <span class="text-danger fw-semibold">${aStr}</span>
                </div>`;
            changedCount++;
        }
    });
    
    html += '</div>';
    return changedCount > 0 ? html : '';
}

/**
 * Fetch and show update history for an employee in a modal.
 */
function showUpdateHistory(employeeId) {
    const modalElement = document.getElementById('updateHistoryModal');
    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);
    const content = document.getElementById('updateHistoryContent');
    
    content.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2 text-danger" role="status"></div>
                Loading history...
            </td>
        </tr>
    `;
    
    modal.show();
    
    fetch(`/employees/${employeeId}/update-history`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                content.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No update history found.</td></tr>';
                return;
            }
            
            content.innerHTML = data.map(log => `
                <tr>
                    <td class="ps-3" style="width: 15%; vertical-align: top;">
                        <div class="d-flex align-items-center">
                            <div class="history-user-avatar me-2" style="width: 28px; height: 28px; flex-shrink: 0;">
                                <i class="fas fa-user" style="font-size: 0.7rem;"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div class="fw-semibold text-dark text-truncate" style="font-size: 0.8rem;" title="${log.user_name}">${log.user_name}</div>
                                <div class="text-muted text-uppercase" style="font-size: 0.55rem; font-weight: 700; letter-spacing: 0.5px;">${log.user_role}</div>
                            </div>
                        </div>
                    </td>
                    <td style="width: 30%; vertical-align: top;">
                        <div class="text-dark fw-semibold" style="font-size: 0.8rem;">${log.description}</div>
                    </td>
                    <td style="width: 35%; vertical-align: top;">
                        ${formatChanges(log.changes)}
                    </td>
                    <td class="pe-3 text-end" style="width: 20%; vertical-align: top;">
                        <div class="text-dark fw-semibold" style="font-size: 0.8rem;">${log.date}</div>
                        <div class="small text-muted" style="font-size: 0.7rem;">${log.time}</div>
                    </td>
                </tr>
            `).join('');
        })
        .catch(err => {
            content.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error loading history.</td></tr>';
            console.error(err);
        });
}
