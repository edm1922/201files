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
                            <span class="stat-title">Total Employees</span>
                            <div class="stat-icon-wrap text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-body">
                            <span class="stat-value-large">{{ number_format($totalEmployees) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Total Companies</span>
                            <div class="stat-icon-wrap text-success">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        <div class="stat-body">
                            <span class="stat-value-large">{{ $totalCompanies }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Total Documents</span>
                            <div class="stat-icon-wrap text-info">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <div class="stat-body">
                            <span class="stat-value-large">{{ number_format($totalDocuments) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Total Users</span>
                            <div class="stat-icon-wrap text-warning">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                        <div class="stat-body">
                            <span class="stat-value-large">{{ number_format($totalUsers) }}</span>
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
                    <form action="{{ route('dashboard') }}" method="GET" class="filter-wrapper mb-0">
                        <!-- <select name="month" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
                            <option value="">All Months</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select> -->
                        <select name="year" class="form-select form-select-sm" style="width: 100px;" onchange="this.form.submit()">
                            @php
                                $currentYear = date('Y');
                                $startYear = 2020;
                            @endphp
                            @foreach(range($currentYear, $startYear) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hiring Trends Line Chart
            const ctx = document.getElementById('hiringChart').getContext('2d');
            const rawData = @json($hiringTrends);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            let labels = [];
            let counts = [];

            if ("{{ $month }}") {
                labels = [months[{{ (int)$month - 1 }}]];
                counts = [rawData.length > 0 ? rawData[0].count : 0];
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

    <style>
        .x-small { font-size: 0.75rem; }
    </style>
</x-app-layout>