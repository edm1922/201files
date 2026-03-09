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

        {{-- Human Resource Dropdown --}}
        <li class="nav-item" x-data="{ open: {{ request()->is('applicant-files*') || request()->is('employee-list*') ? 'true' : 'false' }} }">
            <a class="nav-link {{ request()->is('applicant-files*') || request()->is('employee-list*') ? 'active' : '' }}"
               href="javascript:void(0)"
               @click="open = !open"
               style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-folder-open"></i> Human Resource
                </span>
                <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'" style="font-size: 0.8rem;"></i>
            </a>

            <ul class="nav flex-column ms-3" x-show="open" x-transition x-cloak>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('applicant-files') ? 'fw-bold text-primary' : '' }}" href="{{ url('/applicant-files') }}">
                        <i class="fas fa-file-alt"></i> 201 Files
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('employee-list*') ? 'fw-bold text-primary' : '' }}" href="{{ url('/employee-list') }}">
                        <i class="fas fa-users"></i> Employee List
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
