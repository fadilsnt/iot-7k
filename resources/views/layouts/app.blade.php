<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IoT TKHS Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="app-shell ottoman-pattern">
    <div class="shell-grid">
        <aside class="sidebar collapsed" id="appSidebar">
            <div class="sidebar-header">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <span class="brand-pack">
                        <i class="fa-solid fa-temperature-high app-logo"></i>
                        <span class="brand-text">IOT TKHS</span>
                    </span>
                    <i class="fa-solid fa-bars burger"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard', absolute: false) }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-home"></i>
                    <span class="sidebar-label">Dashboard</span>
                </a>
                <a href="{{ route('sensors.index', absolute: false) }}"
                    class="sidebar-link {{ request()->routeIs('sensors.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-microchip"></i>
                    <span class="sidebar-label">Sensors</span>
                </a>
                <a href="{{ route('activity-log.index', absolute: false) }}"
                    class="sidebar-link {{ request()->routeIs('activity-log.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span class="sidebar-label">Activity Log</span>
                </a>
                <a href="{{ route('machines.index', absolute: false) }}"
                    class="sidebar-link {{ request()->routeIs('machines.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-industry sidebar-icon"></i>
                    <span class="sidebar-label">Monitoring Mesin</span>
                </a>
                <a href="{{ route('rpm.index', absolute: false) }}"
                    class="sidebar-link {{ request()->routeIs('rpm.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tachometer sidebar-icon"></i>
                    <span class="sidebar-label">Monitoring RPM</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="{{ route('profile.edit') }}" class="profile-chip"
                    style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; transition: all 0.3s ease;">
                    @if (Auth::user()?->profile_photo)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile"
                            class="profile-avatar"
                            style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                    @else
                        <div class="profile-avatar">{{ strtoupper(mb_substr(Auth::user()->name ?? 'User', 0, 2)) }}
                        </div>
                    @endif
                    <div class="profile-details">
                        <p class="m-0 font-semibold">{{ Auth::user()->name ?? 'Guest User' }}</p>
                        <small style="opacity:.7;">{{ Auth::user()->position ?? 'IoT Operator' }}</small>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </aside>
        <div class="main-panel">
            <div class="main-scroll">
                {{ $slot }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ url('/build/assets/app-ByW0VTRm.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('appSidebar');
            const toggle = document.getElementById('sidebarToggle');
            toggle?.addEventListener('click', () => {
                sidebar?.classList.toggle('collapsed');
            });

            // Set logout flag on logout (keep bypass cookies for re-login)
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    // Set logout flag (5 years expiry - same as bypass cookies)
                    const fiveYearsInSeconds = 157680000;
                    document.cookie = 'bypass_logged_out=true; path=/; max-age=' + fiveYearsInSeconds;

                    console.log('🚪 Logout: Set logged_out flag (bypass cookies preserved)');

                    // Let form submit normally to clear server session
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
