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

    function performSearch(query, company, autoSelectFirst = false) {
        if (query.length < 1) {
            $('#milliResults').hide().empty();
            return;
        }

        const milliSearchUrl = document.querySelector('meta[name="milli-search-url"]')?.getAttribute('content');
        if (!milliSearchUrl) return;

        $.ajax({
            url: milliSearchUrl,
            type: 'GET',
            data: { query: query, company: company },
            success: function (data) {
                const container = $('#milliResults');
                container.empty().show();

                if (data.length > 0) {
                    if (autoSelectFirst) {
                        navigateToEmployee(data[0]);
                        return;
                    }

                    data.forEach(emp => {
                        const item = $(`
                            <div class="milli-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><strong>${emp.last_name}</strong>, ${emp.first_name}</span>
                                    <span class="badge bg-secondary" style="font-size:0.7rem;">${emp.barcode_id || ''}</span>
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
        const company = $('#companySelect').val();

        if (query.length < 1) {
            $('#milliResults').hide().empty();
            return;
        }

        milliTimer = setTimeout(function () {
            performSearch(query, company, false);
        }, 150);
    });

    // Enter key → auto-select first result
    $('#employeeSearch').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(milliTimer);
            performSearch($(this).val(), $('#companySelect').val(), true);
        }
    });

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
