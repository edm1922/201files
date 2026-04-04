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
        <li class="nav-item" x-data="{ open: {{ request()->is('department-documents*') ? 'true' : 'false' }} }">
            <a class="nav-link {{ request()->is('department-documents*') ? 'active' : '' }}"
               href="{{ route('department-documents.index') }}"
               @click.prevent="open = !open"
               style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-building"></i> Departments
                </span>
                <div @click.stop="window.location.href='{{ route('department-documents.index') }}'" style="cursor: pointer; padding: 0 5px;">
                     <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" style="font-size: 0.7rem;"></i>
                </div>
            </a>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                @php
                    $sidebarUser = Auth::user();
                    $sidebarDepartments = $sidebarUser->isAdmin()
                        ? \App\Models\Department::query()->where('is_active', true)->orderBy('name')->get()
                        : $sidebarUser->authorizedDepartments()->where('is_active', true)->orderBy('departments.name')->get();
                @endphp
                @foreach($sidebarDepartments as $sidebarDept)
                <li class="nav-item">
                    <a class="nav-link {{ request('department_id') == $sidebarDept->id ? 'active' : '' }}" href="{{ route('department-documents.index', ['department_id' => $sidebarDept->id]) }}">
                        <i class="fas fa-folder"></i> {{ $sidebarDept->name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </li>

        <!-- @if(Auth::user()->hasRole('admin', 'encoder'))
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
        @endif -->

        @if(Auth::user()->isAdmin())
        {{-- ═══ ADMINISTRATION ═══ --}}
        <li class="sidebar-section-label">ADMINISTRATION</li>

        {{-- User Management --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('settings/users*') ? 'active' : '' }}" href="{{ route('settings.users.index') }}">
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
                    <a class="nav-link {{ request()->is('settings/document-types*') ? 'active' : '' }}" href="{{ route('settings.document-types.index') }}">
                        <i class="fas fa-file-alt"></i> Document Types
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.folder-locations.*') ? 'active' : '' }}" href="{{ route('settings.folder-locations.index') }}">
                        <i class="fas fa-folder-tree me-2"></i>
                        <span>Folder Locations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('settings/departments*') ? 'active' : '' }}" href="{{ route('settings.departments.index') }}">
                        <i class="fas fa-sitemap"></i> Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('settings/bank-types*') ? 'active' : '' }}" href="{{ route('settings.bank-types.index') }}">
                        <i class="fas fa-university"></i> Bank Types
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('settings/companies*') ? 'active' : '' }}" href="{{ route('settings.companies.index') }}">
                        <i class="fas fa-briefcase"></i> Companies
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
                    <a class="nav-link" href="{{ url('/reports/generate') }}">
                        <i class="fas fa-file-export"></i> Generate Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/reports/audit-log') }}">
                        <i class="fas fa-history"></i> Activity Logs
                    </a>
                </li>
            </ul>
        </li>

        {{-- Archive (admin only) --}}
        <li class="nav-item mt-2">
            <a class="nav-link {{ request()->routeIs('archives.index') ? 'active' : '' }}" href="{{ route('archives.index', ['tab' => 'employees']) }}">
                <i class="fas fa-box-archive"></i> Archives
            </a>
        </li>
        @endif

    </ul>
</div>
