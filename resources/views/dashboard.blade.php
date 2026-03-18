<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 fw-bold">Dashboard</h2>
        <div class="d-flex align-items-center gap-3">
            <div class="date-pill">
                <span>{{ date('M d, Y') }}</span> 
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row justify-content-center mb-3">
        <div class="col-lg-10">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <span class="stat-title d-block mb-1">Total Employees</span>
                                <span class="stat-value-large">{{ number_format($totalEmployees) }}</span>
                            </div>
                            <div class="stat-icon-circle">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <span class="stat-title d-block mb-1">Total Companies</span>
                                <span class="stat-value-large">{{ $totalCompanies }}</span>
                            </div>
                            <div class="stat-icon-circle">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <span class="stat-title d-block mb-1">Total Documents</span>
                                <span class="stat-value-large">{{ number_format($totalDocuments) }}</span>
                            </div>
                            <div class="stat-icon-circle">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div>
                                <span class="stat-title d-block mb-1">Total Users</span>
                                <span class="stat-value-large">{{ number_format($totalUsers) }}</span>
                            </div>
                            <div class="stat-icon-circle">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Hiring Trends Graph -->
        <div class="col-lg-6">
            <div class="graph-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="dashboard-section-title mb-0">
                        <i class="fas fa-chart-line text-primary"></i> Hiring Trends
                    </h3>
                    <form action="{{ route('dashboard') }}" method="GET" class="filter-wrapper mb-0" id="yearFilterForm">
                        <select name="year" 
                                id="yearSelect"
                                class="form-select form-select-sm" 
                                style="width: 140px;" 
                                onchange="handleYearSelectChange(this)">
                            @php
                                $yearsRange = $availableYears ?? [date('Y')];
                                $selYear = (int)$year;
                                $displayLimit = 5;
                                
                                // Years to show initially
                                $visibleYears = array_slice($yearsRange, 0, $displayLimit);
                                
                                // Ensure current selection is visible if not in top 5
                                if ($selYear && !in_array($selYear, $visibleYears) && in_array($selYear, $yearsRange)) {
                                    $visibleYears[] = $selYear;
                                    sort($visibleYears);
                                    $visibleYears = array_reverse($visibleYears);
                                }
                                
                                $hasMore = count($yearsRange) > count($visibleYears);
                            @endphp

                            @foreach($visibleYears as $y)
                                <option value="{{ $y }}" {{ $selYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach

                            @if($hasMore)
                                <option value="more">See More...</option>
                                @endif
                        </select>
                        {{-- Store all years for JS expansion --}}
                        <script>
                            window.allAvailableYears = @json($yearsRange);
                        </script>
                        <input type="hidden" name="month" value="{{ $month }}">
                    </form>
                </div>
                <div style="height: 300px;">
                    <canvas id="hiringChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Company Distribution Pie Chart -->
        <div class="col-lg-4">
            <div class="graph-container">
                <h3 class="dashboard-section-title">
                    <i class="fas fa-chart-pie text-info"></i> Company Distribution
                </h3>
                <div style="height: 300px; position: relative;">
                    <canvas id="companyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <style>
        .x-small { font-size: 0.75rem; }

        .year-pill {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .year-pill:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .year-pill.active {
            background: #dd270d;
            border-color: #dd270d;
            color: #fff;
            box-shadow: 0 4px 6px -1px rgba(221, 39, 13, 0.2);
        }
    </style>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
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

        function handleYearSelectChange(select) {
            const value = select.value;
            if (value === 'more') {
                // Expand the select with all available years
                const allYears = window.allAvailableYears || [];
                const currentSelection = "{{ $year }}";
                
                // Clear current options
                select.innerHTML = '';
                
                // Add all years
                allYears.forEach(y => {
                    const option = document.createElement('option');
                    option.value = y;
                    option.text = y;
                    if (y.toString() === currentSelection.toString()) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
                
                // Focus and "click" to show the user the new options? 
                // Hard to trigger open state, but at least the list is now full.
                // We'll leave it to the user to click again if it closes.
            } else {
                select.form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Hiring Trends Line Chart
            const ctx = document.getElementById('hiringChart').getContext('2d');
            const rawData = @json($hiringTrends);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            let labels = [];
            let counts = [];

            if ("{{ $month }}") {
                const daysInMonth = {{ $daysInMonth }};
                labels = Array.from({length: daysInMonth}, (_, i) => (i + 1).toString());
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

            // Company Distribution Pie Chart
            const companyCtx = document.getElementById('companyChart').getContext('2d');
            const companyData = @json($companyDistribution);
            
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
        });
    </script>
    @endpush
</x-app-layout>