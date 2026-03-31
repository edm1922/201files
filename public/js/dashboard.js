function updateYear(year) {
    if (!year) return;
    const form = document.getElementById('yearFilterForm');
    // Ensure there's a hidden year input if we're calling this from somewhere else
    let yearInput = form.querySelector('input[name="year"]');
    if (!yearInput) {
        yearInput = document.createElement('input');
        yearInput.type = 'hidden';
        yearInput.name = 'year';
        form.appendChild(yearInput);
    }
    yearInput.value = year;
    form.submit();
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.dashboardData === 'undefined') return;

    const data = window.dashboardData;

    // --- Hiring Trends Line Chart ---
    const hiringCtx = document.getElementById('hiringChart');
    if (hiringCtx) {
        const ctx = hiringCtx.getContext('2d');
        const rawData = data.hiringTrends;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        let labels = [];
        let counts = [];

        if (data.month) {
            const daysInMonth = data.daysInMonth;
            labels = Array.from({ length: daysInMonth }, (_, i) => (i + 1).toString());
            counts = new Array(daysInMonth).fill(0);
            rawData.forEach(item => {
                if (item.day) {
                    counts[item.day - 1] = item.count;
                }
            });
        } else {
            labels = months;
            counts = new Array(12).fill(0);
            rawData.forEach(item => {
                counts[item.month - 1] = item.count;
            });
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'New Hires',
                    data: counts,
                    borderColor: '#dd270d',
                    backgroundColor: 'rgba(221, 39, 13, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#dd270d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#9ca3af', font: { size: 11 } },
                        grid: { borderDash: [5, 5], color: '#e5e7eb' }
                    },
                    x: {
                        ticks: { color: '#9ca3af', font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // --- Company Distribution Pie Chart ---
    const companyCtxElem = document.getElementById('companyChart');
    if (companyCtxElem) {
        const companyCtx = companyCtxElem.getContext('2d');
        const companyData = data.companyDistribution;

        new Chart(companyCtx, {
            type: 'doughnut',
            data: {
                labels: companyData.map(item => item.name),
                datasets: [{
                    data: companyData.map(item => item.count),
                    backgroundColor: [
                        '#dd270d', '#2563eb', '#10b981', '#f59e0b', '#8b5cf6',
                        '#ec4899', '#06b6d4', '#4b5563', '#10b981', '#f97316'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        cornerRadius: 8
                    }
                }
            }
        });
    }

    // --- Bank Distribution Column Chart ---
    const bankCtxElem = document.getElementById('bankChart');
    if (bankCtxElem) {
        const bankCtx = bankCtxElem.getContext('2d');
        const bankData = data.bankDistribution;

        new Chart(bankCtx, {
            type: 'bar',
            data: {
                labels: bankData.map(item => item.name || 'Unknown'),
                datasets: [{
                    label: 'Employees per Bank',
                    data: bankData.map(item => item.count),
                    backgroundColor: '#dd270d',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#9ca3af', font: { size: 11 } },
                        grid: { borderDash: [5, 5], color: '#e5e7eb' }
                    },
                    x: {
                        ticks: { color: '#9ca3af', font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
