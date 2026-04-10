<x-app-layout>
    <div class="animate-fade-in stagger-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold text-dark">Data Export & Reports</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Generate tailored datasets with advanced
                    filtering and customized column selection.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-flash alert-flash--success mb-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-flash alert-flash--error mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="row g-4">
            {{-- ── SECTION: WORKFORCE MANAGEMENT ── --}}
            <div class="col-12 mt-5">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fas fa-users-cog text-brand"></i> Workforce Management
                    <hr class="flex-grow-1 ms-2 opacity-10">
                </h5>
            </div>

            {{-- ── Employee Master List Export ── --}}
            <div class="col-lg-8 animate-fade-in stagger-2">
                <div class="card report-card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-brand-light text-brand me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h5 class="report-title">Employee Master List</h5>
                                <p class="report-desc">Comprehensive employee list with customizable data columns.</p>
                            </div>
                        </div>

                        <form action="{{ route('reports.export-employees') }}" method="GET" data-report-export-form>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="report-label">Company</label>
                                    <select name="company_id" class="form-select report-input">
                                        <option value="">All Companies</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="report-label">Status</label>
                                    <select name="status" class="form-select report-input">
                                        <option value="">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="awol">AWOL</option>
                                        <option value="resigned">Resigned</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="report-label">Hiring Year</label>
                                    <select name="year" class="form-select report-input">
                                        <option value="">All Years</option>
                                        @for ($y = date('Y'); $y >= 2000; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="report-label">Hiring Month</label>
                                    <select name="month" class="form-select report-input">
                                        <option value="">All Months</option>
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $i => $m)
                                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="report-label mb-2 d-block">Select Columns to Include</label>
                                <div class="column-picker scrollable-y bg-light p-3 rounded-3"
                                    style="max-height: 120px;">
                                    <div class="row g-2">
                                        @php
                                            $cols = [
                                                'system_id' => 'System ID',
                                                'barcode_id' => 'Barcode ID',
                                                'full_name' => 'Full Name',
                                                'company' => 'Company',
                                                'status' => 'Status',
                                                'date_hired' => 'Date Hired',
                                                'folder_code' => 'Folder Code',
                                                'location' => 'Physical Location',
                                                'atm_status' => 'ATM Status',
                                                'bank_type' => 'Bank Type',
                                                'archive_date' => 'Archive Date'
                                            ];
                                        @endphp
                                        @foreach($cols as $val => $label)
                                            <div class="col-md-4 col-6">
                                                <div class="form-check custom-checkbox">
                                                    <input class="form-check-input" type="checkbox" name="columns[]"
                                                        value="{{ $val }}" id="col_{{ $val }}" checked>
                                                    <label class="form-check-label text-muted small"
                                                        style="cursor: pointer;" for="col_{{ $val }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="btn btn-brand w-100 py-2 d-flex align-items-center justify-content-center"
                                data-export-submit>
                                <i class="fas fa-file-csv me-2"></i>
                                <span class="fw-bold" data-export-label>Download Master List</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Company Summary Card ── --}}
            <div class="col-lg-4 animate-fade-in stagger-3">
                <div class="card report-card bg-dark text-white h-100 shadow-sm border-0">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-white-10 text-white me-3">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div>
                                <h5 class="report-title text-white">Company Tally</h5>
                                <p class="report-desc text-white-50">Headcount distribution & snapshots.</p>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <p class="small text-white-50 mb-4">Produces a summary of active vs resigned employees per
                                company registered in the system.</p>
                            <a href="{{ route('reports.export-company-summary') }}"
                                class="btn btn-outline-light w-100 py-2 fw-bold">
                                <i class="fas fa-download me-2"></i> Export Summary
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION: ARCHIVAL & STORAGE ── --}}
            <div class="col-12 mt-5">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fas fa-boxes text-brand"></i> Archival & Storage
                    <hr class="flex-grow-1 ms-2 opacity-10">
                </h5>
            </div>

            <div class="col-lg-6 animate-fade-in stagger-4">
                <div class="card report-card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-success-light text-success me-3">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <div>
                                <h5 class="report-title">Storage Utilization</h5>
                                <p class="report-desc">Occupancy report showing slots availability.</p>
                            </div>
                        </div>

                        <form action="{{ route('reports.export-storage-utilization') }}" method="GET"
                            data-report-export-form>
                            <div class="mb-4">
                                <label class="report-label">Filter by Storage Type</label>
                                <select name="type" class="form-select report-input">
                                    <option value="201">201 Files Storage (Employees)</option>
                                    <option value="docs">Department Documents Storage</option>
                                </select>
                            </div>
                            <p class="text-muted small mb-4">This report shows the total capacity, occupied slots, and
                                space remaining.</p>
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm"
                                data-export-submit>
                                <i class="fas fa-file-csv me-2"></i>
                                <span data-export-label>Download Storage Audit</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 animate-fade-in stagger-5">
                <div class="card report-card h-100 shadow-sm border-0 border-brand-dashed">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-brand-light text-brand me-3">
                                <i class="fas fa-search-location"></i>
                            </div>
                            <div>
                                <h5 class="report-title">Identify Available Folders</h5>
                                <p class="report-desc">Find available space for new folders.</p>
                            </div>
                        </div>
                        <p class="text-muted small mb-4">A list of folders that haven't been
                            assigned to an employee.</p>
                        <a href="{{ route('reports.export-available-folders') }}"
                            class="btn btn-brand w-100 py-2 fw-bold">
                            <i class="fas fa-file-csv me-2"></i> Export Available Folder Slots
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── SECTION: COMPLIANCE & SECURITY ── --}}
            <div class="col-12 mt-5">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fas fa-shield-alt text-brand"></i> Compliance & Security
                    <hr class="flex-grow-1 ms-2 opacity-10">
                </h5>
            </div>



            {{-- ── Expiry Report ── --}}
            <div class="col-lg-6 animate-fade-in stagger-7">
                <div class="card report-card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-1">
                            <h5 class="report-title fs-5 mb-0">Document Expiry</h5>
                            <span class="badge bg-secondary ms-auto fw-medium">Monitoring</span>
                        </div>
                        <p class="report-desc mb-4">Manage document statuses by their expiry timelines.</p>

                        <form action="{{ route('reports.export-expiry-report') }}" method="GET" data-report-export-form>
                            <div class="mb-3">
                                <label class="report-label">Filter by Status</label>
                                <select name="expiry_status" class="form-select report-input">
                                    <option value="all">All Documents</option>
                                    <option value="expired">Already Expired</option>
                                    <option value="expiring">Expiring Soon (Next 30 Days)</option>
                                    <option value="valid">Valid (Active)</option>
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="report-label">Target Year</label>
                                    <select name="year" class="form-select report-input">
                                        <option value="">All Years</option>
                                        @for ($y = date('Y') + 5; $y >= 2023; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="report-label">Target Month</label>
                                    <select name="month" class="form-select report-input">
                                        <option value="">All Months</option>
                                        @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $i => $m)
                                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="report-label">Department</label>
                                <select name="department_id" class="form-select report-input">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                class="btn btn-secondary w-100 py-2 d-flex align-items-center justify-content-center text-white"
                                data-export-submit>
                                <i class="fas fa-hourglass-half me-2"></i>
                                <span class="fw-bold" data-export-label>Download Expiry List</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Audit Log Export ── --}}
            <div class="col-lg-6 animate-fade-in stagger-8">
                <div class="card report-card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-1">
                            <h5 class="report-title fs-5 mb-0">Full Activity Logs</h5>
                            <span class="badge bg-dark ms-auto fw-medium">Auditing</span>
                        </div>
                        <p class="report-desc mb-4">Filtered systemic trace logs for security tracking.</p>

                        <form action="{{ route('reports.export-audit-logs') }}" method="GET" data-report-export-form>
                            <div class="mb-3">
                                <label class="report-label">Target User</label>
                                <select name="user_id" class="form-select report-input">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <label class="report-label">Year Filter</label>
                                    <select name="year" class="form-select report-input">
                                        <option value="">All Years</option>
                                        @for ($y = date('Y'); $y >= 2023; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="report-label">Month Filter</label>
                                    <select name="month" class="form-select report-input">
                                        <option value="">All Months</option>
                                        @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $i => $m)
                                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit"
                                class="btn btn-dark w-100 py-2 d-flex align-items-center justify-content-center"
                                data-export-submit>
                                <i class="fas fa-file-invoice me-2"></i>
                                <span class="fw-bold" data-export-label>Export History</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .report-card {
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08) !important;
        }

        .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .bg-brand-light {
            background: rgba(221, 39, 13, 0.1);
        }

        .bg-success-light {
            background: rgba(16, 185, 129, 0.1);
        }

        .bg-secondary-light {
            background: rgba(100, 116, 139, 0.1);
        }

        .bg-white-10 {
            background: rgba(255, 255, 255, 0.1);
        }

        .border-brand-dashed {
            border: 1px dashed rgba(221, 39, 13, 0.3) !important;
        }

        .report-title {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 2px;
        }

        .report-desc {
            color: #64748b;
            font-size: 0.8rem;
            margin-bottom: 0;
        }

        .report-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .report-input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.85rem;
            background-color: #f8fafc;
            padding: 8px 12px;
        }

        .report-input:focus {
            background-color: #fff;
            border-color: #dd270d;
            box-shadow: 0 0 0 3px rgba(221, 39, 13, 0.1);
        }

        .column-picker::-webkit-scrollbar {
            width: 5px;
        }

        .column-picker::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>

    @push('scripts')
        <script>
            (() => {
                document.querySelectorAll('form[data-report-export-form]').forEach((form) => {
                    if (form.dataset.exportBound === '1') return;
                    form.dataset.exportBound = '1';

                    form.addEventListener('submit', () => {
                        const button = form.querySelector('[data-export-submit]');
                        const label = form.querySelector('[data-export-label]');
                        if (!button || !label || button.disabled) return;

                        button.disabled = true;
                        button.dataset.originalLabel = label.textContent.trim();
                        label.textContent = 'Preparing CSV...';

                        const spinner = document.createElement('span');
                        spinner.className = 'spinner-border spinner-border-sm me-2';
                        spinner.dataset.exportSpinner = '1';
                        button.insertBefore(spinner, button.firstChild);

                        // Reactivate button after a while since browser download doesn't trigger "back" event
                        setTimeout(() => {
                            button.disabled = false;
                            label.textContent = button.dataset.originalLabel;
                            const s = button.querySelector('[data-export-spinner]');
                            if (s) s.remove();
                        }, 5000);
                    });
                });
            })();
        </script>
    @endpush
</x-app-layout>