{{-- ── Sidebar Backdrop ── --}}
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

{{-- ── Sidebar Drawer ── --}}
<div class="sidebar-drawer" id="sidebar-drawer">

    {{-- User Info --}}
    <div class="sidebar-user-info">
        <div class="sidebar-user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="sidebar-user-details">
            <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
            <div class="sidebar-user-role">
                <span class="badge bg-{{ Auth::user()->role === 'admin' ? 'danger' : (Auth::user()->role === 'encoder' ? 'warning' : 'info') }}">
                    {{ ucfirst(Auth::user()->role) }}
                </span>
            </div>
        </div>
    </div>

    <ul class="nav flex-column">

        {{-- ═══ MAIN ═══ --}}
        <li class="sidebar-section-label">MAIN</li>

        {{-- Dashboard --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>

        {{-- 201 Files (all roles) --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('201files*') ? 'active' : '' }}" href="{{ url('/201files') }}">
                <i class="fas fa-folder-open"></i> 201 Files
            </a>
        </li>

        {{-- ═══ DOCUMENT MANAGEMENT ═══ --}}
        <li class="sidebar-section-label">DOCUMENT MANAGEMENT</li>

        {{-- Department Dropdown --}}
        <li class="nav-item" x-data="{ open: {{ request()->is('departments*') ? 'true' : 'false' }} }">
            <a class="nav-link {{ request()->is('departments*') ? 'active' : '' }}"
               href="javascript:void(0)"
               @click="open = !open"
               style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-building"></i> Departments
                </span>
                <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" style="font-size: 0.7rem;"></i>
            </a>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                @php
                    $departments = \App\Models\Department::all();
                @endphp
                @foreach($departments as $dept)
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/departments/' . $dept->id) }}">
                        <i class="fas fa-folder"></i> {{ $dept->name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </li>

        {{-- Search --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('search*') ? 'active' : '' }}" href="{{ url('/search') }}">
                <i class="fas fa-search"></i> Search Documents
            </a>
        </li>

        @if(Auth::user()->hasRole('admin', 'encoder'))
        {{-- ═══ ENCODING ═══ --}}
        <li class="sidebar-section-label">ENCODING</li>

        {{-- Employees --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('employees*') ? 'active' : '' }}" href="{{ url('/employees') }}">
                <i class="fas fa-users"></i> Employees
            </a>
        </li>

        {{-- Companies --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('companies*') ? 'active' : '' }}" href="{{ url('/companies') }}">
                <i class="fas fa-briefcase"></i> Companies
            </a>
        </li>
        @endif

        @if(Auth::user()->isAdmin())
        {{-- ═══ ADMINISTRATION ═══ --}}
        <li class="sidebar-section-label">ADMINISTRATION</li>

        {{-- User Management --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="{{ url('/users') }}">
                <i class="fas fa-user-cog"></i> User Management
            </a>
        </li>

        {{-- Settings Dropdown --}}
        <li class="nav-item" x-data="{ open: {{ request()->is('settings*') ? 'true' : 'false' }} }">
            <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}"
               href="javascript:void(0)"
               @click="open = !open"
               style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-cogs"></i> Settings
                </span>
                <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" style="font-size: 0.7rem;"></i>
            </a>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/settings/document-types') }}">
                        <i class="fas fa-file-alt"></i> Document Types
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/settings/physical-locations') }}">
                        <i class="fas fa-archive"></i> Physical Locations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/settings/departments') }}">
                        <i class="fas fa-sitemap"></i> Departments
                    </a>
                </li>
            </ul>
        </li>

        {{-- Reports Dropdown --}}
        <li class="nav-item" x-data="{ open: {{ request()->is('reports*') ? 'true' : 'false' }} }">
            <a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}"
               href="javascript:void(0)"
               @click="open = !open"
               style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-chart-bar"></i> Reports
                </span>
                <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" style="font-size: 0.7rem;"></i>
            </a>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/reports/missing-documents') }}">
                        <i class="fas fa-exclamation-triangle"></i> Missing Documents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/reports/expiry-alerts') }}">
                        <i class="fas fa-clock"></i> Expiry Alerts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/reports/audit-log') }}">
                        <i class="fas fa-history"></i> Audit Log
                    </a>
                </li>
            </ul>
        </li>
        @endif

    </ul>
</div>
