<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <title>@yield('title', 'WA Campaign')</title>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>

        /* ══════════════════════════════════════
           BASE LAYOUT
        ══════════════════════════════════════ */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ══════════════════════════════════════
           SIDEBAR — DESKTOP
        ══════════════════════════════════════ */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background-color: #212529;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: width 0.3s ease;
            overflow: hidden;
            position: relative;
            z-index: 100;
        }

        .sidebar.collapsed {
            width: 62px;
        }

        /* Brand text hide on collapse */
        .brand-text {
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Nav labels / text hide on collapse */
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .admin-name,
        .sidebar.collapsed .logout-text {
            display: none;
        }

        .sidebar.collapsed .section-title {
            visibility: hidden;
            height: 12px;
            display: block;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
        }

        /* Nav link base */
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.15s, color 0.15s;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
        }

        /* ══════════════════════════════════════
           OVERLAY (mobile backdrop)
        ══════════════════════════════════════ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ══════════════════════════════════════
           MAIN CONTENT AREA
        ══════════════════════════════════════ */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Topbar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0 1rem;
            height: 57px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        /* Page body scroll */
        .page-body {
            padding: 1.5rem;
            overflow-y: auto;
            height: calc(100vh - 57px);
        }

        /* ══════════════════════════════════════
           RESPONSIVE — TABLET  (< 992px)
        ══════════════════════════════════════ */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 200;
                transform: translateX(-100%);
                transition: transform 0.3s ease, width 0.3s ease;
                width: 240px !important; /* always full width when open on tablet */
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            /* Always show labels when sidebar is open as drawer */
            .sidebar.mobile-open .nav-label,
            .sidebar.mobile-open .admin-name,
            .sidebar.mobile-open .logout-text,
            .sidebar.mobile-open .brand-text {
                display: inline !important;
                opacity: 1 !important;
                width: auto !important;
                overflow: visible !important;
            }

            .sidebar.mobile-open .section-title {
                visibility: visible !important;
                display: block !important;
            }

            .sidebar.mobile-open .nav-link {
                justify-content: flex-start !important;
            }

            /* Main content takes full width */
            .main-content {
                width: 100%;
            }

            /* Desktop collapse button hidden on mobile/tablet */
            #toggleBtn {
                display: none;
            }

            /* Mobile hamburger shown */
            #mobileMenuBtn {
                display: flex !important;
            }

            .page-body {
                padding: 1rem;
            }
        }

        /* ══════════════════════════════════════
           RESPONSIVE — MOBILE  (< 576px)
        ══════════════════════════════════════ */
        @media (max-width: 575.98px) {
            .topbar {
                padding: 0 0.75rem;
            }

            .topbar .navbar-brand {
                font-size: 0.95rem;
            }

            .topbar .topbar-user {
                display: none; /* hide username on very small screens */
            }

            .page-body {
                padding: 0.75rem;
            }

            /* Sidebar full-screen on small mobile */
            .sidebar {
                width: 100vw !important;
            }
        }

        /* ══════════════════════════════════════
           MOBILE MENU BTN — hidden on desktop
        ══════════════════════════════════════ */
        #mobileMenuBtn {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: #333;
            padding: 0;
            cursor: pointer;
            align-items: center;
        }
    </style>

    @yield('styles')
</head>

<body>

    {{-- ══ Backdrop overlay for mobile drawer ══ --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-wrapper">

        {{-- ══════════════════════════════════════
             SIDEBAR
        ══════════════════════════════════════ --}}
        <div class="sidebar" id="sidebar">

            {{-- ── Brand ── --}}
            <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom border-secondary">
                <span class="text-white fw-bold brand-text">
                    <i class="{{ $settings->site_icon ?? 'bi bi-whatsapp' }} text-success me-1"></i>
                    {{ $settings->site_name ?? 'WA Campaign' }}
                </span>
                {{-- Desktop collapse toggle --}}
                <button class="btn btn-sm btn-outline-secondary text-white border-0" id="toggleBtn" title="Toggle sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
            </div>

            {{-- ── User Info ── --}}
            <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom border-secondary">
                @php
                    $authUser = session('role') === 'admin' ? auth()->user() : auth()->guard('client')->user();
                    $nameParts = explode(' ', $authUser->name ?? 'A A');
                    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                @endphp

                <div class="bg-success rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:36px;height:36px;font-size:13px;">
                    {{ $initials }}
                </div>
                <div class="admin-name">
                    <div class="text-white small fw-semibold">{{ $authUser->name ?? '' }}</div>
                    <div class="text-success" style="font-size:11px;">
                        {{ session('role') === 'admin' ? 'Admin' : 'Client' }}
                    </div>
                </div>
            </div>

            {{-- ── Navigation ── --}}
            <nav class="flex-grow-1 pt-2">

                @if (session('role') === 'admin')
                    {{-- ════ ADMIN LINKS ════ --}}
                    @php
                        $clientsSectionActive =
                            request()->routeIs('clients.*') ||
                            request()->routeIs('admin.chatbot.history') ||
                            request()->routeIs('admin.chatbot.history.*') ||
                            request()->routeIs('chatbot.config.*');
                    @endphp

                    <div class="text-secondary px-3 mb-1 section-title"
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
                        class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ $clientsSectionActive ? 'active bg-primary text-white' : '' }}">
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

                @else
                    {{-- ════ CLIENT LINKS ════ --}}
                    @php
                        $clientUser = Auth::guard('client')->user();
                        $clientChatbotHistoryActive =
                            request()->routeIs('client.chatbot.index') ||
                            request()->routeIs('client.chatbot.show') ||
                            request()->routeIs('client.chatbot.download');
                    @endphp

                    <div class="text-secondary px-3 mb-1 section-title"
                        style="font-size:10px;text-transform:uppercase;letter-spacing:1px;">Main</div>

                    <a href="{{ route('client.dashboard') }}"
                        class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('client.dashboard') ? 'active bg-primary text-white' : '' }}">
                        <i class="bi bi-speedometer2 fs-5 flex-shrink-0"></i>
                        <span class="nav-label">Dashboard</span>
                    </a>

                    <hr class="border-secondary my-2 mx-2">
                    <div class="text-secondary px-3 mb-1 section-title"
                        style="font-size:10px;text-transform:uppercase;letter-spacing:1px;">Manage</div>

                    @if ($clientUser->whatsapp_enabled)
                        <a href="{{ route('client.templates.index') }}"
                            class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('client.templates.*') ? 'active bg-primary text-white' : '' }}">
                            <i class="bi bi-file-earmark-text fs-5 flex-shrink-0"></i>
                            <span class="nav-label">My Templates</span>
                        </a>

                        <a href="{{ route('client.campaigns.index') }}"
                            class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('client.campaigns.*') ? 'active bg-primary text-white' : '' }}">
                            <i class="bi bi-megaphone fs-5 flex-shrink-0"></i>
                            <span class="nav-label">My Campaigns</span>
                        </a>
                    @endif

                    @if ($clientUser->chatbot_enabled)
                        <a href="{{ route('client.chatbot.index') }}"
                            class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ $clientChatbotHistoryActive ? 'active bg-primary text-white' : '' }}">
                            <i class="bi bi-robot fs-5 flex-shrink-0"></i>
                            <span class="nav-label">Chatbot History</span>
                        </a>

                        <a href="{{ route('client.chatbot.config.index') }}"
                            class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('client.chatbot.config.*') ? 'active bg-primary text-white' : '' }}">
                            <i class="bi bi-database fs-5 flex-shrink-0"></i>
                            <span class="nav-label">Database Chatbot</span>
                        </a>
                    @endif

                    <a href="{{ route('client.settings.index') }}"
                        class="nav-link text-secondary px-3 py-2 rounded mx-1 {{ request()->routeIs('client.settings.*') ? 'active bg-primary text-white' : '' }}">
                        <i class="bi bi-gear fs-5 flex-shrink-0"></i>
                        <span class="nav-label">Settings</span>
                    </a>
                @endif

            </nav>

            {{-- ── Logout ── --}}
            <div class="border-top border-secondary p-2">
                <form method="POST" action="{{ route('wa.logout') }}">
                    @csrf
                    <button type="submit"
                        class="btn btn-sm btn-warning w-100 d-flex align-items-center justify-content-center gap-2 rounded-3 fw-medium">
                        <i class="bi bi-box-arrow-left flex-shrink-0"></i>
                        <span class="logout-text">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             MAIN CONTENT
        ══════════════════════════════════════ --}}
        <div class="main-content">

            {{-- Topbar --}}
            <div class="topbar">
                <div class="d-flex align-items-center gap-2">
                    {{-- Mobile hamburger --}}
                    <button id="mobileMenuBtn" aria-label="Open menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="navbar-brand mb-0 fw-semibold">@yield('page-title', 'Dashboard')</span>
                </div>
                <span class="text-muted small topbar-user">{{ $authUser->name ?? '' }}</span>
            </div>

            {{-- Page content --}}
            <div class="page-body">
                @yield('content')
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const BASE_URL = '{{ rtrim(url('/'), '/') }}';

        const sidebar        = document.getElementById('sidebar');
        const toggleBtn      = document.getElementById('toggleBtn');
        const mobileMenuBtn  = document.getElementById('mobileMenuBtn');
        const overlay        = document.getElementById('sidebarOverlay');

        // ── Desktop collapse (≥ 992px) ──────────────────
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        toggleBtn.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // ── Mobile drawer (< 992px) ──────────────────────
        function openMobileSidebar() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileMenuBtn.addEventListener('click', openMobileSidebar);
        overlay.addEventListener('click', closeMobileSidebar);

        // Close drawer when a nav link is tapped on mobile
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) closeMobileSidebar();
            });
        });

        // Reset state on window resize crossing 992px breakpoint
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                closeMobileSidebar();
                document.body.style.overflow = '';
            }
        });
    </script>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>