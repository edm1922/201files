<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="h4 mb-0 fw-bold">Dashboard</h2>
        <small>Welcome to the CSC Document Management System</small></div>
        <div class="d-flex align-items-center gap-3">
            <div class="calendar-wrapper" x-data="{ open: false }">
                <div class="date-pill" @click="open = !open">
                    <i class="fas fa-calendar-alt me-2" style="color: #dd270d !important;"></i>
                    <span>{{ date('M d, Y', strtotime($calendarDate)) }}</span> 
                </div>

                <div class="calendar-dropdown" x-show="open" @click.away="open = false" x-transition x-cloak>
                    <div class="calendar-nav">
                        <a href="{{ route('dashboard', ['cal_date' => date('Y-m-d', strtotime('-1 month', strtotime($calendarDate))), 'year' => $year, 'month' => $month]) }}" class="calendar-nav-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <h4>{{ $calendarMonthName }} {{ $calendarYear }}</h4>
                        <a href="{{ route('dashboard', ['cal_date' => date('Y-m-d', strtotime('+1 month', strtotime($calendarDate))), 'year' => $year, 'month' => $month]) }}" class="calendar-nav-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <div class="calendar-grid">
                        <div class="calendar-weekday">Su</div>
                        <div class="calendar-weekday">Mo</div>
                        <div class="calendar-weekday">Tu</div>
                        <div class="calendar-weekday">We</div>
                        <div class="calendar-weekday">Th</div>
                        <div class="calendar-weekday">Fr</div>
                        <div class="calendar-weekday">Sa</div>

                        @foreach($calendarData as $day)
                            <div class="calendar-day {{ $day['current_month'] ? 'current' : 'other' }} {{ $day['today'] ? 'today' : '' }}">
                                {{ $day['day'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Initialize external JS variables -->
    <script>
        window.dashboardData = {
            hiringTrends: @json($hiringTrends),
            companyDistribution: @json($companyDistribution),
            bankDistribution: @json($bankDistribution),
            month: "{{ $month }}",
            daysInMonth: {{ $daysInMonth ?? 0 }}
        };
    </script>

    <!-- Consolidated Dashboard Layout (Column-based) -->
    <div class="row justify-content-center g-4">
        <!-- Main Left Column -->
        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4">
                <!-- 2x2 Stat Cards -->
                <div class="stat-cards-frame">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="stat-card h-100">
                                <div class="stat-header">
                                    <div>
                                        <span class="stat-title d-block mb-1">Total Employees</span>
                                        <span class="stat-value-large" id="mainTotalEmployees">{{ number_format($totalEmployees) }}</span>
                                    </div>
                                    <div class="stat-icon-circle">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card h-100">
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
                        <div class="col-md-6">
                            <div class="stat-card h-100">
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
                        <div class="col-md-6">
                            <div class="stat-card h-100">
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

                <!-- Hiring Trends Graph -->
                <div class="graph-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="dashboard-section-title mb-0">
                            <i class="fas fa-chart-line" style="color: #dd270d;"></i> Hiring Trends
                        </h3>
                        <form action="{{ route('dashboard') }}" method="GET" class="filter-wrapper mb-0" id="yearFilterForm" x-data="{ open: false }">
                            <input type="hidden" name="year" id="selectedYear" value="{{ $year }}">
                            <div class="custom-dropdown" @click.away="open = false">
                                <button type="button" class="dropdown-trigger" @click="open = !open">
                                    <span>{{ $year }}</span>
                                    <i class="fas fa-chevron-down ms-2" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <div class="dropdown-menu-custom" x-show="open" x-transition x-cloak>
                                    @php
                                        $yearsRange = $availableYears ?? [date('Y')];
                                    @endphp
                                    @foreach($yearsRange as $y)
                                        <div class="dropdown-item-custom {{ (int)$year == (int)$y ? 'active' : '' }}" 
                                             @click="document.getElementById('selectedYear').value = '{{ $y }}'; document.getElementById('yearFilterForm').submit()">
                                            {{ $y }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="month" value="{{ $month }}">
                        </form>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="hiringChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Right Column -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-4">
                <!-- Company Distribution Pie Chart -->
                <div class="graph-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="dashboard-section-title mb-0">
                            <i class="fas fa-chart-pie" style="color: #dd270d;"></i> Company Distribution
                        </h3>
                        <!-- Company Filter Dropdown -->
                        <div class="custom-dropdown" x-data="{ open: false, selected: 'All Companies', selectedColor: '#dd270d' }" @click.away="open = false">
                            <button type="button" class="dropdown-trigger" @click="open = !open">
                                <div class="d-flex align-items-center gap-2">
                                    <span x-show="selected !== 'All Companies'" class="legend-dot" :style="'background-color: ' + selectedColor"></span>
                                    <span x-text="selected">All Companies</span>
                                </div>
                                <i class="fas fa-chevron-down ms-2" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div class="dropdown-menu-custom" x-show="open" x-transition x-cloak id="companyFilterOptions">
                                <div class="dropdown-item-custom active" @click="selected = 'All Companies'; open = false; filterCompanyChart('All')">
                                    All Companies
                                </div>
                                <!-- Iterated items will be injected here or handled by JS -->
                            </div>
                        </div>
                    </div>
                    <div style="height: 250px; position: relative;" class="mt-3 chart-with-center">
                        <canvas id="companyChart"></canvas>
                        <div class="chart-center-text">
                            <span class="center-value" id="totalEmployeeCount">{{ number_format($totalEmployees) }}</span>
                            <span class="center-label">Total Enrolled</span>
                        </div>
                    </div>
                    <div id="companyLegend" class="custom-chart-legend mt-4"></div>
                </div>

                <!-- Bank Employee Column Chart -->
                <div class="graph-container">
                    <h3 class="dashboard-section-title mb-2">
                        <i class="fas fa-university" style="color: #dd270d;"></i> Bank Distribution
                    </h3>
                    <div style="height: 300px; position: relative;">
                        <canvas id="bankChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>