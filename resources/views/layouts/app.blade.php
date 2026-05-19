<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WA Campaign')</title>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .brand-text {
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            transition: width 0.3s;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: 62px;
        }

        .sidebar.collapsed .nav-label,
        /* .sidebar.collapsed .brand-text, */
        .sidebar.collapsed .admin-name,
        .sidebar.collapsed .logout-text,
        .sidebar.collapsed .section-title {
            display: none;
        }

        .sidebar.collapsed .section-title {
            display: block;
            visibility: hidden;
            height: 12px;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>


    @yield('styles')
</head>

<body>

    <div class="d-flex">

        <div class="sidebar bg-dark d-flex flex-column flex-shrink-0" id="sidebar">

            <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom border-secondary">
                <span class="text-white fw-bold brand-text">
                    <i class="{{ $settings->site_icon ?? 'bi bi-whatsapp' }} text-success me-1"></i>
                    {{ $settings->site_name ?? 'WA Campaign' }}
                </span>
                <button class="btn btn-sm btn-outline-secondary text-white border-0" id="toggleBtn">
                    <i class="bi bi-list fs-5"></i>
                </button>
            </div>

            <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom border-secondary">
                <div class="bg-success rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:36px;height:36px;font-size:13px;">
                    {{ strtoupper(substr(explode(' ', auth()->user()->name ?? 'A A')[0], 0, 1) . substr(explode(' ', auth()->user()->name ?? 'A A')[1] ?? '', 0, 1)) }}
                </div>
                <div class="admin-name">
                    <div class="text-white small fw-semibold">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="text-success" style="font-size:11px;">Admin</div>
                </div>
            </div>

            <nav class="flex-grow-1 pt-2">

                <div class=" text-secondary px-3 mb-1 section-title"
                    style="font-size:10px;text-transform:uppercase;letter-spacing:1px;">Main</div>

                <a href="{{ route('wa.dashboard') }}"
                    class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('wa.dashboard') ? 'active bg-primary text-white' : '' }}">
                    <i class="bi bi-speedometer2 fs-5 flex-shrink-0"></i>
                    <span class="nav-label">Dashboard</span>
                </a>

                <hr class="border-secondary my-2 mx-2">
                <div class="text-secondary px-3 mb-1 section-title"
                    style="font-size:10px;text-transform:uppercase;letter-spacing:1px;">Manage</div>

                <a href="{{ route('clients.index') }}"
                    class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('clients.*') ? 'active bg-primary text-white' : '' }}">
                    <i class="bi bi-people fs-5 flex-shrink-0"></i>
                    <span class="nav-label">Clients</span>
                </a>


                <a href="{{ route('templates.index') }}"
                    class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('templates.*') ? 'active bg-primary text-white' : '' }}">
                    <i class="bi bi-file-earmark-text fs-5 flex-shrink-0"></i>
                    <span class="nav-label">Templates</span>
                </a>

                <a href="{{ route('campaigns.index') }}"
                    class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('campaigns.*') ? 'active bg-primary text-white' : '' }}">
                    <i class="bi bi-megaphone fs-5 flex-shrink-0"></i>
                    <span class="nav-label">Campaigns</span>
                </a>

                <a href="{{ route('settings.index') }}"
                    class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('settings.*') ? 'active bg-primary text-white' : '' }}">
                    <i class="bi bi-gear fs-5 flex-shrink-0"></i>
                    <span class="nav-label">Settings</span>
                </a>
            </nav>

            <div class="border-top border-secondary p-2">
                <form method="POST" action="{{ route('wa.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-left flex-shrink-0"></i>
                        <span class="logout-text">Logout</span>
                    </button>
                </form>
            </div>

        </div>

        <div class="flex-grow-1 d-flex flex-column" style="min-height: 100vh; overflow: hidden;">

            <nav class="navbar bg-white border-bottom px-3 sticky-top shadow-sm">
                <span class="navbar-brand mb-0 fw-semibold">@yield('page-title', 'Dashboard')</span>
                <span class="text-muted small">{{ auth()->user()->name ?? 'Admin' }}</span>
            </nav>

            <div class="p-4" style="overflow-y: auto; height: calc(100vh - 57px);">
                @yield('content')
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        toggleBtn.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    </script>

    @stack('scripts')
</body>

</html>
