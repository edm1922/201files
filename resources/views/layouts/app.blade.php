<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="meili-search-url" content="{{ route('employees.meiliSearch') }}">
    <meta name="app-base-url" content="{{ rtrim(url('/'), '/') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo2.png') }}">




    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Initialize Alpine.js so the script can be used (local or Public Directory) -->
    <!-- <script defer src="{{ asset('js/alpine.min.js') }}"></script> -->

    <!-- to work App JS (local or Public Directory) -->
    <script defer src="{{ asset('js/app.js') }}"></script>

    <!-- To Initialize App CSS (local or Public Directory) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Custom styles -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    @if(request()->routeIs('dashboard'))
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
        <script src="{{ asset('js/dashboard.js') }}" defer></script>
    @endif

    @stack('styles')
</head>
<body>

    @php
        $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();
    @endphp

    @if(!request()->routeIs('about'))
    <!-- ── Top Navbar ── -->
    <nav class="navbar navbar-dark fixed-top topbar" role="navigation" aria-label="Primary">
        <div class="container-fluid topbar__inner">
            <!-- Drawer toggle -->
            <button class="btn topbar__icon-btn me-2" id="sidebar-toggle" title="Toggle menu" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center topbar__brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('logo2.png') }}" alt="CSC-DMS Logo" height="35" class="me-2">
                CSC-DMS
            </a>

            <div class="topbar__actions ms-auto">
                <a class="btn topbar__icon-btn topbar__notification" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications{{ $unreadNotificationsCount > 0 ? ' (' . $unreadNotificationsCount . ' unread)' : '' }}">
                    <i class="fas fa-bell"></i>
                    @if ($unreadNotificationsCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <div class="dropdown topbar__user-dropdown">
                    <button class="btn topbar__user-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open user menu">
                        <span class="topbar__user-icon" aria-hidden="true">
                            <i class="fas fa-user"></i>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end topbar__user-menu">
                        <li class="topbar__menu-header">
                            <div class="topbar__menu-name">{{ Auth::user()->name }}</div>
                            <div class="topbar__menu-role">{{ ucfirst(Auth::user()->role ?? 'User') }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item topbar__menu-logout">Log Out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    {{-- ── Sidebar ── --}}
    @include('layouts.sidebar')
    @endif

    <!-- ── Main Content ── -->
    <main class="main-content {{ request()->routeIs('about') ? 'full-display' : '' }}">
        {{ $slot }}
    </main>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
