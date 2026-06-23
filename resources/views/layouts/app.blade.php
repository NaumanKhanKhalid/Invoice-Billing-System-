<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Anwar Chicken') — Anwar Chicken</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

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

        /* ── Toast notifications ── */
        #toast-container {
            position: fixed; bottom: 24px; right: 24px;
            z-index: 9999; display: flex; flex-direction: column; gap: 10px;
            pointer-events: none;
        }
        .toast {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 18px; border-radius: 12px;
            font-size: 14px; font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            pointer-events: all; min-width: 280px; max-width: 380px;
            animation: toastIn 0.3s cubic-bezier(0.34,1.56,0.64,1) forwards;
        }
        .toast.hiding { animation: toastOut 0.25s ease forwards; }
        .toast-success { background: #10b981; color: #fff; }
        .toast-error   { background: #ef4444; color: #fff; }
        .toast-warning { background: #f59e0b; color: #fff; }
        .toast-info    { background: #6366f1; color: #fff; }
        .toast-close {
            margin-left: auto; opacity: 0.7; cursor: pointer;
            background: none; border: none; color: inherit; padding: 0;
            flex-shrink: 0;
        }
        .toast-close:hover { opacity: 1; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(60px) scale(0.9); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0) scale(1); }
            to   { opacity: 0; transform: translateX(60px) scale(0.9); }
        }

        /* Prevent Alpine.js flicker before init */
        [x-cloak] { display: none !important; }

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
        <div class="px-4 py-4 border-b border-slate-700/50">
            <img src="/logo.svg" alt="Anwar Chicken Center" class="w-full h-auto" style="max-height:52px;object-fit:contain;">
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-0.5">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Dashboard
            </a>

            <a href="{{ route('udhar.index') }}" class="nav-item {{ request()->routeIs('udhar.index','udhar.create','udhar.show','udhar.payment','udhar.destroy','udhar.report') ? 'active' : '' }}">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                Udhar Book
                @php $overdueUdhar = \App\Models\CreditSale::where('status','!=','paid')->whereDate('due_date','<=',today())->count(); @endphp
                @if($overdueUdhar > 0)
                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $overdueUdhar }}</span>
                @endif
            </a>
            <a href="{{ route('udhar-customers.index') }}" class="nav-item {{ request()->routeIs('udhar-customers.*') ? 'active' : '' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                Udhar Customers
            </a>


            <a href="{{ route('purchases.index') }}"
               class="nav-item {{ request()->routeIs('purchases.index','purchases.show','purchases.edit','purchases.create') ? 'active' : '' }}">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                Purchases
            </a>


            <a href="{{ route('daily-rates.index') }}"
               class="nav-item {{ request()->routeIs('daily-rates.*') ? 'active' : '' }}">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                Daily Rates
            </a>



            <a href="{{ route('supply.index') }}" class="nav-item {{ request()->routeIs('supply.index','supply.show','supply.edit','supply.create') ? 'active' : '' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                Supply Orders
            </a>


            <a href="{{ route('day-end.index') }}" class="nav-item {{ request()->routeIs('day-end.*') ? 'active' : '' }}">
                <i data-lucide="moon" class="w-4 h-4"></i>
                Daily Records
            </a>


            <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                Daily Expenses
            </a>


            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                Reports
            </a>

            @php $systemOpen = request()->routeIs('suppliers.*','customers.*','settings.*','staff.*'); @endphp
            <div x-data="{ open: {{ $systemOpen ? 'true' : 'false' }} }">
              <button @click="open = !open"
                      class="nav-item w-full"
                      :class="open ? 'bg-white/10 text-slate-100' : ''">
                <i data-lucide="settings-2" class="w-4 h-4"></i>
                <span class="flex-1 text-left">System</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 transition-transform duration-200 opacity-50" :class="open ? 'rotate-90' : ''"></i>
              </button>
              <div x-show="open" x-cloak
                   x-transition:enter="transition ease-out duration-150"
                   x-transition:enter-start="opacity-0 scale-y-95"
                   x-transition:enter-end="opacity-100 scale-y-100"
                   class="mx-2 mt-1 mb-1 rounded-lg overflow-hidden bg-slate-900/60">
                <a href="{{ route('suppliers.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors
                          {{ request()->routeIs('suppliers.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="truck" class="w-3.5 h-3.5 flex-shrink-0"></i> Suppliers
                </a>
                <a href="{{ route('customers.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors
                          {{ request()->routeIs('customers.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="building-2" class="w-3.5 h-3.5 flex-shrink-0"></i> Hotels / Companies
                </a>
                <a href="{{ route('staff.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors
                          {{ request()->routeIs('staff.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="user-check" class="w-3.5 h-3.5 flex-shrink-0"></i> Staff & Salaries
                </a>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 text-[13px] font-medium transition-colors
                          {{ request()->routeIs('settings.*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                  <i data-lucide="settings" class="w-3.5 h-3.5 flex-shrink-0"></i> Settings
                </a>
              </div>
            </div>
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
            <span class="font-semibold text-slate-800">Anwar Chicken</span>
        </header>

        <!-- Page content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    <!-- ── Global Toast Container ── -->
    <div id="toast-container"></div>

    <!-- ── Floating Speed Dial ── -->
    <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2">
      {{-- Actions (shown when open) --}}
      <div x-show="open" x-cloak
           x-transition:enter="transition ease-out duration-150"
           x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-100"
           x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
           class="flex flex-col items-end gap-2 mb-1">
        <a href="{{ route('day-end.create') }}" class="flex items-center gap-2.5 bg-slate-800 hover:bg-slate-900 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="moon" class="w-3.5 h-3.5"></i></div>
          Close Day
        </a>
        <a href="{{ route('purchases.create') }}" class="flex items-center gap-2.5 bg-blue-600 hover:bg-blue-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i></div>
          New Purchase
        </a>
        <a href="{{ route('supply.create') }}" class="flex items-center gap-2.5 bg-green-600 hover:bg-green-700 text-white pl-3 pr-4 py-2.5 rounded-full shadow-lg text-sm font-medium transition-colors">
          <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"><i data-lucide="plus" class="w-3.5 h-3.5"></i></div>
          New Supply Order
        </a>
      </div>

      {{-- Main FAB button --}}
      <button @click="open = !open"
              class="w-14 h-14 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-xl flex items-center justify-center transition-all duration-200"
              :class="open ? 'rotate-45 bg-slate-700 hover:bg-slate-800' : ''">
        <i data-lucide="plus" class="w-6 h-6"></i>
      </button>
    </div>

    {{-- Backdrop --}}
    <div x-data x-show="false" class="fixed inset-0 z-40" style="display:none"></div>

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

        /* ── Toast System ── */
        const TOAST_ICONS = {
            success: 'check-circle-2',
            error:   'alert-circle',
            warning: 'alert-triangle',
            info:    'info',
        };

        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
                    ${getIconPath(TOAST_ICONS[type] || 'info')}
                </svg>
                <span style="flex:1">${message}</span>
                <button class="toast-close" onclick="dismissToast(this.parentElement)" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>`;
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
