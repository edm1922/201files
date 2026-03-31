<x-app-layout>
    <div class="animate-fade-in stagger-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 fw-bold text-dark">Data Export & Reports</h2>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Generate and download comprehensive datasets for auditing and compliance.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.audit-log') }}" class="btn btn-action-round" title="View Real-time Logs"
                    aria-label="View real-time audit logs">
                    <i class="fas fa-history"></i>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-flash alert-flash--success mb-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-flash alert-flash--error mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-flash alert-flash--error mb-4" data-report-errors>
                <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="row g-4">
            {{-- ── Employee Master List Export ── --}}
            <div class="col-lg-6 animate-fade-in stagger-2">
                <div class="card doc-list-card h-100 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-4">
                            <div class="file-icon-wrapper file-icon--xls me-3" style="width: 48px; height: 48px; font-size: 1.5rem; background: rgba(221, 39, 13, 0.1); color: #dd270d;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1" style="font-size: 1.1rem; color: #1e293b;">Employee master List</h5>
                                <p class="text-muted small mb-0">Complete roster including IDs, assignment, and status.</p>
                            </div>
                        </div>

                        <form action="{{ route('reports.export-employees') }}" method="GET" class="mt-auto" data-report-export-form>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted" style="font-size: 0.65rem;">Company</label>
                                    <select name="company_id" class="form-select field-input @error('company_id') is-invalid @enderror">
                                        <option value="">All Companies</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}" {{ (string) request('company_id') === (string) $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted" style="font-size: 0.65rem;">Status</label>
                                    <select name="status" class="form-select field-input @error('status') is-invalid @enderror">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="resigned" {{ request('status') === 'resigned' ? 'selected' : '' }}>Resigned (Archived)</option>
                                        <option value="awol" {{ request('status') === 'awol' ? 'selected' : '' }}>AWOL</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-brand w-100 py-2 shadow-sm d-flex align-items-center justify-content-center"
                                style="border-radius: 8px;" data-export-submit>
                                <i class="fas fa-file-csv me-2" style="font-size: 1.1rem;"></i>
                                <span class="fw-bold" data-export-label>Download Master List</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Company Summary Export ── --}}
            <div class="col-lg-6 animate-fade-in stagger-3">
                <div class="card doc-list-card h-100 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-4">
                            <div class="file-icon-wrapper file-icon--pdf me-3" style="width: 48px; height: 48px; font-size: 1.5rem; background: rgba(15, 23, 42, 0.05); color: #0f172a;">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1" style="font-size: 1.1rem; color: #1e293b;">Company distribution</h5>
                                <p class="text-muted small mb-0">Workforce totals (Active vs Resigned) per Company.</p>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <p class="text-muted small mb-4">This report provides a high-level summary of employee counts across all registered companies.</p>
                            <a href="{{ route('reports.export-company-summary') }}" class="btn btn-dark w-100 py-2 shadow-sm d-flex align-items-center justify-content-center"
                                style="background-color: #2c3340; border-color: #2c3340; border-radius: 8px;">
                                <i class="fas fa-file-csv me-2" style="font-size: 1.1rem;"></i>
                                <span class="fw-bold">Export Company Summary</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Storage Utilization Export ── --}}
            <div class="col-lg-6 animate-fade-in stagger-4">
                <div class="card doc-list-card h-100 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-4">
                            <div class="file-icon-wrapper file-icon--xls me-3" style="width: 48px; height: 48px; font-size: 1.5rem; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1" style="font-size: 1.1rem; color: #1e293b;">Storage Utilization</h5>
                                <p class="text-muted small mb-0">Occupancy report showing folders per Row and Column.</p>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <p class="text-muted small mb-4">Track shelf space efficiency and identify available slots in the physical archive.</p>
                            <a href="{{ route('reports.export-storage-utilization') }}" class="btn btn-success w-100 py-2 shadow-sm d-flex align-items-center justify-content-center"
                                style="background-color: #10b981; border-color: #10b981; border-radius: 8px;">
                                <i class="fas fa-file-csv me-2" style="font-size: 1.1rem;"></i>
                                <span class="fw-bold">Export Storage Report</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Audit Log Export ── --}}
            <div class="col-lg-6 animate-fade-in stagger-5">
                <div class="card doc-list-card h-100 shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-4">
                            <div class="file-icon-wrapper file-icon--pdf me-3" style="width: 48px; height: 48px; font-size: 1.5rem; background: rgba(100, 116, 139, 0.1); color: #64748b;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1" style="font-size: 1.1rem; color: #1e293b;">Audit & Activity Logs</h5>
                                <p class="text-muted small mb-0">Detailed event trail filtered by specific date range.</p>
                            </div>
                        </div>

                        <form action="{{ route('reports.export-audit-logs') }}" method="GET" class="mt-auto" data-report-export-form>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted" style="font-size: 0.65rem;">Date From</label>
                                    <input type="date" name="date_from"
                                        class="form-control field-input @error('date_from') is-invalid @enderror"
                                        value="{{ request('date_from', date('Y-m-01')) }}">
                                    @error('date_from')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted" style="font-size: 0.65rem;">Date To</label>
                                    <input type="date" name="date_to"
                                        class="form-control field-input @error('date_to') is-invalid @enderror"
                                        value="{{ request('date_to', date('Y-m-t')) }}">
                                    @error('date_to')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-secondary w-100 py-2 shadow-sm d-flex align-items-center justify-content-center"
                                style="border-radius: 8px; border-width: 2px;" data-export-submit>
                                <i class="fas fa-file-csv me-2" style="font-size: 1.1rem;"></i>
                                <span class="fw-bold" data-export-label>Export Activity Log</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            {{-- ── Available Slots Quick Export ── --}}
            <div class="col-12 animate-fade-in stagger-6">
                <div class="mt-4 p-4 rounded-3 text-center shadow-sm" style="background: rgba(221, 39, 13, 0.02); border: 1px dashed rgba(221, 39, 13, 0.2); border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-4">
                        <p class="text-muted mb-0 small fw-medium">
                            <i class="fas fa-search-location me-1 text-brand"></i>
                            Need to find empty shelf space?
                        </p>
                        <a href="{{ route('reports.export-available-folders') }}" class="btn btn-sm btn-brand px-4 shadow-sm">
                            <i class="fas fa-file-download me-1"></i> Download List of Available Slots
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .doc-list-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .doc-list-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
        }

        .field-input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.9rem;
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        .field-input:focus {
            background-color: #fff;
            border-color: var(--brand-red, #dd270d);
            box-shadow: 0 0 0 3px rgba(221, 39, 13, 0.1);
        }
    </style>

    @push('scripts')
        <script>
            (() => {
                document.querySelectorAll('form[data-report-export-form]').forEach((form) => {
                    if (form.dataset.exportBound === '1') {
                        return;
                    }

                    form.dataset.exportBound = '1';
                    form.addEventListener('submit', () => {
                        const button = form.querySelector('[data-export-submit]');
                        const label = form.querySelector('[data-export-label]');

                        if (!button || !label || button.disabled) {
                            return;
                        }

                        button.disabled = true;
                        button.dataset.originalLabel = label.textContent.trim();
                        label.textContent = 'Preparing export...';

                        const spinner = document.createElement('span');
                        spinner.className = 'spinner-border spinner-border-sm me-2';
                        spinner.setAttribute('role', 'status');
                        spinner.setAttribute('aria-hidden', 'true');
                        spinner.dataset.exportSpinner = '1';
                        button.insertBefore(spinner, button.firstChild);
                    });
                });
            })();
        </script>
    @endpush
</x-app-layout>
