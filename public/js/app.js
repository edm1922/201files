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
                    data.forEach(emp => {
                        const item = $(`
                            <div class="milli-item">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-uppercase"><strong>${emp.last_name}</strong>, ${emp.first_name}</span>
                                    <span style="font-size:0.75rem; color:#dd270d; font-weight:700;"> ${emp.folder_code || ''}</span>
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
});
