@extends('layouts.app')
@section('title','POS Counter')
@section('content')
<script>
window.__POS_PRODUCTS__ = @json($products);
window.__POS_HOLDS__ = @json($heldSales ?? []);
window.__POS_CUSTOMERS__ = @json($customers ?? []);
window.__POS_RECENT__ = @json($recentSales ?? []);
</script>

<style>
  /* Lock the POS to the viewport height so the products grid and cart items
     scroll internally while the checkout stays pinned (page itself never scrolls). */
  #app-shell { height: 100vh; min-height: 0 !important; overflow: hidden; }
  #main-content { padding: 0 !important; display: flex; flex-direction: column; overflow: hidden; flex: 1; min-height: 0; }
  /* Cart panel layout is defined here (not via md: Tailwind utilities) so it
     works even on an older/purged asset build. */
  .pos-cart {
    width: 20rem; flex-shrink: 0; border-left: 1px solid #e2e8f0;
    position: static; transform: none;
  }
  @media (min-width: 1280px) { .pos-cart { width: 24rem; } }
  @media (max-width: 767px) {
    .pos-cart {
      position: fixed; inset: 0; z-index: 40; width: 100%;
      border-left: 0; transform: translateY(100%);
    }
    .pos-cart.open { transform: translateY(0); }
  }
  /* On POS the sidebar toggle lives inside the POS top bar, so hide the
     global desktop top bar here and show the in-bar menu button instead. */
  #desktop-topbar { display: none !important; }
  .pos-menu-btn { display: none; }
  html.sidebar-collapsed .pos-menu-btn { display: flex; }
  /* Hide the global quick-add FAB on POS — it overlaps the cart checkout. */
  .fab-dial { display: none !important; }
  /* Horizontal category strip without a visible scrollbar */
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  /* Give the cart a touch less width on small laptops so products breathe */
  @media (min-width: 768px) and (max-width: 1279px) { .pos-cart { width: 18rem; } }
  @keyframes salePop { from { opacity:0; transform:scale(.4); } to { opacity:1; transform:scale(1); } }
</style>

<div class="flex flex-col flex-1 min-h-0 bg-slate-100" style="height:100%" x-data="posApp()" x-init="init()">

  {{-- ══ Offline queue banner ══ --}}
  <div x-show="offlineQueue.length > 0" x-cloak
       class="px-4 py-2.5 flex items-center gap-3 shrink-0 border-b border-amber-200"
       style="background:linear-gradient(90deg,#fffbeb 0%,#fef3c7 100%)">
    <span class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center shrink-0">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 16.5a5 5 0 0 1 0-9 6.5 6.5 0 0 1 12.4 1.5A4.5 4.5 0 0 1 19 17"/><line x1="2" x2="22" y1="2" y2="22"/><path d="M12 12v9"/></svg>
    </span>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-bold text-amber-900 leading-tight">
        <span x-text="offlineQueue.length"></span>
        <span x-text="offlineQueue.length === 1 ? 'sale offline saved' : 'sales offline saved'"></span>
      </p>
      <p class="text-[11px] text-amber-700/80 leading-tight mt-0.5"
         x-text="syncing ? 'Sync ho rahi hai…' : 'Net aate hi khud sync ho jayengi'"></p>
    </div>
    <button type="button" @click="syncQueue()" :disabled="syncing"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white text-xs font-bold rounded-lg transition shrink-0 shadow-sm">
      <svg :class="syncing ? 'animate-spin' : ''" style="width:13px;height:13px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><polyline points="21 3 21 9 15 9"/></svg>
      <span x-text="syncing ? 'Syncing…' : 'Sync Now'"></span>
    </button>
  </div>

  <div class="flex flex-1 min-h-0 overflow-hidden">

  {{-- ══ LEFT: Products ══ --}}
  <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

    {{-- Top bar --}}
    <div class="bg-white border-b border-slate-200 px-4 py-2.5 flex items-center gap-3 shrink-0">
      <button type="button" onclick="toggleSidebarCollapse()" title="Menu kholein"
              class="pos-menu-btn w-9 h-9 rounded-lg border border-slate-200 items-center justify-center text-slate-500 hover:bg-slate-50 shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="m14 9 3 3-3 3"/></svg>
      </button>

      <div class="relative flex-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" x-ref="mainInput" x-model="searchQ"
               @keydown.enter.prevent="tryBarcodeEnter()"
               placeholder="{{ __('pos.search_products') }}"
               class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-300 focus:bg-white outline-none transition"
               autofocus>
      </div>

      @if(feature_enabled('barcode_scanner'))
      <button type="button" @click="openCamera()"
              class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="10" height="10" rx="1"/></svg>
        Scan
      </button>
      @endif

      {{-- Fullscreen --}}
      <button type="button" @click="toggleFullscreen()" title="Full screen"
              class="w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 flex items-center justify-center shrink-0 transition">
        <svg x-show="!isFullscreen" style="width:16px;height:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
        <svg x-show="isFullscreen" x-cloak style="width:16px;height:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
      </button>

      {{-- Calculator --}}
      <button type="button" @click="calcOpen = !calcOpen" title="Calculator"
              class="w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 flex items-center justify-center shrink-0 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="8" x2="8" y1="14" y2="14"/><line x1="12" x2="12" y1="14" y2="14"/><line x1="16" x2="16" y1="14" y2="14"/><line x1="8" x2="8" y1="18" y2="18"/><line x1="12" x2="12" y1="18" y2="18"/><line x1="16" x2="16" y1="18" y2="18"/></svg>
      </button>

      {{-- Retail | Wholesale price toggle --}}
      <div class="flex items-center rounded-xl border border-slate-200 overflow-hidden shrink-0 text-xs font-semibold">
        <button type="button" @click="priceMode = 'retail'"
                :class="priceMode === 'retail' ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 hover:bg-slate-50'"
                class="px-3 py-2 transition">{{ __('pos.retail') }}</button>
        <button type="button" @click="priceMode = 'wholesale'"
                :class="priceMode === 'wholesale' ? 'bg-amber-500 text-white' : 'bg-white text-slate-500 hover:bg-slate-50'"
                class="px-3 py-2 transition">{{ __('pos.wholesale') }}</button>
      </div>

      @if(feature_enabled('open_tabs'))
      {{-- Held sales pill --}}
      <button type="button" @click="holdsPanelOpen = true"
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-semibold transition shrink-0"
              :class="heldSales.length > 0 ? 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="10" x2="10" y1="9" y2="15"/><line x1="14" x2="14" y1="9" y2="15"/></svg>
        <span x-text="'Held (' + heldSales.length + ')'"></span>
      </button>
      @endif

      <button type="button" @click="recentPanelOpen = true"
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 text-xs font-semibold transition shrink-0 whitespace-nowrap">
        <svg style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>
        <span x-text="'Sales (' + recentSales.length + ')'"></span>
      </button>
    </div>


    {{-- Category tabs --}}
    <div class="px-4 pt-3 pb-0 flex gap-2 flex-nowrap overflow-x-auto no-scrollbar shrink-0" x-show="!searchQ">
      <button @click="activeCategory = null" x-show="categories.length > 0"
              :class="activeCategory === null ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 hover:bg-slate-50'"
              class="px-3 py-1 rounded-lg text-xs font-semibold border border-slate-200 transition whitespace-nowrap shrink-0">All</button>
      <template x-for="cat in categories" :key="cat">
        <button @click="activeCategory = (activeCategory === cat ? null : cat)"
                :class="activeCategory === cat ? 'bg-green-600 text-white border-green-600' : 'bg-white text-slate-600 hover:bg-slate-50'"
                class="px-3 py-1 rounded-lg text-xs font-semibold border border-slate-200 transition whitespace-nowrap shrink-0"
                x-text="cat"></button>
      </template>
      {{-- Out-of-stock visibility toggle --}}
      <button @click="hideOutOfStock = !hideOutOfStock"
              class="ml-auto shrink-0 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold border transition whitespace-nowrap"
              :class="hideOutOfStock ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'">
        <svg x-show="!hideOutOfStock" style="width:13px;height:13px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg x-show="hideOutOfStock" x-cloak style="width:13px;height:13px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
        <span x-text="hideOutOfStock ? '{{ __('pos.out_of_stock_hide') }}' : '{{ __('pos.out_of_stock') }}'"></span>
      </button>
    </div>

    {{-- Product grid --}}
    <div class="flex-1 overflow-y-auto p-4 pb-24 md:pb-4 flex flex-col gap-4">
      <div class="grid gap-2.5" style="grid-template-columns:repeat(auto-fill,minmax(132px,1fr))">
        <template x-for="entry in displayList" :key="entry.id">
          <button type="button"
                  @click="entry.isGroup ? openVariantPicker(entry) : addToCart(entry.product)"
                  :disabled="cardStock(entry) <= 0"
                  class="group relative flex flex-col text-left rounded-xl border bg-white transition-all duration-150 overflow-hidden"
                  :class="cardStock(entry) <= 0
                    ? 'border-slate-100 opacity-60 cursor-not-allowed'
                    : 'border-slate-200 hover:border-green-400 hover:shadow-md cursor-pointer active:scale-[0.98]'">

            {{-- Image / placeholder (fixed height so every card is uniform) --}}
            <div class="relative bg-slate-50 flex items-center justify-center overflow-hidden" style="height:5.5rem">
              <template x-if="cardImg(entry)">
                <img :src="cardImg(entry)" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
              </template>
              <template x-if="!cardImg(entry)">
                <div class="absolute inset-0 flex items-center justify-center" :style="'background:' + cardTint(entry)[0]">
                  <span class="text-xl font-extrabold tracking-tight" :style="'color:' + cardTint(entry)[1]" x-text="cardInitials(entry)"></span>
                </div>
              </template>
              {{-- stock badge (top-left) — solid bg so it stays readable over product photos --}}
              <span class="absolute top-2 left-2 text-[9px] font-bold px-1 py-[2px] rounded tabular-nums leading-none shadow-sm"
                    :class="cardStock(entry) <= 0 ? 'bg-red-500 text-white' : (cardStock(entry) <= 5 ? 'bg-amber-400 text-amber-950' : 'bg-white/90 text-slate-600')"
                    x-text="cardStockLabel(entry)"></span>
              {{-- price badge (top-right) --}}
              <span class="absolute top-2 right-2 text-[9px] font-bold text-green-700 bg-white/90 px-1 py-[2px] rounded tabular-nums leading-none shadow-sm" x-text="cardPrice(entry)"></span>
              {{-- variant count chip (bottom-left) --}}
              <template x-if="entry.isGroup">
                <span class="absolute bottom-1.5 left-1.5 text-[9px] font-semibold px-1.5 py-[3px] rounded-md bg-slate-900/80 text-white leading-none" x-text="entry.variants.length + ' options'"></span>
              </template>
              <span class="absolute inset-0 bg-green-600/0 group-hover:bg-green-600/5 transition"></span>
            </div>

            {{-- Info (name only) --}}
            <div class="px-2 py-1.5">
              <p class="text-[11px] font-medium text-slate-700 leading-snug line-clamp-2" style="min-height:1.8rem" x-text="entry.isGroup ? entry.group : entry.product.name"></p>
            </div>
          </button>
        </template>

        <template x-if="displayList.length === 0">
          <div class="flex flex-col items-center justify-center py-16 text-center" style="grid-column:1/-1">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <p class="text-slate-400 text-sm font-medium">{{ __('pos.no_products') }}</p>
          </div>
        </template>
      </div>

    </div>

    {{-- Bottom bar: today stats (left) + actions (right) — products width only --}}
    <div class="hidden sm:flex shrink-0 items-center gap-3 px-4 py-2 bg-white border-t border-slate-200">
      <div class="flex items-center gap-4 text-xs min-w-0 overflow-hidden">
        <span class="flex items-center gap-1.5 text-slate-500 whitespace-nowrap">
          <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
          Sales <span class="font-bold text-slate-800 tabular-nums">{{ $todaySales }}</span>
        </span>
        <span class="flex items-center gap-1.5 text-slate-500 whitespace-nowrap">
          <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
          Revenue <span class="font-bold text-slate-800 tabular-nums">PKR {{ number_format($todayRevenue) }}</span>
        </span>
        <span class="flex items-center gap-1.5 whitespace-nowrap {{ $lowStock > 0 ? 'text-amber-600' : 'text-slate-500' }}">
          <span class="w-1.5 h-1.5 rounded-full {{ $lowStock > 0 ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
          Low stock <span class="font-bold tabular-nums">{{ $lowStock }}</span>
        </span>
      </div>

      <div class="flex items-center gap-1.5 ml-auto shrink-0">
        @if(feature_enabled('open_tabs'))
        <button type="button" @click="openHoldModal()" :disabled="cart.length === 0"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 hover:border-blue-300 hover:text-blue-700 hover:bg-blue-50 disabled:opacity-40 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="10" x2="10" y1="9" y2="15"/><line x1="14" x2="14" y1="9" y2="15"/></svg>
          Hold
        </button>
        @endif
        <button type="button" @click="clearCart()" :disabled="cart.length === 0"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 hover:border-red-300 hover:text-red-600 hover:bg-red-50 disabled:opacity-40 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
          Clear
        </button>
        <a href="{{ route('pos.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 hover:border-slate-300 hover:text-slate-800 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>
          Orders
        </a>
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 hover:border-slate-300 hover:text-slate-800 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
          Products
        </a>
      </div>
    </div>

  </div>

  {{-- ══ RIGHT: Cart + Checkout ══ --}}
  {{-- Mobile: full-screen slide-up panel; Desktop (md+): static sidebar --}}
  {{-- Compiled Tailwind lacks translate-y-full/md:translate-y-0 variants, so slide is driven by custom .pos-cart CSS below --}}
  <div class="pos-cart flex flex-col bg-white overflow-hidden transition-transform duration-200"
       :class="mobileCartOpen ? 'open' : ''">

    {{-- Cart header --}}
    <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-white">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
        <span class="text-slate-900 font-bold text-sm">{{ __('pos.cart') }}</span>
        <span x-show="cart.length > 0"
              class="bg-green-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center"
              x-text="cart.length"></span>
        <span x-show="holdId" x-cloak
              class="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full"
              x-text="holdTabNumber"></span>
      </div>
      <button type="button" @click="mobileCartOpen = false"
              class="md:hidden w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">✕</button>
    </div>


    {{-- Cart items (scrollable, compact list) --}}
    <div class="overflow-y-auto bg-white" style="flex:1 1 0; min-height:0;">
      <template x-if="cart.length === 0">
        <div class="flex flex-col items-center justify-center h-full py-10 text-center">
          <div class="w-14 h-14 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          </div>
          <p class="text-slate-500 text-sm font-medium">{{ __('pos.cart_empty') }}</p>
          <p class="text-slate-400 text-xs mt-1">{{ __('pos.click_product') }}</p>
        </div>
      </template>

      <template x-for="(item, idx) in cart" :key="item.id">
        <div class="flex items-center gap-2 px-3 py-2 border-b border-slate-100 hover:bg-slate-50/60 transition">
          <button type="button" @click="removeFromCart(item.id)" title="Hatayein"
                  class="text-slate-300 hover:text-red-500 shrink-0 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
          </button>
          <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold text-slate-800 leading-tight truncate" x-text="item.name"></p>
            <p class="text-[11px] text-slate-400" x-text="'PKR ' + Number(item.price).toLocaleString() + ' / ' + item.unit"></p>
          </div>
          <div class="flex items-center gap-0.5 shrink-0 bg-slate-100 rounded-full p-0.5">
            <button type="button" @click="item.qty > 1 ? item.qty-- : removeFromCart(item.id)"
                    class="w-6 h-6 rounded-full bg-white hover:bg-slate-50 text-slate-600 shadow-sm font-bold flex items-center justify-center transition text-sm leading-none">−</button>
            <span class="text-slate-900 font-bold text-[13px] w-6 text-center tabular-nums" x-text="item.qty"></span>
            <button type="button" @click="item.qty < item.stock ? item.qty++ : null"
                    :disabled="item.qty >= item.stock"
                    class="w-6 h-6 rounded-full bg-green-600 hover:bg-green-500 text-white shadow-sm font-bold flex items-center justify-center transition text-sm leading-none disabled:opacity-30">+</button>
          </div>
          <p class="w-[70px] text-right text-[13px] font-extrabold text-slate-900 tabular-nums shrink-0" x-text="Number(item.qty * item.price).toLocaleString()"></p>
        </div>
      </template>
    </div>

    {{-- Checkout (fixed bottom) --}}
    <div class="shrink-0 border-t border-slate-200 bg-white">
      {{-- Submitted via fetch (submitSale) so failed sales can be queued offline --}}
      <form method="POST" action="{{ route('pos.store') }}" @submit.prevent="submitSale()">
        @csrf

        {{-- Totals --}}
        <div class="px-4 pt-2 pb-1.5 space-y-1">
          <div class="flex justify-between text-[13px] text-slate-500">
            <span>{{ __('pos.subtotal') }}</span>
            <span x-text="'PKR ' + subtotal.toLocaleString()" class="tabular-nums text-slate-700"></span>
          </div>
          <div class="flex justify-between items-center text-[13px] text-slate-500">
            <span class="flex items-center gap-1.5">Discount
              <span x-show="discountType === 'percent' && discountAmount > 0" x-cloak class="text-[11px] text-red-500 font-semibold tabular-nums" x-text="'−PKR ' + discountAmount.toLocaleString()"></span>
            </span>
            <div class="flex items-center gap-1">
              {{-- type toggle --}}
              <div class="flex rounded-lg border border-slate-200 overflow-hidden text-[11px] font-bold shrink-0">
                <button type="button" @click="discountType = 'flat'" :class="discountType === 'flat' ? 'bg-slate-800 text-white' : 'bg-white text-slate-400 hover:bg-slate-50'" class="px-2 py-1 transition">PKR</button>
                <button type="button" @click="discountType = 'percent'" :class="discountType === 'percent' ? 'bg-slate-800 text-white' : 'bg-white text-slate-400 hover:bg-slate-50'" class="px-2 py-1 transition">%</button>
              </div>
              <input type="number" x-model="discount" min="0" step="1" placeholder="0"
                     :max="discountType === 'percent' ? 100 : null"
                     class="w-14 text-right px-2 py-1 bg-white border border-slate-200 rounded-lg text-slate-900 text-sm outline-none focus:ring-2 focus:ring-green-300 transition tabular-nums">
            </div>
          </div>
          <div class="flex justify-between items-baseline pt-1 border-t border-slate-100">
            <span class="text-slate-700 font-semibold text-sm">{{ __('pos.total') }}</span>
            <span class="text-slate-900 text-lg font-extrabold tabular-nums" x-text="'PKR ' + total.toLocaleString()"></span>
          </div>
        </div>

        <input type="hidden" name="payment_method" x-model="payMethod">

        {{-- Submit — opens the Payment popup --}}
        <div class="px-4 pt-2 pb-3 flex gap-2">
          <button type="button" @click="openPaymentModal()" :disabled="cart.length === 0 || submitting"
                  class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-slate-200 disabled:text-slate-400 text-white py-2.5 rounded-xl font-extrabold text-[13px] transition flex items-center justify-center gap-2 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span x-text="submitting ? 'Processing...' : '{{ __('pos.complete_sale') }}'"></span>
          </button>
          @if(feature_enabled('quotations'))
          <button type="button" @click="saveQuotation()" :disabled="cart.length === 0" title="Quotation banayein"
                  class="shrink-0 bg-white border border-slate-200 hover:border-green-300 hover:text-green-700 disabled:opacity-40 text-slate-600 px-3.5 py-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>
            Quotation
          </button>
          @endif
        </div>
      </form>
    </div>
  </div>

  </div>{{-- /flex row --}}

  {{-- ══ Mobile cart bottom bar (<md) ══ --}}
  <button type="button" @click="mobileCartOpen = true"
          class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-green-600 px-4 py-3 flex items-center justify-between text-left shadow-lg">
    <span class="flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
      <span class="text-white font-bold text-sm" x-text="cart.length + ' item(s)'"></span>
    </span>
    <span class="flex items-center gap-2">
      <span class="text-white font-extrabold text-sm tabular-nums" x-text="'PKR ' + total.toLocaleString()"></span>
      <span class="bg-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-xl">{{ __('pos.view_cart') }}</span>
    </span>
  </button>

  @if(feature_enabled('barcode_scanner'))
  {{-- Camera Modal --}}
  <div x-show="cameraOpen" x-cloak x-transition
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
       @keydown.escape.window="closeCamera()">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden" @click.outside="closeCamera()">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="font-bold text-slate-900">{{ __('pos.camera_scanner') }}</h3>
          <p class="text-xs text-slate-400 mt-0.5" x-text="cameraStatus"></p>
        </div>
        <button @click="closeCamera()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition">✕</button>
      </div>
      <div class="relative bg-black">
        <div id="pos-camera-reader" class="w-full" style="min-height:16rem"></div>
        <div x-show="cameraError" class="absolute inset-0 flex items-center justify-center bg-slate-900/90 p-6">
          <p class="text-white text-sm text-center leading-relaxed" x-text="cameraError"></p>
        </div>
      </div>
      <div class="px-5 py-4 text-center">
        <p class="text-xs text-slate-500">{{ __('pos.barcode_hint') }}</p>
        <p x-show="lastCameraResult" class="mt-1.5 text-sm font-mono font-bold text-green-700" x-text="lastCameraResult"></p>
      </div>
    </div>
  </div>
  @endif

  @if(feature_enabled('open_tabs'))
  {{-- Hold Sale Modal --}}
  <div x-show="holdModalOpen" x-cloak x-transition
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
       @keydown.escape.window="holdModalOpen = false">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden" @click.outside="holdModalOpen = false">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="font-bold text-slate-900" x-text="holdId ? '{{ __('pos.update_hold') }}' : '{{ __('pos.hold_sale') }}'"></h3>
          <p class="text-xs text-slate-400 mt-0.5">Cart save ho jayega, baad mein resume karein</p>
        </div>
        <button type="button" @click="holdModalOpen = false" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition">✕</button>
      </div>
      <form @submit.prevent="confirmHold()" class="px-5 py-4 space-y-3">
        <div>
          <label class="block text-xs font-bold text-slate-500 mb-1">Customer Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="holdCustomerName" x-ref="holdNameInput" required placeholder="e.g. Rafiq mistri"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-300 outline-none transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 mb-1">Phone (optional)</label>
          <input type="text" x-model="holdCustomerPhone" placeholder="03xx-xxxxxxx"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-300 outline-none transition">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 mb-1">Note (optional)</label>
          <input type="text" x-model="holdNote" placeholder="e.g. CD-70 ka kaam"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-300 outline-none transition">
        </div>
        <button type="submit" :disabled="holdSaving || !holdCustomerName.trim()"
                class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white py-2.5 rounded-xl font-bold text-sm transition"
                x-text="holdSaving ? 'Saving...' : (holdId ? '{{ __('pos.update_hold') }}' : '{{ __('pos.hold_sale') }} — ' + cart.length + ' item(s)')"></button>
      </form>
    </div>
  </div>

  {{-- Recent Sales quick-view slide-over --}}
  <div x-show="recentPanelOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="recentPanelOpen = false">
    <div class="absolute inset-0 bg-black/50" @click="recentPanelOpen = false" x-show="recentPanelOpen" x-transition.opacity></div>
    <div class="absolute right-0 inset-y-0 w-full max-w-md bg-white shadow-2xl flex flex-col"
         x-show="recentPanelOpen" x-transition:enter="transition transform duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
        <div>
          <h3 class="font-bold text-slate-900">{{ __('pos.todays_sales') }}</h3>
          <p class="text-xs text-slate-400 mt-0.5" x-text="recentSales.length + ' sale(s) · PKR ' + recentSales.reduce((s,r)=>s+Number(r.total||0),0).toLocaleString()"></p>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('pos.index') }}" class="text-xs font-semibold text-green-600 hover:underline">Full history →</a>
          <button type="button" @click="recentPanelOpen = false" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition">✕</button>
        </div>
      </div>
      <div class="flex-1 overflow-y-auto p-3 space-y-2">
        <template x-if="recentSales.length === 0">
          <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center mb-3">
              <svg style="width:24px;height:24px" class="text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            </div>
            <p class="text-slate-500 text-sm font-medium">{{ __('pos.no_sale_today') }}</p>
          </div>
        </template>
        <template x-for="s in recentSales" :key="s.number">
          <a :href="s.url" target="_blank"
             class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-100 hover:border-green-300 hover:bg-green-50/50 transition">
            <div class="w-9 h-9 rounded-lg bg-green-100 text-green-700 flex items-center justify-center shrink-0">
              <svg style="width:16px;height:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-800 truncate" x-text="s.number"></p>
              <p class="text-[11px] text-slate-400 truncate" x-text="(s.customer || 'Walk-in') + ' · ' + s.time"></p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-sm font-extrabold text-slate-900 tabular-nums" x-text="'PKR ' + Number(s.total).toLocaleString()"></p>
              <p class="text-[10px] text-slate-400" x-text="payLabel(s.method)"></p>
            </div>
          </a>
        </template>
      </div>
    </div>
  </div>

  {{-- Held Sales slide-over --}}
  <div x-show="holdsPanelOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="holdsPanelOpen = false; confirmDeleteId = null">
    <div class="absolute inset-0 bg-black/50" @click="holdsPanelOpen = false; confirmDeleteId = null" x-show="holdsPanelOpen" x-transition.opacity></div>
    <div class="absolute right-0 inset-y-0 w-full max-w-md bg-white shadow-2xl flex flex-col"
         x-show="holdsPanelOpen" x-transition:enter="transition transform duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0 bg-slate-50/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="10" x2="10" y1="9" y2="15"/><line x1="14" x2="14" y1="9" y2="15"/></svg>
          </div>
          <div>
            <h3 class="font-bold text-slate-900 leading-tight">{{ __('pos.held_sales') }}</h3>
            <p class="text-xs text-slate-400 mt-0.5" x-text="heldSales.length + ' hold' + (heldSales.length === 1 ? '' : 's') + ' pending'"></p>
          </div>
        </div>
        <button type="button" @click="holdsPanelOpen = false" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition">✕</button>
      </div>

      {{-- Total value bar --}}
      <div x-show="heldSales.length > 0" class="flex items-center justify-between px-5 py-2.5 bg-amber-50/60 border-b border-amber-100 shrink-0">
        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide">{{ __('pos.total_held') }}</span>
        <span class="text-sm font-extrabold text-amber-700 tabular-nums" x-text="'PKR ' + heldSales.reduce((s,h)=>s+Number(h.total||0),0).toLocaleString()"></span>
      </div>

      <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
        <template x-if="heldSales.length === 0">
          <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="10" x2="10" y1="9" y2="15"/><line x1="14" x2="14" y1="9" y2="15"/></svg>
            </div>
            <p class="text-slate-400 text-sm font-medium">{{ __('pos.no_holds') }}</p>
            <p class="text-slate-300 text-xs mt-1">Cart mein "Hold" button se sale save karein</p>
          </div>
        </template>
        <template x-for="h in heldSales" :key="h.id">
          <div class="border border-slate-200 rounded-2xl p-4 bg-white hover:border-slate-300 transition">
            {{-- Title + amount --}}
            <div class="flex items-center justify-between gap-3">
              <p class="font-bold text-slate-900 text-sm truncate" x-text="h.customer_name || ('Order ' + h.tab_number)"></p>
              <p class="font-extrabold text-slate-900 text-base whitespace-nowrap shrink-0 tabular-nums" x-text="'PKR ' + Number(h.total).toLocaleString()"></p>
            </div>
            {{-- Meta --}}
            <p class="text-xs text-slate-400 mt-1">
              <span class="font-mono font-semibold text-slate-500" x-text="h.tab_number"></span>
              <span> · </span><span x-text="h.items_count + ' item(s)'"></span>
              <span> · </span><span x-text="timeAgo(h.created_at)"></span>
            </p>

            {{-- Normal actions --}}
            <div class="flex items-center gap-2 mt-3" x-show="confirmDeleteId !== h.id">
              <button type="button" @click="resumeHold(h)"
                      class="flex-1 inline-flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 active:scale-[.99] text-white py-2 rounded-lg text-xs font-bold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                {{ __('pos.resume') }}
              </button>
              <button type="button" @click="confirmDeleteId = h.id" title="Delete hold"
                      class="w-8 h-8 inline-flex items-center justify-center border border-red-200 text-red-500 hover:bg-red-50 rounded-lg transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
              </button>
            </div>

            {{-- Inline delete confirm (replaces the native browser popup) --}}
            <div class="mt-3 bg-red-50 border border-red-100 rounded-xl p-2.5 flex items-center gap-2" x-show="confirmDeleteId === h.id" x-cloak>
              <span class="text-xs text-red-700 font-medium flex-1">Ye hold delete karein?</span>
              <button type="button" @click="deleteHold(h)"
                      class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition">Haan, delete</button>
              <button type="button" @click="confirmDeleteId = null"
                      class="px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-xs font-bold transition">{{ __('common.no') }}</button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
  @endif

  {{-- ══ Payment popup (method selector + amount / split / udhar) ══ --}}
  <div x-show="finalizeModalOpen" x-cloak x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center p-4"
       style="background:rgba(15,23,42,0.55);backdrop-filter:blur(2px);"
       @keydown.escape.window="finalizeModalOpen = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full overflow-hidden flex flex-col max-h-[88vh]" style="max-width:23rem"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         @click.outside="finalizeModalOpen = false">
      {{-- Header --}}
      <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 shrink-0">
        <h3 class="font-bold text-slate-900">{{ __('pos.payment') }}</h3>
        <button type="button" @click="finalizeModalOpen = false" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400">✕</button>
      </div>

      <div class="overflow-y-auto">
      {{-- Total --}}
      <div class="px-4 pt-2.5">
        <div class="flex items-center justify-between bg-slate-50 rounded-xl px-4 py-2.5">
          <span class="text-sm text-slate-500 font-medium">{{ __('pos.total_to_pay') }}</span>
          <span class="text-xl font-extrabold text-slate-900 tabular-nums" x-text="'PKR ' + total.toLocaleString()"></span>
        </div>
      </div>

      {{-- Customer (same dropdown as before, now the single place) --}}
      <div class="px-4 pt-2.5 relative z-30" @click.outside="showCustList = false">
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">{{ __('pos.customer_info') }}</p>

        {{-- Search / walk-in state --}}
        <template x-if="!selectedCustomer && !addingCustomer">
          <div class="relative">
            <div class="flex gap-1.5">
              <button type="button"
                      @click="showCustList = !showCustList; if (showCustList) $nextTick(() => $refs.custSearchPop && $refs.custSearchPop.focus())"
                      class="flex-1 min-w-0 flex items-center gap-2 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm hover:border-green-300 transition"
                      :class="showCustList ? 'ring-2 ring-green-300 bg-white' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="flex-1 text-left text-slate-500 truncate">Walk-in Customer</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="showCustList ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <button type="button" @click="startAddCustomer()" title="Naya customer add karein"
                      class="w-10 h-10 rounded-xl bg-green-600 hover:bg-green-700 text-white flex items-center justify-center shrink-0 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              </button>
            </div>

            <div x-show="showCustList" x-cloak x-transition.opacity.duration.100ms
                 class="absolute z-40 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden">
              <div class="p-2 border-b border-slate-100">
                <div class="relative">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                  <input type="text" x-ref="custSearchPop" x-model="customerQuery" placeholder="{{ __('pos.search_customer') }}"
                         class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 text-sm placeholder-slate-400 outline-none focus:ring-2 focus:ring-green-300 focus:bg-white transition">
                </div>
              </div>
              <div class="max-h-52 overflow-y-auto">
                <template x-for="c in filteredCustomers" :key="c.id">
                  <button type="button" @click="pickCustomer(c)"
                          class="w-full text-left px-3 py-2 hover:bg-green-50 flex items-center justify-between gap-2 border-b border-slate-50 last:border-0 transition">
                    <span class="min-w-0">
                      <span class="block text-sm font-semibold text-slate-800 truncate" x-text="c.name"></span>
                      <span class="block text-xs text-slate-400" x-text="c.phone || '—'"></span>
                    </span>
                    <span x-show="c.current_balance > 0" class="text-[11px] font-bold text-amber-600 whitespace-nowrap" x-text="'Udhar ' + Number(c.current_balance).toLocaleString()"></span>
                  </button>
                </template>
                <div x-show="!filteredCustomers.length" class="px-3 py-4 text-center text-xs text-slate-400">Koi customer nahi mila</div>
              </div>
            </div>
          </div>
        </template>

        {{-- Add-new inline --}}
        <template x-if="addingCustomer">
          <div class="rounded-2xl border border-green-200 bg-green-50/60 p-3">
            <div class="flex items-center justify-between mb-3">
              <p class="text-[11px] text-green-800 font-bold uppercase tracking-wider">{{ __('pos.new_customer') }}</p>
              <button type="button" @click="cancelAddCustomer()" class="text-xs text-slate-400 hover:text-slate-600 font-semibold">{{ __('common.cancel') }}</button>
            </div>
            <div class="space-y-2">
              <input type="text" x-model="customerName" placeholder="{{ __('pos.customer_name') }}" @keydown.enter.prevent="saveNewCustomer()"
                     class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm outline-none focus:ring-2 focus:ring-green-300 transition">
              <input type="tel" x-model="customerPhone" placeholder="{{ __('pos.phone_number') }}" @keydown.enter.prevent="saveNewCustomer()"
                     class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm outline-none focus:ring-2 focus:ring-green-300 transition">
              <p class="text-[11px] text-slate-400 leading-snug">Udhar sale ke liye phone number zaroori hai.</p>
              <button type="button" @click="saveNewCustomer()" :disabled="!customerName.trim()"
                      class="w-full py-2 bg-green-600 hover:bg-green-700 disabled:bg-slate-200 disabled:text-slate-400 text-white text-sm font-bold rounded-xl transition">Customer add karein</button>
            </div>
          </div>
        </template>

        {{-- Selected --}}
        <template x-if="selectedCustomer">
          <div class="flex gap-1.5">
            <button type="button" title="Customer badlein"
                    @click="clearCustomer(); showCustList = true; $nextTick(() => $refs.custSearchPop && $refs.custSearchPop.focus())"
                    class="flex-1 min-w-0 flex items-center gap-2 px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm hover:border-green-300 transition">
              <span class="w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-[11px] shrink-0" x-text="(selectedCustomer.name || '?').charAt(0).toUpperCase()"></span>
              <span class="flex-1 text-left min-w-0">
                <span class="block text-slate-800 font-semibold leading-tight truncate" x-text="selectedCustomer.name"></span>
                <span class="block text-[11px] text-slate-400 leading-tight truncate" x-text="(selectedCustomer.phone || '{{ __('pos.no_phone') }}') + (selectedCustomer.current_balance > 0 ? '  ·  Udhar PKR ' + Number(selectedCustomer.current_balance).toLocaleString() : '')"></span>
              </span>
            </button>
            <button type="button" @click="clearCustomer()" title="Hatayein"
                    class="w-10 h-10 rounded-xl border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 flex items-center justify-center shrink-0 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </template>
      </div>

      {{-- Method selector --}}
      <div class="px-4 pt-2.5">
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">{{ __('pos.payment_method') }}</p>
        <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:0.375rem">
          <template x-for="pm in payMethods" :key="pm.value">
            <button type="button" @click="payMethod = pm.value"
                    :class="payMethod === pm.value ? 'border-green-500 bg-green-50 text-green-700' : 'border-slate-200 bg-white text-slate-500 hover:border-green-300'"
                    class="flex flex-col items-center justify-center gap-0.5 py-2 rounded-xl border transition">
              <svg style="width:16px;height:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-html="pm.icon"></svg>
              <span x-text="pm.label" class="text-[10px] font-bold"></span>
            </button>
          </template>
        </div>
      </div>


      {{-- Cash / Online amount --}}
      <div class="px-4 pt-2.5" x-show="payMethod === 'cash' || payMethod === 'online'">
        <div class="flex items-center justify-between mb-1">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider" x-text="payMethod === 'online' ? '{{ __('pos.amount_received') }}' : '{{ __('pos.cash_received') }}'"></label>
        </div>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">PKR</span>
          <input type="number" x-model="receivedAmount" min="0" step="1" @keydown.enter="confirmPayment()"
                 class="w-full pl-12 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-base font-bold outline-none focus:ring-2 focus:ring-green-300 transition tabular-nums">
        </div>
        <div class="flex items-center justify-between mt-2 px-1">
          <span class="text-xs text-slate-500 font-medium">{{ __('pos.change_to_return') }}</span>
          <span class="text-base font-extrabold tabular-nums" :class="change > 0 ? 'text-green-600' : 'text-slate-400'" x-text="'PKR ' + change.toLocaleString()"></span>
        </div>
      </div>

      {{-- Udhar (full credit) --}}
      <div class="px-4 pt-2.5" x-show="payMethod === 'credit'" x-cloak>
        <div class="rounded-2xl border p-4" :class="(customerName.trim() && customerPhone.trim()) ? 'border-green-200 bg-green-50/60' : 'border-amber-200 bg-amber-50/60'">
          <template x-if="customerName.trim() && customerPhone.trim()">
            <p class="text-sm text-slate-700">Poori raqam <b class="text-green-700" x-text="'PKR ' + total.toLocaleString()"></b> <b x-text="customerName"></b> ke udhaar khaate me jayegi.</p>
          </template>
          <template x-if="!(customerName.trim() && customerPhone.trim())">
            <p class="text-sm text-amber-700 font-medium">⚠ Udhar ke liye upar se customer select karein.</p>
          </template>
        </div>
      </div>

      {{-- Split tenders --}}
      <div class="px-4 pt-3 space-y-2.5" x-show="payMethod === 'split'" x-cloak>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 uppercase tracking-wider">
              <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/></svg> Cash
            </label>
            <button type="button" @click="cashAmount = String(Math.max(0, total - (Number(onlineAmount)||0) - (Number(udharAmount)||0)))" class="text-[11px] text-green-600 font-semibold hover:underline">Baaki cash</button>
          </div>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">PKR</span>
            <input type="number" x-model="cashAmount" min="0" step="1" placeholder="0" class="w-full pl-12 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 font-semibold outline-none focus:ring-2 focus:ring-green-300 transition tabular-nums"></div>
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 uppercase tracking-wider">
              <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/></svg> Online
            </label>
            <button type="button" @click="onlineAmount = String(Math.max(0, total - (Number(cashAmount)||0) - (Number(udharAmount)||0)))" class="text-[11px] text-green-600 font-semibold hover:underline">Baaki online</button>
          </div>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">PKR</span>
            <input type="number" x-model="onlineAmount" min="0" step="1" placeholder="0" class="w-full pl-12 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 font-semibold outline-none focus:ring-2 focus:ring-green-300 transition tabular-nums"></div>
        </div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-500 uppercase tracking-wider">
              <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg> Udhar
            </label>
            <button type="button" @click="udharAmount = String(Math.max(0, total - splitPaid))" class="text-[11px] text-green-600 font-semibold hover:underline">Baaki udhar</button>
          </div>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">PKR</span>
            <input type="number" x-model="udharAmount" min="0" step="1" placeholder="0" class="w-full pl-12 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 font-semibold outline-none focus:ring-2 focus:ring-green-300 transition tabular-nums"></div>
          <p x-show="splitUdhar > 0 && (!customerName.trim() || !customerPhone.trim())" x-cloak class="text-[11px] text-amber-600 font-semibold mt-1">⚠ Udhar ke liye customer select karein.</p>
        </div>
        <div class="flex items-center justify-between text-sm px-1 pt-1">
          <span class="text-slate-500" x-text="splitAllocated > total ? '{{ __('pos.extra') }}' : '{{ __('pos.remaining') }}'"></span>
          <span class="font-bold tabular-nums" :class="splitAllocated >= total ? 'text-green-600' : 'text-red-500'" x-text="'PKR ' + Math.abs(splitAllocated - total).toLocaleString()"></span>
        </div>
      </div>
      </div>

      {{-- Actions — single Sale Complete button + auto-print toggle --}}
      <div class="px-4 py-3 shrink-0 border-t border-slate-100 bg-slate-50/50">
        @if(feature_enabled('receipt_print'))
        <div @click="toggleAutoPrint()" role="switch" :aria-checked="autoPrint"
             class="flex items-center justify-between gap-2 mb-2 cursor-pointer select-none">
          <span class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
            <svg style="width:13px;height:13px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Har sale par auto-print
          </span>
          <span class="relative inline-block shrink-0 rounded-full border transition-colors" style="width:36px;height:20px"
                :style="autoPrint ? 'background:#22c55e;border-color:#16a34a' : 'background:#e2e8f0;border-color:#cbd5e1'">
            <span class="absolute rounded-full bg-white shadow transition-transform" style="top:2px;left:2px;width:14px;height:14px"
                  :style="autoPrint ? 'transform:translateX(16px)' : 'transform:translateX(0)'"></span>
          </span>
        </div>
        @endif
        <button type="button" @click="confirmPayment(false)" :disabled="submitting"
                class="w-full bg-green-600 hover:bg-green-700 active:scale-[.99] disabled:bg-slate-200 disabled:text-slate-400 text-white py-3 rounded-xl font-bold text-sm transition flex items-center justify-center gap-2 shadow-sm shadow-green-600/20">
          <svg style="width:16px;height:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span x-text="submitting ? '...' : '{{ __('pos.complete_sale') }}'"></span>
        </button>
      </div>
    </div>
  </div>

  {{-- ══ Calculator (floating) ══ --}}
  <div x-show="calcOpen" x-cloak x-transition
       class="fixed z-50 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
       style="top:4rem;right:1rem;width:15rem"
       @keydown.window="calcKey($event)" @click.outside="calcOpen = false">
    <div class="flex items-center justify-between px-3 py-2 bg-slate-800 text-white">
      <span class="text-xs font-bold flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/></svg>
        Calculator
      </span>
      <button type="button" @click="calcOpen = false" class="w-6 h-6 rounded hover:bg-white/10 flex items-center justify-center text-slate-300">✕</button>
    </div>
    <div class="px-3 pt-3 pb-1 text-right">
      <p class="text-2xl font-extrabold text-slate-900 tabular-nums truncate" x-text="calcExpr || '0'"></p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:0.375rem;padding:0.625rem">
      <template x-for="k in ['C','DEL','%','÷','7','8','9','×','4','5','6','-','1','2','3','+','0','.','=']" :key="k">
        <button type="button" @click="calcPress(k)"
                :style="k === '=' ? 'grid-column:span 2' : ''"
                :class="{
                  'bg-slate-100 text-slate-700 hover:bg-slate-200': ['C','DEL','%'].includes(k),
                  'bg-slate-800 text-white hover:bg-slate-700': ['÷','×','-','+'].includes(k),
                  'bg-green-600 text-white hover:bg-green-700': k === '=',
                  'bg-slate-50 text-slate-900 hover:bg-slate-100': !['C','DEL','%','÷','×','-','+','='].includes(k)
                }"
                class="py-2.5 rounded-xl font-bold text-sm transition" x-text="k"></button>
      </template>
    </div>
  </div>

  {{-- ══ Variant picker modal ══ --}}
  <div x-show="variantModalOpen" x-cloak x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center p-4"
       style="background:rgba(15,23,42,0.5);backdrop-filter:blur(2px);"
       @keydown.escape.window="variantModalOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full overflow-hidden flex flex-col max-h-[85vh]" style="max-width:24rem" @click.outside="variantModalOpen = false">
      <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 shrink-0">
        <div class="min-w-0">
          <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('pos.choose_variant') }}</p>
          <h3 class="font-bold text-slate-900 truncate" x-text="variantGroupName"></h3>
        </div>
        <button type="button" @click="variantModalOpen = false" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">✕</button>
      </div>
      <div class="overflow-y-auto p-2">
        <template x-for="v in variantOptions" :key="v.id">
          <button type="button" @click="pickVariant(v)" :disabled="v.stock_qty <= 0"
                  class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl border border-slate-100 hover:border-green-300 hover:bg-green-50 disabled:opacity-40 disabled:cursor-not-allowed transition mb-1.5">
            <div class="w-10 h-10 rounded-lg bg-slate-50 overflow-hidden flex items-center justify-center shrink-0">
              <template x-if="v.image_url"><img :src="v.image_url" class="w-full h-full object-cover"></template>
              <template x-if="!v.image_url"><span class="text-slate-300 text-lg">▦</span></template>
            </div>
            <div class="flex-1 min-w-0 text-left">
              <p class="text-sm font-bold text-slate-800 truncate" x-text="v.variant_name || v.name"></p>
              <p class="text-[11px] font-semibold" :class="v.stock_qty <= 0 ? 'text-red-500' : 'text-slate-400'"
                 x-text="v.stock_qty <= 0 ? '{{ __('pos.out_of_stock') }}' : v.stock_qty + ' ' + v.unit + ' available'"></p>
            </div>
            <p class="text-sm font-extrabold text-green-600 shrink-0" x-text="'PKR ' + Number(priceFor(v)).toLocaleString()"></p>
          </button>
        </template>
      </div>
    </div>
  </div>

  {{-- ══ Sale Complete modal (stays on POS, next order ready) ══ --}}
  <div x-show="saleModalOpen" x-cloak x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center p-4"
       style="background:rgba(15,23,42,0.55);backdrop-filter:blur(2px);"
       @keydown.escape.window="closeSaleModal()">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden mx-auto relative"
         style="width:100%;max-width:22rem;"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
         @click.outside="closeSaleModal()">
      {{-- Decorative gradient header band --}}
      <div style="height:88px;background:linear-gradient(135deg,#16a34a 0%,#059669 100%)"></div>

      {{-- Success check (overlaps band) --}}
      <div class="flex justify-center" style="margin-top:-44px">
        <div class="w-[72px] h-[72px] rounded-full bg-white flex items-center justify-center shadow-lg" style="animation:salePop .35s cubic-bezier(.34,1.56,.64,1) both">
          <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          </div>
        </div>
      </div>

      {{-- Title --}}
      <div class="pt-3 pb-1 px-6 text-center">
        <h3 class="text-xl font-extrabold text-slate-900">{{ __('pos.sale_complete') }}</h3>
        <p class="text-[13px] text-green-600 font-semibold mt-0.5">{{ __('pos.payment_received') }}</p>
        <p class="text-[11px] text-slate-400 font-mono mt-1" x-text="lastSale.number"></p>
      </div>

      {{-- Amount box --}}
      <div class="px-5 pt-2">
        <div class="rounded-2xl px-4 py-4 text-center border border-slate-100" style="background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%)">
          <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Paid</p>
          <p class="text-[34px] leading-none font-extrabold text-slate-900 tabular-nums" x-text="'PKR ' + Number(lastSale.total).toLocaleString()"></p>
          <div class="flex items-center justify-center gap-2 mt-3">
            <span class="px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold" x-text="payLabel(lastSale.payMethod)"></span>
            <template x-if="lastSale.change > 0">
              <span class="px-2.5 py-0.5 rounded-full bg-white text-slate-600 text-xs font-semibold tabular-nums border border-slate-200" x-text="'Change PKR ' + Number(lastSale.change).toLocaleString()"></span>
            </template>
          </div>
        </div>
      </div>

      {{-- Print / WhatsApp --}}
      <div class="px-5 pt-3 pb-3 flex gap-2"
           x-show="{{ feature_enabled('receipt_print') ? 'true' : 'false' }} || lastSale.whatsappUrl">
        @if(feature_enabled('receipt_print'))
        <button type="button" @click="printReceipt()" class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:border-green-400 hover:text-green-700 hover:bg-green-50/50 text-sm font-bold transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Print
        </button>
        @endif
        <template x-if="lastSale.whatsappUrl">
          <a :href="lastSale.whatsappUrl" target="_blank" class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:border-emerald-400 hover:text-emerald-600 hover:bg-emerald-50/50 text-sm font-bold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            WhatsApp
          </a>
        </template>
      </div>

      {{-- New Sale (primary) --}}
      <div class="px-5 pb-5">
        <button type="button" @click="closeSaleModal()"
                class="w-full py-3.5 rounded-2xl bg-green-600 hover:bg-green-700 active:scale-[.99] text-white font-extrabold text-sm transition flex items-center justify-center gap-2 shadow-sm shadow-green-600/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
          {{ __('pos.new_sale') }}
        </button>
      </div>
    </div>
  </div>

  {{-- IMEI / Serial capture modal --}}
  <div x-show="serialModalOpen" x-cloak x-transition
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
       @keydown.escape.window="serialModalOpen = false">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-[90vh]" @click.outside="serialModalOpen = false">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
        <div>
          <h3 class="font-bold text-slate-900">IMEI / Serial Numbers</h3>
          <p class="text-xs text-slate-400 mt-0.5">Scan karein ya type karein — Enter se agla box</p>
        </div>
        <button type="button" @click="serialModalOpen = false" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition">✕</button>
      </div>
      <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
        <template x-for="si in serialItems" :key="si.id">
          <div>
            <p class="text-xs font-bold text-slate-600 mb-1.5" x-text="si.name + ' — ' + si.qty + ' pcs'"></p>
            <div class="space-y-1.5">
              <template x-for="n in si.qty" :key="n">
                <input type="text" maxlength="64"
                       x-model="serialInputs[si.id][n - 1]"
                       :data-serial-idx="serialInputIndex(si.id, n - 1)"
                       @keydown.enter.prevent="focusNextSerial(si.id, n - 1)"
                       :placeholder="'Serial / IMEI #' + n"
                       class="w-full px-3 py-2 text-sm font-mono border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-300 outline-none transition">
              </template>
            </div>
          </div>
        </template>
      </div>
      <div class="px-5 py-4 border-t border-slate-100 flex gap-2 shrink-0">
        <button type="button" @click="skipSerials()"
                class="px-4 py-2.5 border border-slate-200 text-slate-500 hover:bg-slate-50 rounded-xl text-xs font-bold transition">Skip</button>
        <button type="button" @click="confirmSerials()"
                class="flex-1 bg-green-600 hover:bg-green-500 text-white py-2.5 rounded-xl font-bold text-sm transition">Continue Sale</button>
      </div>
    </div>
  </div>
</div>

<script>
function posApp() {
  return {
    products: window.__POS_PRODUCTS__ || [],
    cart: [],
    searchQ: '',
    activeCategory: null,
    hideOutOfStock: false,
    discount: '',
    discountType: 'flat',
    amountPaid: '',
    cashAmount: '',
    onlineAmount: '',
    udharAmount: '',
    receivedAmount: '',
    finalizeModalOpen: false,
    payMethod: 'cash',
    payMethods: [
      { value: 'cash',   label: 'Cash',   icon: '<rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>' },
      { value: 'online', label: 'Online', icon: '<rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/>' },
      { value: 'credit', label: 'Udhar',  icon: '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/>' },
      { value: 'split',  label: 'Split',  icon: '<path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="m21 3-7.5 7.5"/><path d="M3 3l7.5 7.5"/><path d="M12 13v8"/>' },
    ],
    customerName: '',
    customerPhone: '',
    customers: window.__POS_CUSTOMERS__ || [],
    recentSales: window.__POS_RECENT__ || [],
    recentPanelOpen: false,
    quoteNumber: @json($nextQuoteNumber ?? ''),
    quoteToday: @json(date('Y-m-d')),
    saleModalOpen: false,
    splitModalOpen: false,
    isFullscreen: false,
    printAfter: false,
    calcOpen: false,
    calcExpr: '',
    variantModalOpen: false,
    variantGroupName: '',
    variantOptions: [],
    lastSale: { number: '', total: 0, change: 0, payMethod: 'cash', receiptUrl: '', whatsappUrl: null },
    autoPrint: false,
    saleModalTimer: null,
    customerQuery: '',
    showCustList: false,
    addingCustomer: false,
    selectedCustomer: null,
    submitting: false,
    mobileCartOpen: false,
    // Retail / Wholesale price mode (new adds only — cart items keep their price)
    priceMode: 'retail',
    // Hold / Resume (Open Tabs backend)
    holdsEnabled: {{ feature_enabled('open_tabs') ? 'true' : 'false' }},
    heldSales: window.__POS_HOLDS__ || [],
    holdsPanelOpen: false,
    confirmDeleteId: null,
    holdModalOpen: false,
    holdSaving: false,
    holdId: null,          // set when cart was resumed from a hold → re-hold updates in place
    holdTabNumber: '',
    holdCustomerName: '',
    holdCustomerPhone: '',
    holdNote: '',
    // IMEI / serial capture
    serialModalOpen: false,
    serialItems: [],
    serialInputs: {},
    serials: {},
    offlineQueue: [],
    syncing: false,
    QUEUE_KEY: 'pos_offline_queue',
    scanMsg: '', scanOk: true, scanTimer: null,
    scannerEnabled: {{ feature_enabled('barcode_scanner') ? 'true' : 'false' }},
    cameraOpen: false, cameraStatus: '', cameraError: '', lastCameraResult: '',
    html5Qr: null,

    init() {
      this.loadQueue();
      try { this.autoPrint = localStorage.getItem('posAutoPrint') === '1'; } catch (e) {}
      // On smaller screens the POS needs every pixel — collapse the sidebar
      // automatically (kiosk-style). User can still reopen it from the top bar.
      if (window.innerWidth < 1280) document.documentElement.classList.add('sidebar-collapsed');
      document.addEventListener('fullscreenchange', () => { this.isFullscreen = !!document.fullscreenElement; });
      window.addEventListener('online', () => this.syncQueue());
      // Try syncing anything left over from a previous session
      this.syncQueue();
    },

    // ── Offline sale queue ──────────────────────────────────────
    loadQueue() {
      try { this.offlineQueue = JSON.parse(localStorage.getItem(this.QUEUE_KEY)) || []; }
      catch (e) { this.offlineQueue = []; }
    },

    saveQueue() {
      localStorage.setItem(this.QUEUE_KEY, JSON.stringify(this.offlineQueue));
    },

    makeUuid() {
      return (window.crypto && crypto.randomUUID)
        ? crypto.randomUUID()
        : Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
    },

    buildPayload() {
      return {
        client_uuid:    this.makeUuid(),
        customer_name:  this.customerName || null,
        customer_phone: this.customerPhone || null,
        udhar_customer_id: (this.selectedCustomer && this.selectedCustomer.id) ? this.selectedCustomer.id : null,
        payment_method: this.payMethod,
        discount:       this.discountAmount,
        amount_paid:    this.paidTotal,
        cash_amount:    this.payMethod === 'split' ? (Number(this.cashAmount) || 0) : null,
        online_amount:  this.payMethod === 'split' ? (Number(this.onlineAmount) || 0) : null,
        hold_id:        this.holdId || null,
        serials:        Object.keys(this.serials).length ? this.serials : null,
        items:          this.cart.map(i => ({ product_id: i.id, qty: i.qty, unit_price: i.price })),
      };
    },

    resetSale() {
      this.cart = []; this.discount = ''; this.discountType = 'flat'; this.amountPaid = ''; this.receivedAmount = '';
      this.cashAmount = ''; this.onlineAmount = ''; this.udharAmount = '';
      this.finalizeModalOpen = false; this.splitModalOpen = false;
      this.customerName = ''; this.customerPhone = ''; this.payMethod = 'cash'; this.mobileCartOpen = false;
      this.holdId = null; this.holdTabNumber = '';
      this.serials = {}; this.serialItems = []; this.serialInputs = {};
      this.clearCustomer();
    },

    /* ── Customer picker ── */
    get filteredCustomers() {
      const q = this.customerQuery.trim().toLowerCase();
      const list = q
        ? this.customers.filter(c =>
            (c.name || '').toLowerCase().includes(q) ||
            (c.phone || '').toLowerCase().includes(q))
        : this.customers;
      return list.slice(0, 8);
    },
    pickCustomer(c) {
      this.selectedCustomer = c;
      this.customerName = c.name || '';
      this.customerPhone = c.phone || '';
      this.showCustList = false;
      this.addingCustomer = false;
      this.customerQuery = '';
    },
    startAddCustomer() {
      this.addingCustomer = true;
      this.showCustList = false;
      this.customerName = this.customerQuery.trim();
      this.customerPhone = '';
      this.customerQuery = '';
    },
    cancelAddCustomer() {
      this.addingCustomer = false;
      this.customerName = '';
      this.customerPhone = '';
    },
    saveNewCustomer() {
      if (!this.customerName.trim()) return;
      this.selectedCustomer = {
        id: null, name: this.customerName.trim(),
        phone: this.customerPhone.trim(), current_balance: 0,
      };
      this.addingCustomer = false;
    },
    clearCustomer() {
      this.selectedCustomer = null;
      this.addingCustomer = false;
      this.customerQuery = '';
      this.customerName = '';
      this.customerPhone = '';
    },

    clearCart() {
      this.cart = [];
      this.holdId = null; this.holdTabNumber = '';
      this.serials = {};
    },

    /* Build a quotation from the current cart and open it (normal POST) */
    saveQuotation() {
      if (!this.cart.length) return;
      const f = document.createElement('form');
      f.method = 'POST';
      f.action = @json(route('quotations.store'));
      const add = (n, v) => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = n; i.value = (v === null || v === undefined) ? '' : v;
        f.appendChild(i);
      };
      add('_token', @json(csrf_token()));
      add('quote_number', this.quoteNumber);
      add('date', this.quoteToday);
      add('customer_name', this.customerName);
      add('customer_phone', this.customerPhone);
      add('discount', Number(this.discount || 0));
      this.cart.forEach((it, i) => {
        add(`items[${i}][product_id]`, it.id);
        add(`items[${i}][product_name]`, it.name);
        add(`items[${i}][unit]`, it.unit || '');
        add(`items[${i}][qty]`, it.qty);
        add(`items[${i}][unit_price]`, it.price);
      });
      document.body.appendChild(f);
      f.submit();
    },

    postSale(payload) {
      return fetch('{{ route('pos.store') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
      });
    },

    queueSale(payload) {
      this.offlineQueue.push(payload);
      this.saveQueue();
      this.applySoldStock();   // decrement grid stock now (cart still intact) so offline sales also reflect live
      this.resetSale();
      this.flash('Net nahi hai — sale offline save ho gayi, baad mein sync hogi', true);
    },

    // Reduce local product stock for everything in the cart so the grid reflects
    // the sale instantly (DB is already updated server-side; this avoids a refresh).
    applySoldStock() {
      for (const item of this.cart) {
        const p = this.products.find(pr => pr.id === item.id);
        if (p && typeof p.stock_qty !== 'undefined') {
          p.stock_qty = Math.max(0, Number(p.stock_qty) - Number(item.qty || 0));
        }
      }
    },

    // ── Sale complete modal (stay on POS, ready for next order) ──
    showSaleComplete(json) {
      this.lastSale = {
        number:      json.sale_number || '',
        total:       Number(json.total || this.total || 0),
        change:      Number(json.change || 0),
        payMethod:   json.payment_method || this.payMethod,
        receiptUrl:  json.receipt_url || '',
        whatsappUrl: json.whatsapp_url || null,
      };
      // Prepend to the quick-view recent sales list (live, no refresh needed)
      if (json.sale_number) {
        this.recentSales.unshift({
          number: json.sale_number,
          customer: this.customerName || null,
          total: Number(json.total || this.total || 0),
          method: json.payment_method || this.payMethod,
          time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }),
          url: json.receipt_url || '',
        });
        if (this.recentSales.length > 30) this.recentSales.pop();
      }
      this.applySoldStock();          // live stock update on product grid (no refresh needed)
      this.resetSale();               // cart clear — POS turant next order ke liye ready
      this.saleModalOpen = true;
      if (this.autoPrint || this.printAfter) this.printReceipt();
      this.printAfter = false;
      // Auto-close after a few seconds so a busy counter never stays blocked
      clearTimeout(this.saleModalTimer);
      this.saleModalTimer = setTimeout(() => { this.saleModalOpen = false; }, 6000);
    },

    printReceipt() {
      if (!this.lastSale.receiptUrl) return;
      const sep = this.lastSale.receiptUrl.includes('?') ? '&' : '?';
      window.open(this.lastSale.receiptUrl + sep + 'print=1', '_blank');
    },

    closeSaleModal() {
      clearTimeout(this.saleModalTimer);
      this.saleModalOpen = false;
      this.$nextTick(() => { if (this.$refs.mainInput) this.$refs.mainInput.focus(); });
    },

    toggleAutoPrint() {
      this.autoPrint = !this.autoPrint;
      try { localStorage.setItem('posAutoPrint', this.autoPrint ? '1' : '0'); } catch (e) {}
    },

    submitSale() {
      if (this.cart.length === 0 || this.submitting) return;

      // Udhar requires customer name + phone
      if (this.payMethod === 'credit' && (!this.customerName.trim() || !this.customerPhone.trim())) {
        this.flash('Udhar ke liye customer name aur phone dono likhein', false);
        return;
      }

      // Split must cover the total; udhar portion needs a customer
      if (this.payMethod === 'split') {
        if (this.splitAllocated + 0.01 < this.total) {
          this.flash('Split total ' + this.total.toLocaleString() + ' cover hona chahiye', false); return;
        }
        if (this.splitUdhar > 0 && (!this.customerName.trim() || !this.customerPhone.trim())) {
          this.flash('Udhar portion ke liye customer zaroori hai', false); this.splitModalOpen = true; return;
        }
      }

      // Cash / Online → open finalize modal to enter received amount
      if (this.payMethod === 'cash' || this.payMethod === 'online') {
        this.receivedAmount = String(this.total);
        this.finalizeModalOpen = true;
        return;
      }

      this.proceedSale();
    },

    // Open the unified Payment popup (default cash, exact amount)
    openPaymentModal() {
      if (this.cart.length === 0 || this.submitting) return;
      if (!this.payMethod) this.payMethod = 'cash';
      if (Number(this.receivedAmount || 0) < this.total) this.receivedAmount = String(this.total);
      this.finalizeModalOpen = true;
    },

    // Validate the chosen method then finish. print=true → print invoice after.
    confirmPayment(print = false) {
      this.printAfter = !!print;
      if (this.payMethod === 'credit' && (!this.customerName.trim() || !this.customerPhone.trim())) {
        this.flash('Udhar ke liye customer select karein', false); return;
      }
      if (this.payMethod === 'split') {
        if (this.splitAllocated + 0.01 < this.total) { this.flash('Split total cover hona chahiye', false); return; }
        if (this.splitUdhar > 0 && (!this.customerName.trim() || !this.customerPhone.trim())) {
          this.flash('Udhar portion ke liye customer zaroori hai', false); return;
        }
      }
      if ((this.payMethod === 'cash' || this.payMethod === 'online') && (Number(this.receivedAmount) || 0) + 0.01 < this.total) {
        this.flash('Poori amount receive karein', false); return;
      }
      this.proceedSale();
    },

    // Called after amount/split confirmed
    proceedSale() {
      this.finalizeModalOpen = false;
      const trackable = this.cart.filter(i => i.track_serial);
      if (trackable.length > 0) { this.openSerialModal(trackable); return; }
      this.finishSubmit();
    },

    // ── IMEI / serial capture ───────────────────────────────────
    openSerialModal(trackable) {
      this.serialItems = trackable.map(i => ({ id: i.id, name: i.name, qty: i.qty }));
      this.serialInputs = {};
      this.serialItems.forEach(si => { this.serialInputs[si.id] = Array(si.qty).fill(''); });
      this.serialModalOpen = true;
      this.$nextTick(() => {
        const first = document.querySelector('[data-serial-idx="0"]');
        if (first) first.focus();
      });
    },

    serialInputIndex(productId, n) {
      let idx = 0;
      for (const si of this.serialItems) {
        if (si.id === productId) return idx + n;
        idx += si.qty;
      }
      return idx + n;
    },

    focusNextSerial(productId, n) {
      const next = document.querySelector('[data-serial-idx="' + (this.serialInputIndex(productId, n) + 1) + '"]');
      if (next) next.focus();
      else this.confirmSerials();
    },

    confirmSerials() {
      const serials = {};
      for (const si of this.serialItems) {
        const list = (this.serialInputs[si.id] || []).map(s => (s || '').trim()).filter(Boolean);
        if (list.length) serials[si.id] = list;
      }
      this.serials = serials;
      this.serialModalOpen = false;
      this.finishSubmit();
    },

    skipSerials() {
      this.serials = {};
      this.serialModalOpen = false;
      this.finishSubmit();
    },

    async finishSubmit() {
      if (this.cart.length === 0 || this.submitting) return;
      const payload = this.buildPayload();

      if (!navigator.onLine) { this.queueSale(payload); return; }

      this.submitting = true;
      try {
        const res = await this.postSale(payload);
        if (res.ok) {
          const json = await res.json();
          this.showSaleComplete(json);
          return;
        }
        // Server reached but rejected (e.g. validation / stock) — don't queue, show error
        let msg = 'Sale save nahi hui (HTTP ' + res.status + ')';
        try { const j = await res.json(); if (j.message) msg = j.message; } catch (e) {}
        this.flash(msg, false);
      } catch (e) {
        // Network failure — queue for later sync
        this.queueSale(payload);
      } finally {
        this.submitting = false;
      }
    },

    async syncQueue() {
      if (this.syncing || this.offlineQueue.length === 0 || !navigator.onLine) return;
      this.syncing = true;
      try {
        for (const payload of [...this.offlineQueue]) {
          try {
            const res = await this.postSale(payload);
            if (res.ok) {
              this.offlineQueue = this.offlineQueue.filter(q => q.client_uuid !== payload.client_uuid);
              this.saveQueue();
            }
            // Non-2xx: keep it queued, try again next time
          } catch (e) {
            break; // still offline — stop trying
          }
        }
        if (this.offlineQueue.length === 0) this.flash('Sab offline sales sync ho gayin ✓', true);
      } finally {
        this.syncing = false;
      }
    },

    get categories() {
      return [...new Set(this.products.map(p => p.category).filter(Boolean))].sort();
    },

    get filtered() {
      let list = this.products;
      if (this.activeCategory && !this.searchQ) list = list.filter(p => p.category === this.activeCategory);
      if (this.searchQ) {
        const q = this.searchQ.toLowerCase();
        list = list.filter(p =>
          p.name.toLowerCase().includes(q) ||
          (p.sku && p.sku.toLowerCase().includes(q)) ||
          (p.barcode && p.barcode.toLowerCase().includes(q)) ||
          (p.variant_group && p.variant_group.toLowerCase().includes(q)) ||
          (p.variant_name && p.variant_name.toLowerCase().includes(q))
        );
      }
      return list;
    },

    // Group variants (same variant_group) into one card; others stay single.
    get displayList() {
      const groups = {}; const result = [];
      for (const p of this.filtered) {
        if (this.hideOutOfStock && Number(p.stock_qty || 0) <= 0) continue;
        if (p.variant_group) {
          if (!groups[p.variant_group]) {
            groups[p.variant_group] = { isGroup: true, group: p.variant_group, variants: [], id: 'g:' + p.variant_group };
            result.push(groups[p.variant_group]);
          }
          groups[p.variant_group].variants.push(p);
        } else {
          result.push({ isGroup: false, product: p, id: 'p:' + p.id });
        }
      }
      return result.slice(0, 40);
    },

    // ── Card display helpers (work for both single + group) ──
    cardStock(e) { return e.isGroup ? e.variants.reduce((s, v) => s + Number(v.stock_qty || 0), 0) : e.product.stock_qty; },
    cardUnit(e)  { return e.isGroup ? (e.variants[0] ? e.variants[0].unit : '') : e.product.unit; },
    cardImg(e)   { if (e.isGroup) { const v = e.variants.find(x => x.image_url); return v ? v.image_url : null; } return e.product.image_url; },
    cardPrice(e) {
      const p = e.isGroup ? Math.min(...e.variants.map(v => this.priceFor(v))) : this.priceFor(e.product);
      return (e.isGroup ? 'from PKR ' : 'PKR ') + Number(p).toLocaleString();
    },
    cardStockLabel(e) { const s = this.cardStock(e); return s <= 0 ? 'Out' : s + ' ' + this.cardUnit(e); },
    cardName(e) { return e.isGroup ? e.group : e.product.name; },
    cardInitials(e) {
      const n = (this.cardName(e) || '?').trim();
      const parts = n.split(/\s+/);
      return (parts.length > 1 ? (parts[0][0] + parts[1][0]) : n.slice(0, 2)).toUpperCase();
    },
    cardTint(e) {
      const pal = [['#dcfce7','#15803d'],['#dbeafe','#1d4ed8'],['#fef3c7','#b45309'],['#fce7f3','#be185d'],['#ede9fe','#6d28d9'],['#ccfbf1','#0f766e'],['#ffedd5','#c2410c'],['#e0e7ff','#4338ca'],['#fee2e2','#b91c1c'],['#f0fdfa','#0d9488']];
      const n = this.cardName(e) || '';
      let h = 0; for (let i = 0; i < n.length; i++) h = (h * 31 + n.charCodeAt(i)) >>> 0;
      return pal[h % pal.length];
    },

    payLabel(m) {
      return ({ cash: 'Cash', online: 'Online', credit: 'Udhar', split: 'Split' })[m] || (m || '');
    },

    // ── Fullscreen ──
    toggleFullscreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
      } else {
        document.exitFullscreen().catch(() => {});
      }
    },

    // ── Calculator ──
    calcPress(k) {
      if (k === 'C')   { this.calcExpr = ''; return; }
      if (k === 'DEL') { this.calcExpr = (this.calcExpr === 'Error') ? '' : this.calcExpr.slice(0, -1); return; }
      if (k === '=')   { this.calcEval(); return; }
      // fresh start after an error or a completed result when a digit is typed
      if (this.calcExpr === 'Error') this.calcExpr = '';
      // don't allow two operators in a row
      if (['+','-','×','÷','%'].includes(k) && /[+\-×÷%]$/.test(this.calcExpr)) {
        this.calcExpr = this.calcExpr.slice(0, -1) + k;
      } else {
        this.calcExpr += k;
      }
    },
    calcEval() {
      try {
        let e = this.calcExpr.replace(/×/g, '*').replace(/÷/g, '/').replace(/%/g, '/100');
        e = e.replace(/[^0-9+\-*/.() ]/g, '').trim();
        if (!e || /[+\-*/.]$/.test(e)) return;   // ignore trailing operator
        let r = Function('"use strict";return (' + e + ')')();
        if (r === undefined || r === null || !isFinite(r)) { this.calcExpr = 'Error'; return; }
        this.calcExpr = String(Math.round((r + Number.EPSILON) * 10000) / 10000);
      } catch (err) { this.calcExpr = 'Error'; }
    },
    calcKey(e) {
      if (!this.calcOpen) return;
      const k = e.key;
      let handled = true;
      if (k >= '0' && k <= '9') this.calcPress(k);
      else if (k === '.') this.calcPress('.');
      else if (k === '+' || k === '-') this.calcPress(k);
      else if (k === '*') this.calcPress('×');
      else if (k === '/') this.calcPress('÷');
      else if (k === '%') this.calcPress('%');
      else if (k === 'Enter' || k === '=') this.calcPress('=');
      else if (k === 'Backspace') this.calcPress('DEL');
      else if (k === 'Escape') this.calcOpen = false;
      else if (k === 'c' || k === 'C') this.calcPress('C');
      else handled = false;
      if (handled) { e.preventDefault(); e.stopPropagation(); }
    },

    // ── Variant picker ──
    openVariantPicker(entry) {
      this.variantGroupName = entry.group;
      this.variantOptions = entry.variants;
      this.variantModalOpen = true;
    },
    pickVariant(v) {
      this.variantModalOpen = false;
      this.addToCart(v);
      this.flash('' + (v.variant_name || v.name), true);
    },

    get subtotal() { return this.cart.reduce((s, i) => s + i.qty * i.price, 0); },
    get discountAmount() {
      const v = Number(this.discount) || 0;
      const amt = this.discountType === 'percent'
        ? Math.round(this.subtotal * Math.min(Math.max(v, 0), 100) / 100)
        : Math.max(v, 0);
      return Math.min(this.subtotal, amt);
    },
    get total()    { return Math.max(0, this.subtotal - this.discountAmount); },
    get splitPaid() { return (Number(this.cashAmount) || 0) + (Number(this.onlineAmount) || 0); },
    get splitUdhar() { return Number(this.udharAmount) || 0; },
    get splitAllocated() { return this.splitPaid + this.splitUdhar; },
    get quickCash() {
      const t = this.total; const opts = new Set();
      [100, 500, 1000, 5000].forEach(step => opts.add(Math.ceil(t / step) * step));
      return [...opts].filter(v => v > t).sort((a, b) => a - b).slice(0, 3);
    },
    get paidTotal() {
      if (this.payMethod === 'split')  return this.splitPaid;      // cash + online part
      if (this.payMethod === 'credit') return 0;                   // full Udhar — nothing paid now
      return Number(this.receivedAmount) || 0;
    },
    get change()   { return Math.max(0, this.paidTotal - this.total); },
    setFullPay()   { this.receivedAmount = this.total; },

    // Effective price for new adds: wholesale rate when toggled (fallback to retail)
    priceFor(p) {
      return (this.priceMode === 'wholesale' && p.wholesale_price)
        ? parseFloat(p.wholesale_price)
        : parseFloat(p.sale_price);
    },

    addToCart(p) {
      if (p.stock_qty <= 0) return;
      const ex = this.cart.find(i => i.id == p.id);
      if (ex) { if (ex.qty < p.stock_qty) ex.qty++; }
      else {
        const nm = (p.variant_group && p.variant_name) ? (p.variant_group + ' — ' + p.variant_name) : p.name;
        this.cart.push({ id: p.id, name: nm, unit: p.unit, price: this.priceFor(p), qty: 1, stock: p.stock_qty, track_serial: !!p.track_serial });
      }
    },

    // ── Hold / Resume (Open Tabs) ───────────────────────────────
    openHoldModal() {
      if (this.cart.length === 0) return;
      if (!this.holdCustomerName) this.holdCustomerName = this.customerName;
      this.holdModalOpen = true;
      this.$nextTick(() => this.$refs.holdNameInput && this.$refs.holdNameInput.focus());
    },

    async confirmHold() {
      if (this.holdSaving || !this.holdCustomerName.trim()) return;
      this.holdSaving = true;
      try {
        const res = await fetch('{{ route('pos.hold') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({
            hold_id:        this.holdId || null,
            customer_name:  this.holdCustomerName.trim(),
            customer_phone: this.holdCustomerPhone.trim() || null,
            notes:          this.holdNote.trim() || null,
            items:          this.cart.map(i => ({ product_id: i.id, name: i.name, qty: i.qty, price: i.price, unit: i.unit })),
          }),
        });
        if (!res.ok) {
          let msg = 'Hold save nahi hua (HTTP ' + res.status + ')';
          try { const j = await res.json(); if (j.message) msg = j.message; } catch (e) {}
          this.flash(msg, false);
          return;
        }
        const json = await res.json();
        this.heldSales = json.holds || this.heldSales;
        this.holdModalOpen = false;
        this.holdCustomerName = ''; this.holdCustomerPhone = ''; this.holdNote = '';
        this.resetSale();
        this.flash('Sale hold ho gayi — ' + json.tab_number, true);
      } catch (e) {
        this.flash('Net nahi hai — hold ke liye internet zaroori hai', false);
      } finally {
        this.holdSaving = false;
      }
    },

    async resumeHold(h) {
      if (this.cart.length > 0 && !confirm('Cart mein pehle se items hain — unko hata kar yeh hold load karein?')) return;
      try {
        const res = await fetch('{{ url('/pos/hold') }}/' + h.id, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) { this.flash('Hold load nahi hua (HTTP ' + res.status + ')', false); return; }
        const json = await res.json();
        this.cart = (json.items || []).map(it => {
          const p = this.products.find(pr => pr.id == it.product_id);
          return {
            id:           it.product_id,
            name:         it.name,
            unit:         it.unit || (p ? p.unit : 'pcs'),
            price:        parseFloat(it.price),
            qty:          Math.max(1, Math.round(Number(it.qty))),
            stock:        p ? p.stock_qty : Number(it.qty),
            track_serial: p ? !!p.track_serial : false,
          };
        });
        this.holdId = json.id;
        this.holdTabNumber = json.tab_number;
        this.customerName = json.customer_name || '';
        this.customerPhone = json.customer_phone || '';
        this.holdCustomerName = json.customer_name || '';
        this.holdCustomerPhone = json.customer_phone || '';
        this.holdNote = json.notes || '';
        this.holdsPanelOpen = false;
        this.flash('' + json.tab_number + ' resume ho gaya — ' + (json.customer_name || ''), true);
      } catch (e) {
        this.flash('Net nahi hai — hold load nahi hua', false);
      }
    },

    async deleteHold(h) {
      this.confirmDeleteId = null;
      try {
        const res = await fetch('{{ url('/pos/hold') }}/' + h.id, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
        });
        if (!res.ok) { this.flash('Hold delete nahi hua (HTTP ' + res.status + ')', false); return; }
        const json = await res.json();
        this.heldSales = json.holds || this.heldSales.filter(x => x.id !== h.id);
        if (this.holdId === h.id) { this.holdId = null; this.holdTabNumber = ''; }
        this.flash('Hold delete ho gaya', true);
      } catch (e) {
        this.flash('Net nahi hai — hold delete nahi hua', false);
      }
    },

    timeAgo(iso) {
      if (!iso) return '';
      const mins = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 60000));
      if (mins < 1) return 'abhi abhi';
      if (mins < 60) return mins + ' min pehle';
      const hrs = Math.floor(mins / 60);
      if (hrs < 24) return hrs + ' ghante pehle';
      return Math.floor(hrs / 24) + ' din pehle';
    },

    removeFromCart(id) { this.cart = this.cart.filter(i => i.id != id); },

    tryBarcodeEnter() {
      const q = this.searchQ.trim();
      if (!q) return;
      const exact = this.products.find(p => (p.barcode && p.barcode === q) || (p.sku && p.sku === q));
      if (exact) { this.addToCart(exact); this.flash('' + exact.name, true); this.searchQ = ''; }
    },

    addByBarcode(code) {
      const p = this.products.find(p => (p.barcode && p.barcode === code) || (p.sku && p.sku === code));
      if (p) {
        if (p.stock_qty <= 0) { this.flash('Out of stock: ' + p.name, false); return; }
        this.addToCart(p); this.flash('' + p.name, true);
      } else { this.flash('Not found: ' + code, false); }
    },

    flash(msg, ok) {
      // Route POS flashes through the unified top-right toast for a consistent look.
      if (window.showToast) { window.showToast(msg, ok ? 'success' : 'error', 2600); return; }
      this.scanMsg = msg; this.scanOk = ok;
      clearTimeout(this.scanTimer);
      this.scanTimer = setTimeout(() => { this.scanMsg = ''; }, 2200);
    },

    loadScannerLib() {
      if (window.Html5Qrcode) return Promise.resolve();
      if (window.__html5QrcodeLoading) return window.__html5QrcodeLoading;
      window.__html5QrcodeLoading = new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
        s.onload = resolve;
        s.onerror = () => reject(new Error('load failed'));
        document.head.appendChild(s);
      });
      return window.__html5QrcodeLoading;
    },

    async openCamera() {
      if (!this.scannerEnabled) return;
      this.cameraOpen = true; this.lastCameraResult = ''; this.cameraError = '';
      this.cameraStatus = 'Scanner load ho raha hai...';
      try {
        await this.loadScannerLib();
      } catch (e) {
        this.cameraError = 'Scanner library load nahi hui. Internet connection check karein aur dobara try karein.';
        this.cameraStatus = 'Error';
        return;
      }
      this.cameraStatus = 'Camera permission maang raha hai...';
      try {
        this.html5Qr = new Html5Qrcode('pos-camera-reader');
        await this.html5Qr.start(
          { facingMode: 'environment' },
          { fps: 10, qrbox: { width: 220, height: 140 } },
          (decodedText) => this.onCameraScan(decodedText),
          () => {}
        );
        this.cameraStatus = 'Scanning...';
      } catch (e) {
        this.html5Qr = null;
        this.cameraError = 'Camera access denied. Browser settings mein camera permission allow karein, phir dobara try karein.';
        this.cameraStatus = 'Error';
      }
    },

    onCameraScan(code) {
      if (!this.html5Qr) return;
      this.lastCameraResult = code;
      this.closeCamera();
      this.addByBarcode(code);
    },

    closeCamera() {
      const qr = this.html5Qr;
      this.html5Qr = null;
      if (qr) {
        qr.stop().then(() => qr.clear()).catch(() => {});
      }
      this.cameraOpen = false;
    },
  };
}
</script>
@endsection
