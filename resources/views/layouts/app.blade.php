<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'GSC Risk Intelligence') }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-navy: #3E53A0;
            --primary-blue: #1C55FF;
            --bg-light: #F4F7FE;
            --card-border-radius: 12px;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #333;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--primary-navy);
            color: #fff;
            z-index: 1000;
            transition: all var(--transition-speed);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 20px 24px;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .nav-sidebar {
            padding: 15px 0;
            list-style: none;
            margin: 0;
            flex-grow: 1;
        }

        .nav-sidebar .nav-item { margin-bottom: 4px; padding: 0 0 0 15px; }

        .nav-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255,255,255,0.7);
            border-radius: 30px 0 0 30px;
            transition: all var(--transition-speed);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
        }

        .nav-sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav-sidebar .nav-link.active {
            background-color: var(--bg-light);
            color: var(--primary-navy);
            font-weight: 700;
        }

        /* Mini Cards in Sidebar */
        .sidebar-widget {
            background-color: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 15px;
            margin: 15px;
            font-size: 0.8rem;
        }
        
        .sidebar-widget h6 { font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: #fff; }
        .sidebar-widget p { margin: 0; color: rgba(255,255,255,0.6); }

        /* Main Content Area */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all var(--transition-speed);
        }

        /* Top Navbar */
        .top-navbar {
            background-color: #fff;
            padding: 15px 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .search-bar-wrapper {
            position: relative;
            width: 300px;
        }

        .search-bar-wrapper .bi-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .search-bar-wrapper input {
            padding-left: 40px;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
            background-color: #f9f9f9;
        }

        .search-bar-wrapper input:focus {
            background-color: #fff;
            box-shadow: none;
            border-color: var(--primary-blue);
        }

        .nav-actions { display: flex; align-items: center; gap: 20px; }
        .nav-actions .icon-btn { color: #666; font-size: 1.2rem; cursor: pointer; position: relative; }
        .nav-actions .badge { position: absolute; top: -5px; right: -5px; font-size: 0.6rem; }
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .user-profile .user-info { display: flex; flex-direction: column; }
        .user-profile .user-name { font-weight: 600; font-size: 0.9rem; color: #333; line-height: 1; }
        .user-profile .user-role { font-size: 0.75rem; color: #888; }

        /* Card Styles */
        .card {
            border: none;
            border-radius: var(--card-border-radius);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 16px 20px;
            font-weight: 600;
            border-radius: var(--card-border-radius) var(--card-border-radius) 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Mobile Adjustments */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
            .sidebar-toggler { display: block !important; }
        }
        
        .sidebar-toggler { display: none; background: none; border: none; font-size: 1.5rem; color: #333; }
        
        /* Utility */
        .text-navy { color: var(--primary-navy); }
        .bg-light-blue { background-color: #E6EDFF; }
        .bg-light-success { background-color: #E8F5E9; }
        .bg-light-danger { background-color: #FFEBEE; }
        .bg-light-warning { background-color: #FFF3E0; }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #888;
        }
        .empty-state i { font-size: 2.5rem; color: #ddd; margin-bottom: 10px; }
    </style>
    @stack('styles')
</head>
<body>

    @guest
        <!-- Simple Layout for Guest/Auth -->
        <div class="container-fluid py-4">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    @else
        <!-- SaaS Layout for Authenticated Users -->
        
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-globe-americas text-primary"></i> GSC Risk
            </div>
            
            <ul class="nav-sidebar">
                @if(Auth::user()->role === 'admin')
                    <div class="px-3 pb-2 text-uppercase" style="font-size: 0.65rem; color: #888; font-weight: 700; letter-spacing: 1px;">MAIN MENU</div>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> User Management</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.ports.*') ? 'active' : '' }}" href="{{ route('admin.ports.index') }}"><i class="bi bi-pin-map"></i> Port Dataset</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}"><i class="bi bi-journal-text"></i> Analysis Articles</a></li>
                @else
                    <div class="px-3 pb-2 text-uppercase" style="font-size: 0.65rem; color: #888; font-weight: 700; letter-spacing: 1px;">{{ __('Main Menu') }}</div>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}"><i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.shipments.*') ? 'active' : '' }}" href="{{ route('user.shipments.index') }}"><i class="bi bi-box-seam"></i> {{ __('Shipment Monitoring') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.country') || request()->routeIs('user.countries.index') ? 'active' : '' }}" href="{{ route('user.countries.index') }}"><i class="bi bi-flag"></i> {{ __('Countries') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.weather') ? 'active' : '' }}" href="{{ route('user.weather') }}"><i class="bi bi-cloud-sun"></i> {{ __('Weather') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.currency') ? 'active' : '' }}" href="{{ route('user.currency') }}"><i class="bi bi-currency-exchange"></i> {{ __('Currency') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.news') ? 'active' : '' }}" href="{{ route('user.news') }}"><i class="bi bi-newspaper"></i> {{ __('News Intelligence') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.ports.*') ? 'active' : '' }}" href="{{ route('user.ports.index') }}"><i class="bi bi-pin-map"></i> {{ __('Ports') }}</a></li>

                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.comparison') ? 'active' : '' }}" href="{{ route('user.comparison') }}"><i class="bi bi-bar-chart-line"></i> {{ __('Country Comparison') }}</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('user.watchlist.index') ? 'active' : '' }}" href="{{ route('user.watchlist.index') }}"><i class="bi bi-star"></i> {{ __('Favorite Monitoring') }}</a></li>
                @endif
            </ul>


            
            <!-- System Status -->
            <div class="mt-auto px-3 py-3 border-top border-secondary border-opacity-25" id="system-status-widget" style="background-color: transparent;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-white-50 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ __('System Status') }}</span>
                    <i class="bi bi-arrow-repeat text-white-50" id="sync-spinner" style="font-size: 0.8rem;"></i>
                </div>
                
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span id="sync-indicator" class="rounded-circle bg-success" style="width: 8px; height: 8px; box-shadow: 0 0 5px #198754;"></span>
                    <span id="sync-text" class="text-white fw-medium" style="font-size: 0.75rem;">{{ __('All Systems Operational') }}</span>
                </div>

                <div class="text-white-50" style="font-size: 0.65rem;">
                    <span id="last-sync-time">{{ now()->format('d M Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div id="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="sidebar-toggler" id="sidebarToggle"><i class="bi bi-list"></i></button>
                    <h5 class="m-0 fw-semibold d-none d-sm-block text-capitalize">{{ __(str_replace(['user.', 'admin.', 'index'], '', Route::currentRouteName() ?? 'Dashboard')) }}</h5>
                </div>
                


                <div class="nav-actions">
                    <!-- Language Switcher -->
                    <div class="dropdown">
                        <div class="icon-btn" data-bs-toggle="dropdown" style="cursor: pointer;">
                            <i class="bi bi-translate"></i> <span style="font-size:0.8rem; font-weight:bold;">{{ strtoupper(App::getLocale()) }}</span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">🇬🇧 {{ __('English') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('lang.switch', 'id') }}">🇮🇩 {{ __('Indonesian') }}</a></li>
                        </ul>
                    </div>
                    
                    <div class="dropdown">
                        <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff" alt="User">
                            <div class="user-info d-none d-sm-flex">
                                <span class="user-name">{{ Auth::user()->name }}</span>
                                <span class="user-role text-capitalize">{{ Auth::user()->role }}</span>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="container-fluid p-4">
                @if(session('status'))
                    <div class="alert alert-success border-0 shadow-sm">{{ session('status') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    @endguest

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            if(sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('show');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>