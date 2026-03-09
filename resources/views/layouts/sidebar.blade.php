{{-- ── Sidebar Backdrop ── --}}
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

{{-- ── Sidebar Drawer ── --}}
<div class="sidebar-drawer" id="sidebar-drawer">
    <ul class="nav flex-column">

        {{-- Dashboard --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>

        {{-- 201 files --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('201files*') ? 'active' : '' }}" href="{{ url('/201files') }}">
                <i class="fas fa-clipboard-list"></i> 201 Files
            </a>
        </li>

        {{-- Department Dropdown --}}
        <li class="nav-item" x-data="{ open: {{ request()->is('applicant-files*') || request()->is('employee-list*') ? 'true' : 'false' }} }">
            <a class="nav-link {{ request()->is('applicant-files*') || request()->is('employee-list*') ? 'active' : '' }}"
               href="javascript:void(0)"
               @click="open = !open"
               style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-folder-open"></i> Department
                </span>
                <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" style="font-size: 0.8rem;"></i>
            </a>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('finance*') ? 'fw-bold text-primary' : '' }}" href="{{ url('/finance') }}">
                        <i class="fas fa-users"></i> Finance
                    </a>
                </li>
            </ul>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('cda*') ? 'fw-bold text-primary' : '' }}" href="{{ url('/cda') }}">
                        <i class="fas fa-users"></i> CDA
                    </a>
                </li>
            </ul>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('braveheart*') ? 'fw-bold text-primary' : '' }}" href="{{ url('/braveheart') }}">
                        <i class="fas fa-users"></i> Braveheart
                    </a>
                </li>
            </ul>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('accounting*') ? 'fw-bold text-primary' : '' }}" href="{{ url('/accounting') }}">
                        <i class="fas fa-users"></i> Accounting
                    </a>
                </li>
            </ul>
            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('human-resource*') ? 'fw-bold text-primary' : '' }}" href="{{ url('/human-resource') }}">
                        <i class="fas fa-users"></i> Human Resource
                    </a>
                </li>
            </ul>
        </li>

         {{-- Portal --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->is('portal*') ? 'active' : '' }}" href="{{ url('/portal') }}">
                <i class="fas fa-clipboard-list"></i> Portal
            </a>
        </li>
    </ul>
</div>
