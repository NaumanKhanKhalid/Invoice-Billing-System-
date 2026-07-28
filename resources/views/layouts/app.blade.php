<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $isTenantCtx = tenancy()->initialized;
        $appShopName = ($isTenantCtx && tenancy()->initialized)
            ? \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? 'My Shop')
            : config('app.name', 'Admin');
        $shopType = $isTenantCtx ? (tenant()->shop_type ?? 'general') : null;
        $isChicken = $shopType === 'chicken';
        $isProduct = in_array($shopType, ['hardware', 'mobile', 'bike', 'general', 'medical']);
        $isCoaching = $shopType === 'coaching';
        // Role gate: cashiers only see the sales counter; managers/owners see everything
        $canManage = !$isTenantCtx || (auth()->check() && auth()->user()->canManage());
    @endphp
    <title>@yield('title', $appShopName) — {{ $appShopName }}</title>
    {{-- Tenant logo (white-label) if set, else shop-type emoji tile --}}
    <link rel="icon" href="{{ shop_favicon() }}">
    <link rel="apple-touch-icon" href="{{ shop_favicon() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    @php
        // Use root-relative paths so assets load correctly on both central domain and tenant subdomains
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $cssFile  = '/build/' . $manifest['resources/css/app.css']['file'];
        $jsFile   = '/build/' . $manifest['resources/js/app.js']['file'];
    @endphp
    <link rel="stylesheet" href="{{ $cssFile }}">
    <script type="module" src="{{ $jsFile }}" defer></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

        #sidebar { background: #0f172a; transition: transform 0.25s ease, margin-left 0.28s ease; }

        /* Desktop sidebar collapse toggle (persisted). On mobile the sidebar
           already slides via .open, so this only applies from md up.
           Slides out via margin (not display:none) so it animates smoothly. */
        body { overflow-x: hidden; }
        /* When collapsed on desktop a slim top bar (with the menu button)
           appears so every page keeps a consistent header, like the POS. */
        #desktop-topbar { display: none; }
        @media (min-width: 768px) {
            html.sidebar-collapsed #sidebar { margin-left: -16rem; }
            html.sidebar-collapsed #desktop-topbar { display: flex; }
        }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: #94a3b8; font-size: 14px; font-weight: 500;
            cursor: pointer; transition: background 0.15s, color 0.15s;
            text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.07); color: #f1f5f9; }
        .nav-item.active { background: #16a34a; color: #fff; }

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

        /* ── Toast notifications (unified card style) ── */
        #toast-container {
            position: fixed; top: 20px; right: 20px;
            z-index: 9999; display: flex; flex-direction: column; gap: 10px;
            pointer-events: none; width: max-content; max-width: calc(100vw - 32px);
        }
        .toast {
            position: relative; display: flex; align-items: flex-start; gap: 12px;
            padding: 13px 15px; border-radius: 14px; overflow: hidden;
            background: #fff; border: 1px solid #e2e8f0;
            border-left: 4px solid var(--toast-accent, #16a34a);
            box-shadow: 0 16px 40px -12px rgba(15,23,42,0.28);
            pointer-events: all; min-width: 300px; max-width: 380px;
            animation: toastIn 0.28s cubic-bezier(0.34,1.4,0.64,1) both;
        }
        .toast.hiding { animation: toastOut 0.22s ease forwards; }
        .toast-icon {
            width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: var(--toast-soft, #f0fdf4); color: var(--toast-accent, #16a34a);
        }
        .toast-body { flex: 1; min-width: 0; }
        .toast-title { margin: 0; font-size: 13.5px; font-weight: 700; color: #0f172a; }
        .toast-msg   { margin: 3px 0 0 0; font-size: 12.5px; color: #64748b; line-height: 1.4; word-break: break-word; }
        .toast-close {
            background: none; border: none; cursor: pointer; padding: 2px;
            flex-shrink: 0; opacity: 0.5; color: #64748b;
        }
        .toast-close:hover { opacity: 1; }
        .toast-bar {
            position: absolute; left: 0; bottom: 0; height: 2.5px;
            width: 100%; background: var(--toast-accent, #16a34a);
            animation: toastShrink var(--toast-duration, 4000ms) linear forwards;
        }
        .toast-success { --toast-accent: #16a34a; --toast-soft: #f0fdf4; }
        .toast-error   { --toast-accent: #dc2626; --toast-soft: #fef2f2; }
        .toast-warning { --toast-accent: #d97706; --toast-soft: #fffbeb; }
        .toast-info    { --toast-accent: #2563eb; --toast-soft: #eff6ff; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(40px) scale(0.96); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0) scale(1); }
            to   { opacity: 0; transform: translateX(40px) scale(0.96); }
        }
        @keyframes toastShrink { from { width: 100%; } to { width: 0%; } }

        /* Prevent Alpine.js flicker before init */
        [x-cloak] { display: none !important; }

        @media (max-width: 768px) {
            #sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 40; }
            #overlay.show { display: block; }
        }

        /* Mobile table scroll */
        @media (max-width: 640px) {
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table-responsive table { min-width: 520px; }
        }

        /* ── Mobile responsiveness helpers ── */
        .money { overflow-wrap: anywhere; word-break: break-word; }
        @media (max-width: 640px) {
            /* Shrink large PKR figures so they don't overflow KPI cards */
            .kpi-value { font-size: 1.25rem !important; line-height: 1.75rem !important; }
            /* Collapse multi-column stat/input grids to a single column */
            .grid-stack-sm { grid-template-columns: minmax(0, 1fr) !important; }
        }
        @media (max-width: 768px) {
            /* FAB speed dial: smaller, tucked in, clear of iOS safe area */
            .fab-dial {
                bottom: calc(1rem + env(safe-area-inset-bottom, 0px)) !important;
                right: 1rem !important;
            }
            .fab-dial .fab-main { width: 3rem; height: 3rem; }
            .fab-dial .fab-main svg { width: 1.25rem; height: 1.25rem; }
            /* Full-height desktop screens flow naturally on mobile */
            .mobile-h-auto { height: auto !important; }
        }
    </style>
</head>
<body class="min-h-screen flex">
<script>
    /* Apply saved sidebar-collapsed state before paint to avoid a flash */
    try { if (localStorage.getItem('sidebarCollapsed') === '1') document.documentElement.classList.add('sidebar-collapsed'); } catch (e) {}
</script>

    <!-- Mobile overlay -->
    <div id="overlay" onclick="closeSidebar()"></div>


    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 flex-shrink-0 flex flex-col h-screen sticky top-0 overflow-y-auto">
        <!-- Logo -->
        <div class="px-4 py-4 border-b border-slate-700/50">
            @if($isTenantCtx && tenancy()->initialized)
            @php $shopLogo = \App\Models\Setting::getValue('logo_path'); @endphp
            <div class="flex items-center gap-2.5">
              @if($shopLogo)
              <img src="{{ tenant_asset($shopLogo) }}" alt="Logo"
                   class="w-9 h-9 rounded-lg object-contain bg-white p-0.5 flex-shrink-0">
              @else
              <div class="w-9 h-9 rounded-lg bg-green-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($appShopName, 0, 1)) }}
              </div>
              @endif
              <div class="overflow-hidden">
                <p class="text-sm font-bold text-white truncate">{{ $appShopName }}</p>
                <p class="text-xs text-slate-400 capitalize">{{ tenant()->shop_type ?? '' }} · {{ tenant()->plan ?? 'basic' }}</p>
              </div>
              {{-- Collapse sidebar (desktop only) --}}
              <button type="button" onclick="toggleSidebarCollapse()" title="Sidebar band karein"
                      class="hidden md:flex ml-auto w-8 h-8 rounded-lg items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700/60 transition flex-shrink-0">
                <i data-lucide="panel-left-close" class="w-5 h-5"></i>
              </button>
            </div>
            @else
            <p class="text-sm font-bold text-white">⚡ Admin Panel</p>
            @endif
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-0.5">
            @if($isTenantCtx)
            {{-- ── Tenant sidebar ── --}}

            {{-- Dashboard (all except coaching — coaching has its own below) --}}
            @if(!$isCoaching && $canManage)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>
            @endif

            {{-- ── CHICKEN SHOP ── --}}
            @if($isChicken)

            <a href="{{ route('daily-rates.index') }}" class="nav-item {{ request()->routeIs('daily-rates.*') ? 'active' : '' }}">
                <i data-lucide="trending-up" class="w-4 h-4"></i> Daily Rates
            </a>

            <a href="{{ route('purchases.index') }}" class="nav-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i data-lucide="package-search" class="w-4 h-4"></i> Purchases
            </a>

            <a href="{{ route('supply.index') }}" class="nav-item {{ request()->routeIs('supply.*') ? 'active' : '' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i> Supply Orders
            </a>

            <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i data-lucide="building-2" class="w-4 h-4"></i> Hotels / Companies
            </a>

            @endif

            {{-- ── PRODUCT SHOPS (hardware / mobile / bike / general) ── --}}
            @if($isProduct)

            <a href="{{ route('pos.create') }}" class="nav-item {{ request()->routeIs('pos.create','pos.store','pos.show','pos.receipt') ? 'active' : '' }}">
                <i data-lucide="scan-line" class="w-4 h-4"></i> POS Counter
            </a>

            <a href="{{ route('pos.index') }}" class="nav-item {{ request()->routeIs('pos.index') ? 'active' : '' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i> Sales History
            </a>

            @if($canManage)
            <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i data-lucide="package" class="w-4 h-4"></i> Products & Stock
            </a>

            <a href="{{ route('product-purchases.index') }}" class="nav-item {{ request()->routeIs('product-purchases.*') ? 'active' : '' }}">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Purchases
            </a>
            @endif

            @if(feature_enabled('quotations'))
            <a href="{{ route('quotations.index') }}" class="nav-item {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i> Quotations
            </a>
            @endif

            @if(feature_enabled('repairs'))
            <a href="{{ route('repairs.index') }}" class="nav-item {{ request()->routeIs('repairs.*') ? 'active' : '' }}">
                <i data-lucide="wrench" class="w-4 h-4"></i> Repairing
            </a>
            @endif

            @endif

            {{-- ── COMMON (all shop types except coaching) ── --}}
            @if(!$isCoaching && feature_enabled('udhar_book'))
            @php $overdueUdhar = \App\Models\CreditSale::where('status','!=','paid')->whereDate('due_date','<=',today())->count(); @endphp
            <a href="{{ route('udhar.index') }}" class="nav-item {{ request()->routeIs('udhar.*') ? 'active' : '' }}">
                <i data-lucide="book-open" class="w-4 h-4"></i> Udhar Book
                @if($overdueUdhar > 0)
                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $overdueUdhar }}</span>
                @endif
            </a>
            @endif

            @if(!$isCoaching && feature_enabled('expenses') && $canManage)
            <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i data-lucide="wallet" class="w-4 h-4"></i> Expenses
            </a>
            @endif

            {{-- ── COACHING CENTER ── --}}
            @if($isCoaching)
            <a href="{{ route('coaching.dashboard') }}" class="nav-item {{ request()->routeIs('coaching.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>
            <a href="{{ route('coaching.students.index') }}" class="nav-item {{ request()->routeIs('coaching.students.*') ? 'active' : '' }}">
                <i data-lucide="users" class="w-4 h-4"></i> Students
            </a>
            <a href="{{ route('coaching.fees.index') }}" class="nav-item {{ request()->routeIs('coaching.fees.*') ? 'active' : '' }}">
                <i data-lucide="banknote" class="w-4 h-4"></i> Fee Collection
            </a>
            <a href="{{ route('coaching.courses.index') }}" class="nav-item {{ request()->routeIs('coaching.courses.*') ? 'active' : '' }}">
                <i data-lucide="book-open" class="w-4 h-4"></i> Courses & Batches
            </a>
            @if(feature_enabled('expenses'))
            <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i data-lucide="wallet" class="w-4 h-4"></i> Expenses
            </a>
            @endif
            @endif

            @if($canManage)
            @if($isChicken && feature_enabled('day_closing'))
            <a href="{{ route('day-end.index') }}" class="nav-item {{ request()->routeIs('day-end.*') ? 'active' : '' }}">
                <i data-lucide="moon" class="w-4 h-4"></i> Daily Records
            </a>
            @elseif(!$isCoaching && feature_enabled('day_closing'))
            <a href="{{ route('day-summary.index') }}" class="nav-item {{ request()->routeIs('day-summary.*') ? 'active' : '' }}">
                <i data-lucide="moon" class="w-4 h-4"></i> Day Closing
            </a>
            @endif

            @if(!$isCoaching && feature_enabled('reports'))
            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Reports
            </a>
            @endif
            @endif

            {{-- System dropdown (managers/owners only) --}}
            @if($canManage)
            @php $systemOpen = request()->routeIs('suppliers.*','staff.*','tenant.users.*','udhar-customers.*','settings.*'); @endphp
            <div x-data="{ open: {{ $systemOpen ? 'true' : 'false' }} }">
              <button @click="open = !open" class="nav-item w-full" :class="open ? 'bg-white/10 text-slate-100' : ''">
                <i data-lucide="settings-2" class="w-4 h-4"></i>
                <span class="flex-1 text-left">System</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 transition-transform duration-200 opacity-50" :class="open ? 'rotate-90' : ''"></i>
              </button>
              <div x-show="open" x-cloak
                   x-transition:enter="transition ease-out duration-150"
                   x-transition:enter-start="opacity-0 scale-y-95"
                   x-transition:enter-end="opacity-100 scale-y-100"
                   class="mx-2 mt-1 mb-1 rounded-lg overflow-hidden bg-slate-900/60">
                @if(!$isCoaching)
                <a href="{{ route('suppliers.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="truck" class="w-3.5 h-3.5 flex-shrink-0"></i> Suppliers
                </a>
                <a href="{{ route('udhar-customers.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors {{ request()->routeIs('udhar-customers.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="user-check" class="w-3.5 h-3.5 flex-shrink-0"></i> Udhar Customers
                </a>
                @endif
                @if(feature_enabled('staff_module'))
                <a href="{{ route('staff.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors {{ request()->routeIs('staff.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="hard-hat" class="w-3.5 h-3.5 flex-shrink-0"></i> Staff & Salaries
                </a>
                @endif
                @if(in_array(auth()->user()->role ?? '', ['owner', 'admin']))
                @if(feature_enabled('audit_log'))
                <a href="{{ route('activity-log.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors {{ request()->routeIs('activity-log.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="history" class="w-3.5 h-3.5 flex-shrink-0"></i> Activity Log
                </a>
                @endif
                <a href="{{ route('tenant.users.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors {{ request()->routeIs('tenant.users.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="users" class="w-3.5 h-3.5 flex-shrink-0"></i> Team Members
                </a>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors {{ request()->routeIs('settings.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="settings" class="w-3.5 h-3.5 flex-shrink-0"></i> Settings
                </a>
                @endif
              </div>
            </div>
            @endif
            @else
            {{-- ── Admin / Central domain sidebar ── --}}
            <a href="{{ route('admin.tenants.index') }}" class="nav-item {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                <i data-lucide="store" class="w-4 h-4"></i> Tenants
            </a>
            <a href="{{ route('admin.plans') }}" class="nav-item {{ request()->routeIs('admin.plans') ? 'active' : '' }}">
                <i data-lucide="credit-card" class="w-4 h-4"></i> Plans & Revenue
            </a>
            <a href="{{ route('admin.logs') }}" class="nav-item {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                <i data-lucide="file-warning" class="w-4 h-4"></i> Error Logs
            </a>
            @endif
        </nav>

        <!-- User section -->
        <div class="border-t border-slate-700/50 px-3 py-4">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-slate-200 text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-slate-400 text-xs truncate capitalize">{{ auth()->user()->role }}</p>
                </div>
                <form method="POST" action="{{ $isTenantCtx ? route('logout') : route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors" title="Logout">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div id="app-shell" class="flex-1 flex flex-col min-h-screen min-w-0">
        <!-- Top bar (mobile) -->
        <header class="md:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-3">
            <button onclick="openSidebar()" class="text-slate-600">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <span class="font-semibold text-slate-800">{{ $appShopName }}</span>
        </header>

        <!-- Top bar (desktop, shown only when sidebar collapsed) -->
        <header id="desktop-topbar" class="bg-white border-b border-slate-200 px-4 py-2.5 items-center gap-3">
            <button type="button" onclick="toggleSidebarCollapse()" title="Menu kholein"
                    class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition shrink-0">
                <i data-lucide="panel-left-open" class="w-4 h-4"></i>
            </button>
            <span class="font-semibold text-slate-700 text-sm">{{ $appShopName }}</span>
        </header>

        <!-- Subscription expiry warning banner -->
        @php
          $tenantExpiry = null;
          if ($isTenantCtx && tenancy()->initialized) {
              $t = tenant();
              if ($t->plan_expires_at) {
                  $daysLeft = now()->diffInDays($t->plan_expires_at, false);
                  if ($daysLeft <= 7 && $daysLeft >= 0) $tenantExpiry = $daysLeft;
              }
          }
        @endphp
        {{-- Impersonation banner (inline styles so it can't be purged from the build) --}}
        @if(session('impersonating'))
        <div style="background:#7c3aed;color:#fff;font-size:0.875rem;font-weight:500;padding:0.5rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:0.75rem;">
          <div style="display:flex;align-items:center;gap:0.5rem;">
            <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span>Impersonating as <strong>{{ session('impersonate_as') }}</strong> — logged in by <strong>{{ session('admin_name') }}</strong></span>
          </div>
          <form method="POST" action="{{ route('impersonate.stop') }}">
            @csrf
            <button type="submit" style="display:inline-flex;align-items:center;gap:0.375rem;background:rgba(255,255,255,0.2);color:#fff;padding:0.25rem 0.75rem;border-radius:0.5rem;font-size:0.75rem;font-weight:600;border:0;cursor:pointer;"
                    onmouseover="this.style.background='rgba(255,255,255,0.32)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
              <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
              Exit — Back to Admin
            </button>
          </form>
        </div>
        @endif

        @if($tenantExpiry !== null)
        <div class="bg-amber-500 text-white text-sm font-medium px-4 py-2 flex items-center justify-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          @if($tenantExpiry == 0)
            Your subscription expires <strong>today</strong>! Contact support to renew.
          @else
            Your subscription expires in <strong>{{ $tenantExpiry }} day{{ $tenantExpiry > 1 ? 's' : '' }}</strong>. Contact support to renew.
          @endif
        </div>
        @endif

        <!-- Page content -->
        <main id="main-content" class="flex-1 p-3 sm:p-6">
            @yield('content')
        </main>
    </div>

    <!-- ── Global Toast Container ── -->
    <div id="toast-container"></div>

    @if($isTenantCtx)
    <!-- ── Floating Speed Dial (tenant only) ── -->
    <div x-data="{ open: false }" class="fab-dial fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2">
      <div x-show="open" x-cloak
           x-transition:enter="transition ease-out duration-150"
           x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-100"
           x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
           class="flex flex-col items-end gap-2 mb-1">

        @if($isChicken)
        <a href="{{ route('day-end.create') }}" class="flex items-center gap-2.5 bg-slate-800 hover:bg-slate-900 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="moon" class="w-3.5 h-3.5"></i></div>
          Close Day
        </a>
        @elseif(!$isCoaching)
        <a href="{{ route('day-summary.create') }}" class="flex items-center gap-2.5 bg-slate-800 hover:bg-slate-900 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="moon" class="w-3.5 h-3.5"></i></div>
          Close Day
        </a>
        @endif

        @if($isCoaching)
        <a href="{{ route('coaching.students.create') }}" class="flex items-center gap-2.5 bg-green-600 hover:bg-green-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="user-plus" class="w-3.5 h-3.5"></i></div>
          Enroll Student
        </a>
        <a href="{{ route('coaching.fees.index') }}" class="flex items-center gap-2.5 bg-blue-600 hover:bg-blue-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="banknote" class="w-3.5 h-3.5"></i></div>
          Collect Fees
        </a>
        @endif

        @if($isChicken)
        <a href="{{ route('purchases.create') }}" class="flex items-center gap-2.5 bg-blue-600 hover:bg-blue-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="package-search" class="w-3.5 h-3.5"></i></div>
          New Purchase
        </a>
        <a href="{{ route('supply.create') }}" class="flex items-center gap-2.5 bg-green-600 hover:bg-green-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="plus" class="w-3.5 h-3.5"></i></div>
          New Supply Order
        </a>
        @endif

        @if($isProduct)
        <a href="{{ route('pos.create') }}" class="flex items-center gap-2.5 bg-green-600 hover:bg-green-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="scan-line" class="w-3.5 h-3.5"></i></div>
          New Sale (POS)
        </a>
        <a href="{{ route('product-purchases.create') }}" class="flex items-center gap-2.5 bg-blue-600 hover:bg-blue-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i></div>
          New Purchase
        </a>
        @endif

        <a href="{{ route('expenses.create') }}" class="flex items-center gap-2.5 bg-amber-600 hover:bg-amber-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="wallet" class="w-3.5 h-3.5"></i></div>
          Add Expense
        </a>
      </div>

      <button @click="open = !open"
              class="fab-main w-14 h-14 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-xl flex items-center justify-center transition-all duration-200"
              :class="open ? 'rotate-45 bg-slate-700 hover:bg-slate-800' : ''">
        <i data-lucide="plus" class="w-6 h-6"></i>
      </button>
    </div>
    @endif

    {{-- Backdrop --}}
    <div x-data x-show="false" class="fixed inset-0 z-40" style="display:none"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) lucide.createIcons({ icons: lucide.icons });
        });

        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('overlay').classList.add('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        }

        /* Desktop sidebar collapse — state saved so it sticks across pages */
        function toggleSidebarCollapse() {
            const collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {}
        }

        /* ── Toast System ── */
        const TOAST_ICONS = {
            success: 'check-circle-2',
            error:   'alert-circle',
            warning: 'alert-triangle',
            info:    'info',
        };

        const TOAST_TITLES = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Notice' };

        function showToast(message, type = 'success', duration = 4000, title = null) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.setProperty('--toast-duration', duration + 'ms');
            const esc = (s) => String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
            const heading = title || TOAST_TITLES[type] || 'Notice';
            toast.innerHTML = `
                <div class="toast-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.2"
                         stroke-linecap="round" stroke-linejoin="round">
                        ${getIconPath(TOAST_ICONS[type] || 'info')}
                    </svg>
                </div>
                <div class="toast-body">
                    <p class="toast-title">${esc(heading)}</p>
                    <p class="toast-msg">${esc(message)}</p>
                </div>
                <button class="toast-close" onclick="dismissToast(this.closest('.toast'))" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                ${duration > 0 ? '<div class="toast-bar"></div>' : ''}`;
            container.appendChild(toast);
            if (duration > 0) setTimeout(() => dismissToast(toast), duration);
        }

        function dismissToast(toast) {
            if (!toast || toast.classList.contains('hiding')) return;
            toast.classList.add('hiding');
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
        }

        function getIconPath(name) {
            const paths = {
                'check-circle-2': '<circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>',
                'alert-circle':   '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
                'alert-triangle': '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
                'info':           '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
            };
            return paths[name] || paths['info'];
        }

        /* ── Auto-fire toasts from Laravel session flash ── */
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', () =>
                showToast(@json(session('success')), 'success'));
        @endif
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', () =>
                showToast(@json(session('error')), 'error'));
        @endif
        @if(session('warning'))
            document.addEventListener('DOMContentLoaded', () =>
                showToast(@json(session('warning')), 'warning'));
        @endif
        @if(session('info'))
            document.addEventListener('DOMContentLoaded', () =>
                showToast(@json(session('info')), 'info'));
        @endif
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () =>
                showToast(@json($errors->first()), 'error'));
        @endif
    </script>

    @stack('scripts')

    {{-- Global Confirm Modal --}}
    <div id="confirmModal" x-cloak
         style="position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,0.5);backdrop-filter:blur(2px)"
         x-data="confirmModal()" x-show="open"
         @confirm-open.window="show($event.detail)">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <div class="flex items-start gap-4 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 text-base" x-text="title"></h3>
              <p class="text-sm text-slate-500 mt-1" x-text="message"></p>
            </div>
          </div>
          <div class="flex gap-3 justify-end">
            <button @click="cancel()" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium transition-colors">Cancel</button>
            <button @click="confirm()" class="px-4 py-2 rounded-lg text-white text-sm font-semibold transition-colors"
                    :class="danger ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                    x-text="confirmText"></button>
          </div>
        </div>
      </div>
    </div>

    <script>
    function confirmModal() {
        return {
            open: false,
            title: '',
            message: '',
            confirmText: 'Yes, Delete',
            danger: true,
            _resolve: null,
            show(detail) {
                this.title = detail.title || 'Are you sure?';
                this.message = detail.message || '';
                this.confirmText = detail.confirmText || 'Yes, Delete';
                this.danger = detail.danger !== false;
                this.open = true;
                this._resolve = detail.resolve;
            },
            confirm() {
                this.open = false;
                if (this._resolve) this._resolve(true);
            },
            cancel() {
                this.open = false;
                if (this._resolve) this._resolve(false);
            }
        }
    }

    function askConfirm(detail) {
        return new Promise(resolve => {
            window.dispatchEvent(new CustomEvent('confirm-open', { detail: { ...detail, resolve } }));
        });
    }

    // Replace all onsubmit confirm forms
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-confirm-title]').forEach(form => {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const ok = await askConfirm({
                    title: form.dataset.confirmTitle,
                    message: form.dataset.confirmMessage || '',
                    confirmText: form.dataset.confirmText || 'Yes, Delete',
                    danger: form.dataset.confirmDanger !== 'false',
                });
                if (ok) form.submit();
            });
        });
    });
    </script>

    {{-- Hidden iframe for in-page printing --}}
    <iframe id="print-frame" style="position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;border:none;" tabindex="-1"></iframe>
    <script>
    function printInvoice(url) {
        const frame = document.getElementById('print-frame');
        frame.onload = function () {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        };
        frame.src = url;
    }
    </script>
</body>
</html>
