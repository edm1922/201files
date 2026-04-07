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


// Meili-Search Functionality
$(document).ready(function () {
    let meiliTimer;
    let activeSearchRequest = null;
    let latestQuery = '';
    let selectedIndex = -1; // Track currently highlighted index

    function highlightText(text, query) {
        if (!query) return text;
        const tokens = String(query).trim().split(/[\s,.]+/).filter(t => t.length > 0);
        if (tokens.length === 0) return text;

        let highlighted = text;
        // Sort tokens by length descending to avoid partial replacements of longer matches
        tokens.sort((a, b) => b.length - a.length).forEach(token => {
            const escapedToken = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(^|\\s|\\W)(${escapedToken})`, 'gi');
            // Use placeholders to prevent double-wrapping, and preserve preceding char
            highlighted = highlighted.replace(regex, '$1<<$2>>');
        });

        return highlighted.replace(/<<([^>]+)>>/g, '<b>$1</b>');
    }

    function performSearch(query, autoSelectFirst = false) {
        const normalizedQuery = String(query || '').trim();
        selectedIndex = -1; // reset on new search

        if (normalizedQuery.length < 2) {
            latestQuery = '';
            if (activeSearchRequest) {
                activeSearchRequest.abort();
                activeSearchRequest = null;
            }
            $('#meiliResults').hide().empty();
            return;
        }

        latestQuery = normalizedQuery;
        const meiliSearchUrl = document.querySelector('meta[name="meili-search-url"]')?.getAttribute('content');
        if (!meiliSearchUrl) return;

        if (activeSearchRequest) {
            activeSearchRequest.abort();
            activeSearchRequest = null;
        }

        activeSearchRequest = $.ajax({
            url: meiliSearchUrl,
            type: 'GET',
            data: { query: normalizedQuery },
            success: function (data) {
                if (normalizedQuery !== latestQuery) {
                    return;
                }

                const container = $('#meiliResults');
                container.empty().show();

                if (data.length > 0) {
                    if (autoSelectFirst) {
                        navigateToEmployee(data[0]);
                        return;
                    }

                    data.forEach(emp => {
                        const mid = emp.middle_name && emp.middle_name !== 'NULL' ? emp.middle_name : '';
                        const fullName = `${emp.last_name}, ${emp.first_name} ${mid}`.trim();
                        const folderCode = emp.folder?.folder_code || emp.folder_code || '';
                        const barcode = emp.barcode_id || 'NO-BARCODE';

                        const highlightedName = highlightText(fullName, normalizedQuery);
                        const highlightedFolder = highlightText(folderCode, normalizedQuery);
                        const highlightedBarcode = highlightText(barcode, normalizedQuery);

                        const item = $(`
                            <div class="meili-item">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="text-uppercase fw-medium">${highlightedName}</span>
                                    <span class="text-muted opacity-50 px-1">•</span>
                                    <span class="meili-folder-code">${highlightedFolder}</span>
                                    <span class="text-muted opacity-50 px-1">•</span>
                                    <span class="meili-barcode">${highlightedBarcode}</span>
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
            error: function (xhr, status) {
                if (status === 'abort') {
                    return;
                }

                console.error('Meili-search failed:', xhr.status, xhr.responseText);
            },
            complete: function () {
                activeSearchRequest = null;
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
        clearTimeout(meiliTimer);
        const query = String($(this).val() || '').trim();

        if (query.length < 2) {
            latestQuery = '';
            if (activeSearchRequest) {
                activeSearchRequest.abort();
                activeSearchRequest = null;
            }
            $('#meiliResults').hide().empty();
            return;
        }

        meiliTimer = setTimeout(function () {
            performSearch(query, false);
        }, 100);
    });

    // Keyboard navigation (Up, Down, Enter)
    $('#employeeSearch').on('keydown', function (e) {
        const items = $('.meili-item');

        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(meiliTimer);

            if (selectedIndex >= 0 && selectedIndex < items.length) {
                items.eq(selectedIndex).click();
            } else {
                performSearch($(this).val(), true);
            }
            return;
        }

        if (items.length === 0) return; // ignore if no results open

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            updateHighlight(items);
        }
    });

    function updateHighlight(items) {
        items.removeClass('active');
        if (selectedIndex >= 0) {
            const activeItem = items.eq(selectedIndex);
            activeItem.addClass('active');

            // Auto scroll container if needed
            const container = $('#meiliResults');
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
            $('#meiliResults').hide();
        }
    });

    // Select2 Initialization
    $('.basic-select').select2({
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: 0,
        escapeMarkup: function (markup) { return markup; }
    });

    $('.tagging-select').select2({
        width: '100%',
        tags: true,
        allowClear: true,
        minimumResultsForSearch: 0
    });

    // Flash Messages Handling (Auto-hide and Manual Close)
    $('.alert, .alert-flash').each(function () {
        const $alert = $(this);

        // Ensure the content is wrapped in a span for flex-grow if not already
        if ($alert.find('span').length === 0 && $alert.text().trim() !== '') {
            const text = $alert.html();
            // Wrap text (excluding the icon if any)
            // But for simplicity, let's just make sure the close button is appended
        }

        // Add manual close button if it's not already there
        if ($alert.find('.btn-close-flash').length === 0) {
            const closeBtn = $('<button type="button" class="btn-close-flash" title="Close"><i class="fas fa-times"></i></button>');
            $alert.append(closeBtn);

            // Manual close event
            closeBtn.on('click', function () {
                $alert.fadeOut('fast');
            });
        }
    });

    // Auto-hide after 5 seconds
    setTimeout(function () {
        $('.alert, .alert-flash').fadeOut('slow');
    }, 5000);
});

/**
 * 201 Files View - Scripts
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('statusManager', (initialStatus = '', initialTab = 'employee') => ({
        currentStatus: initialStatus,
        previousStatus: initialStatus,
        activeTab: initialTab,
        modal: null,

        init() {
            // Universal uppercase for inputs with .text-uppercase
            document.querySelectorAll('.text-uppercase').forEach(input => {
                input.addEventListener('input', function () {
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

document.addEventListener('DOMContentLoaded', function () {
    // Sync toolbar company dropdown → hidden form field
    const companySelect = document.getElementById('companySelect');
    if (companySelect) {
        companySelect.addEventListener('change', function () {
            const hiddenField = document.getElementById('companyIdHidden');
            if (hiddenField) hiddenField.value = this.value;
        });
    }

    // Folder Code: Handle selection and clear
    const folderCodeInput = document.getElementById('folderCodeInput');
    const availableSelect = document.getElementById('availableCodeSelect');
    const clearButton = document.getElementById('clearFolderCode');

    if (availableSelect && folderCodeInput) {
        availableSelect.addEventListener('change', function () {
            if (this.value) {
                folderCodeInput.value = this.value;
                // Optional: show a small feedback that it was replaced
            }
        });
    }

    if (clearButton && folderCodeInput) {
        clearButton.addEventListener('click', function () {
            folderCodeInput.value = '';
            folderCodeInput.placeholder = 'Auto-generate';
            if (availableSelect) availableSelect.value = '';
        });
    }
});

/**
 * Format audit log changes for the update history modal.
 */
function formatChanges(changes, action = null) {
    if (action === 'created') {
        return '';
    }

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
                        ${formatChanges(log.changes, log.action)}
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
