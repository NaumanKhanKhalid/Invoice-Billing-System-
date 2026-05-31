<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'InvoicePro') — InvoicePro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

        #sidebar { background: #0f172a; transition: transform 0.25s ease; }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: #94a3b8; font-size: 14px; font-weight: 500;
            cursor: pointer; transition: background 0.15s, color 0.15s;
            text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.07); color: #f1f5f9; }
        .nav-item.active { background: #6366f1; color: #fff; }

        .nav-section {
            font-size: 10px; font-weight: 600; letter-spacing: 0.08em;
            color: #475569; text-transform: uppercase;
            padding: 16px 12px 6px;
        }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-red { background: #fee2e2; color: #dc2626; }
        .badge-yellow { background: #fef9c3; color: #ca8a04; }
        .badge-blue { background: #dbeafe; color: #2563eb; }
        .badge-gray { background: #f1f5f9; color: #64748b; }
        .badge-slate { background: #f1f5f9; color: #475569; }
        .badge-purple { background: #ede9fe; color: #7c3aed; }

        @media (max-width: 768px) {
            #sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 40; }
            #overlay.show { display: block; }
        }
    </style>
</head>
<body class="min-h-screen flex">

    <!-- Mobile overlay -->
    <div id="overlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 flex-shrink-0 flex flex-col h-screen sticky top-0 overflow-y-auto">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-700/50">
            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <i data-lucide="zap" class="w-4 h-4 text-white"></i>
            </div>
            <span class="text-white font-bold text-lg">InvoicePro</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-0.5">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Dashboard
            </a>

            <div class="nav-section">Management</div>

            <a href="{{ route('clients.index') }}"
               class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                Clients
            </a>

            <a href="{{ route('products.index') }}"
               class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i data-lucide="package" class="w-4 h-4"></i>
                Products & Services
            </a>

            <div class="nav-section">Billing</div>

            <a href="{{ route('invoices.index') }}"
               class="nav-item {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.show') ? 'active' : '' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                Invoices
            </a>

            <a href="{{ route('invoices.create') }}"
               class="nav-item {{ request()->routeIs('invoices.create') ? 'active' : '' }}">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                New Invoice
            </a>

            <div class="nav-section">Analytics</div>

            <a href="{{ route('reports.index') }}"
               class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                Reports
            </a>

            <div class="nav-section">System</div>

            <a href="{{ route('settings.index') }}"
               class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i data-lucide="settings" class="w-4 h-4"></i>
                Settings
            </a>
        </nav>

        <!-- User section -->
        <div class="border-t border-slate-700/50 px-3 py-4">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-slate-200 text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-slate-400 text-xs truncate capitalize">{{ auth()->user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors" title="Logout">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-h-screen min-w-0">
        <!-- Top bar (mobile) -->
        <header class="md:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-3">
            <button onclick="openSidebar()" class="text-slate-600">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <span class="font-semibold text-slate-800">InvoicePro</span>
        </header>

        <!-- Page content -->
        <main class="flex-1 p-6">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
                    <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();

        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('overlay').classList.add('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        }
    </script>

    @stack('scripts')
</body>
</html>
