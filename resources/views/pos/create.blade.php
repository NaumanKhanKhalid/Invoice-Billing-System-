@extends('layouts.app')
@section('title','POS Counter')
@section('content')
<script>window.__POS_PRODUCTS__ = @json($products);</script>

<style>
  #main-content { padding: 0 !important; display: flex; flex-direction: column; overflow: hidden; flex: 1; min-height: 0; }
</style>

<div class="flex flex-1 min-h-0 bg-slate-100" style="height:100%" x-data="posApp()" x-init="init()">

  {{-- ══ LEFT: Products ══ --}}
  <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

    {{-- Top bar --}}
    <div class="bg-white border-b border-slate-200 px-4 py-2.5 flex items-center gap-3 shrink-0">
      <a href="{{ route('pos.index') }}"
         class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      </a>

      <div class="relative flex-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" x-ref="mainInput" x-model="searchQ"
               @keydown.enter.prevent="tryBarcodeEnter()"
               placeholder="Search products or scan barcode..."
               class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-300 focus:bg-white outline-none transition"
               autofocus>
      </div>

      <button type="button" @click="openCamera()" x-show="cameraSupported"
              class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><rect x="7" y="7" width="10" height="10" rx="1"/></svg>
        Scan
      </button>

      <a href="{{ route('pos.index') }}" class="text-xs text-slate-400 hover:text-slate-600 shrink-0 hidden lg:block whitespace-nowrap">History →</a>
    </div>

    {{-- Scan toast --}}
    <div x-show="scanMsg" x-transition.opacity
         :class="scanOk ? 'bg-emerald-500' : 'bg-red-500'"
         class="mx-4 mt-2.5 mb-0 px-4 py-2 rounded-xl text-white text-sm font-medium flex items-center gap-2 shadow-lg shrink-0">
      <span x-text="scanMsg"></span>
    </div>

    {{-- Category tabs --}}
    <div class="px-4 pt-3 pb-0 flex gap-2 flex-wrap shrink-0" x-show="categories.length > 0 && !searchQ">
      <button @click="activeCategory = null"
              :class="activeCategory === null ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 hover:bg-slate-50'"
              class="px-3 py-1 rounded-lg text-xs font-semibold border border-slate-200 transition">All</button>
      <template x-for="cat in categories" :key="cat">
        <button @click="activeCategory = (activeCategory === cat ? null : cat)"
                :class="activeCategory === cat ? 'bg-green-600 text-white border-green-600' : 'bg-white text-slate-600 hover:bg-slate-50'"
                class="px-3 py-1 rounded-lg text-xs font-semibold border border-slate-200 transition"
                x-text="cat"></button>
      </template>
    </div>

    {{-- Product grid --}}
    <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3">
        <template x-for="p in filtered" :key="p.id">
          <button type="button" @click="addToCart(p)"
                  :disabled="p.stock_qty <= 0"
                  class="group relative text-left rounded-2xl border-2 bg-white transition-all duration-150 overflow-hidden"
                  :class="p.stock_qty <= 0
                    ? 'border-slate-100 opacity-50 cursor-not-allowed'
                    : 'border-transparent hover:border-green-400 hover:shadow-md cursor-pointer active:scale-95'">

            <div class="h-1.5 w-full shrink-0"
                 :class="p.stock_qty <= 0 ? 'bg-red-300' : (p.stock_qty <= 5 ? 'bg-amber-400' : 'bg-green-400')"></div>

            <div class="p-3">
              <p class="text-sm font-bold text-slate-900 leading-tight line-clamp-2" style="min-height:2.5rem" x-text="p.name"></p>
              <p class="text-[10px] text-slate-400 mt-0.5 font-mono truncate" x-text="p.sku || ''"></p>
              <p class="text-base font-extrabold text-green-600 mt-2" x-text="'PKR ' + Number(p.sale_price).toLocaleString()"></p>
              <div class="mt-1.5 flex items-center justify-between">
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                      :class="p.stock_qty <= 0 ? 'bg-red-100 text-red-600' : (p.stock_qty <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500')"
                      x-text="p.stock_qty <= 0 ? 'Out of stock' : p.stock_qty + ' ' + p.unit"></span>
                <span class="opacity-0 group-hover:opacity-100 transition-opacity w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold">+</span>
              </div>
            </div>
          </button>
        </template>

        <template x-if="filtered.length === 0">
          <div class="col-span-5 flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <p class="text-slate-400 text-sm font-medium">Koi product nahi mila</p>
          </div>
        </template>
      </div>

      {{-- Today at a Glance --}}
      <div class="grid grid-cols-3 gap-3 mt-auto pt-2">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          </div>
          <div>
            <p class="text-xs text-slate-400 font-medium">Today's Sales</p>
            <p class="text-xl font-extrabold text-slate-900">{{ $todaySales }}</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div>
            <p class="text-xs text-slate-400 font-medium">Today's Revenue</p>
            <p class="text-xl font-extrabold text-slate-900">{{ number_format($todayRevenue) }}</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-{{ $lowStock > 0 ? 'amber' : 'slate' }}-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-{{ $lowStock > 0 ? 'amber' : 'slate' }}-50 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-{{ $lowStock > 0 ? 'amber-600' : 'slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
          </div>
          <div>
            <p class="text-xs text-slate-400 font-medium">Low Stock</p>
            <p class="text-xl font-extrabold text-{{ $lowStock > 0 ? 'amber-600' : 'slate-900' }}">{{ $lowStock }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Bottom status --}}
    <div class="bg-white border-t border-slate-200 px-4 py-2 flex items-center gap-3 text-xs text-slate-400 shrink-0">
      <span x-text="products.length + ' products'"></span>
      <span>·</span>
      <span x-text="filtered.length + ' shown'"></span>
      <span x-show="cart.length > 0" class="ml-auto text-green-600 font-semibold" x-text="cart.length + ' item(s) in cart'"></span>
    </div>
  </div>

  {{-- ══ RIGHT: Cart + Checkout ══ --}}
  <div class="w-80 xl:w-96 flex flex-col bg-slate-900 shrink-0 border-l border-slate-800 overflow-hidden">

    {{-- Cart header --}}
    <div class="px-5 py-3.5 border-b border-slate-800 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
        <span class="text-white font-bold text-sm">Cart</span>
        <span x-show="cart.length > 0"
              class="bg-green-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center"
              x-text="cart.length"></span>
      </div>
      <button @click="cart = []" x-show="cart.length > 0"
              class="text-slate-500 hover:text-red-400 transition text-xs font-medium flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        Clear
      </button>
    </div>

    {{-- Cart items (scrollable) --}}
    <div class="overflow-y-auto py-2 px-3 space-y-2" style="flex:1 1 0; min-height:0;">
      <template x-if="cart.length === 0">
        <div class="flex flex-col items-center justify-center h-full py-10 text-center">
          <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          </div>
          <p class="text-slate-500 text-sm">Cart khali hai</p>
          <p class="text-slate-600 text-xs mt-1">Product card click karein</p>
        </div>
      </template>

      <template x-for="(item, idx) in cart" :key="item.id">
        <div class="bg-slate-800 rounded-xl p-3 flex gap-3 items-start">
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm font-semibold leading-snug" x-text="item.name"></p>
            <p class="text-green-400 text-xs mt-0.5 font-medium" x-text="'PKR ' + Number(item.price).toLocaleString() + ' / ' + item.unit"></p>
          </div>
          <div class="flex flex-col items-end gap-1.5 shrink-0">
            <div class="flex items-center gap-1">
              <button type="button" @click="item.qty > 1 ? item.qty-- : removeFromCart(item.id)"
                      class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold flex items-center justify-center transition text-base leading-none">−</button>
              <span class="text-white font-bold text-sm w-7 text-center tabular-nums" x-text="item.qty"></span>
              <button type="button" @click="item.qty < item.stock ? item.qty++ : null"
                      :disabled="item.qty >= item.stock"
                      class="w-7 h-7 rounded-lg bg-green-600 hover:bg-green-500 text-white font-bold flex items-center justify-center transition text-base leading-none disabled:opacity-30">+</button>
            </div>
            <p class="text-white font-extrabold text-sm tabular-nums" x-text="'PKR ' + (item.qty * item.price).toLocaleString()"></p>
          </div>
        </div>
      </template>
    </div>

    {{-- Checkout (fixed bottom) --}}
    <div class="shrink-0 border-t border-slate-800">
      <form method="POST" action="{{ route('pos.store') }}">
        @csrf
        <template x-for="(item, idx) in cart">
          <div>
            <input type="hidden" :name="'items['+idx+'][product_id]'" :value="item.id">
            <input type="hidden" :name="'items['+idx+'][qty]'" :value="item.qty">
            <input type="hidden" :name="'items['+idx+'][unit_price]'" :value="item.price">
          </div>
        </template>

        {{-- Totals --}}
        <div class="px-5 pt-3 pb-2 space-y-1.5">
          <div class="flex justify-between text-sm text-slate-400">
            <span>Subtotal</span>
            <span x-text="'PKR ' + subtotal.toLocaleString()" class="tabular-nums"></span>
          </div>
          <div class="flex justify-between items-center text-sm text-slate-400">
            <span>Discount</span>
            <div class="flex items-center gap-1.5">
              <span class="text-slate-500 text-xs">PKR</span>
              <input type="number" name="discount" x-model="discount" min="0" step="1"
                     class="w-20 text-right px-2 py-1 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm outline-none focus:border-green-500 transition tabular-nums">
            </div>
          </div>
          <div class="flex justify-between items-baseline pt-1.5 border-t border-slate-800">
            <span class="text-slate-300 font-semibold text-sm">Total</span>
            <span class="text-white text-xl font-extrabold tabular-nums" x-text="'PKR ' + total.toLocaleString()"></span>
          </div>
        </div>

        {{-- Payment method --}}
        <div class="px-5 pb-2">
          <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-1.5">Payment Method</p>
          <input type="hidden" name="payment_method" x-model="payMethod">
          <div class="grid grid-cols-3 gap-1.5">
            <template x-for="pm in payMethods" :key="pm.value">
              <button type="button" @click="payMethod = pm.value"
                      :class="payMethod === pm.value
                        ? 'bg-green-600 border-green-500 text-white'
                        : 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-600'"
                      class="py-1.5 px-1 rounded-xl border text-center transition">
                <span x-text="pm.icon" class="block text-sm"></span>
                <span x-text="pm.label" class="text-[10px] font-semibold leading-none mt-0.5 block"></span>
              </button>
            </template>
          </div>
        </div>

        {{-- Customer + Cash --}}
        <div class="px-5 pb-2 space-y-2">
          <input type="text" name="customer_name" placeholder="Customer name (optional)"
                 class="w-full px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 outline-none focus:border-green-500 transition">

          <div class="flex gap-2">
            <input type="number" name="amount_paid" x-model="amountPaid" min="0" step="0.01" required
                   placeholder="Cash received..."
                   class="flex-1 min-w-0 px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm outline-none focus:border-green-500 transition tabular-nums">
            <button type="button" @click="setFullPay()"
                    class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-semibold rounded-xl transition whitespace-nowrap">Exact</button>
          </div>

          <div x-show="change > 0" class="flex justify-between items-center bg-emerald-950 border border-emerald-800 rounded-xl px-3 py-1.5">
            <span class="text-emerald-400 text-sm font-semibold">Change</span>
            <span class="text-emerald-300 font-extrabold text-sm tabular-nums" x-text="'PKR ' + change.toLocaleString()"></span>
          </div>
        </div>

        {{-- Submit --}}
        <div class="px-4 pb-4">
          <button type="submit" :disabled="cart.length === 0"
                  class="w-full bg-green-600 hover:bg-green-500 disabled:bg-slate-700 disabled:text-slate-500 text-white py-3 rounded-2xl font-extrabold text-sm transition flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span x-text="cart.length === 0 ? 'Add items to cart' : 'Complete Sale — PKR ' + total.toLocaleString()"></span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Camera Modal --}}
  <div x-show="cameraOpen" x-transition
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
       @keydown.escape.window="closeCamera()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="font-bold text-slate-900">Camera Scanner</h3>
          <p class="text-xs text-slate-400 mt-0.5" x-text="cameraStatus"></p>
        </div>
        <button @click="closeCamera()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 transition">✕</button>
      </div>
      <div class="relative bg-black">
        <video x-ref="cameraVideo" autoplay playsinline muted class="w-full aspect-video object-cover"></video>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div class="w-52 h-32 relative">
            <span class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-green-400"></span>
            <span class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-green-400"></span>
            <span class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-green-400"></span>
            <span class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-green-400"></span>
            <div class="absolute inset-x-4 top-1/2 h-px bg-green-400 opacity-80 animate-pulse"></div>
          </div>
        </div>
      </div>
      <div class="px-5 py-4 text-center">
        <p class="text-xs text-slate-500">Barcode ko camera ke saamne rakhen</p>
        <p x-show="lastCameraResult" class="mt-1.5 text-sm font-mono font-bold text-green-700" x-text="lastCameraResult"></p>
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
    discount: 0,
    amountPaid: 0,
    payMethod: 'cash',
    payMethods: [
      { value: 'cash',      label: 'Cash',      icon: '💵' },
      { value: 'jazzcash',  label: 'JazzCash',  icon: '📱' },
      { value: 'easypaisa', label: 'EasyPaisa', icon: '📱' },
      { value: 'bank',      label: 'Bank',      icon: '🏦' },
      { value: 'credit',    label: 'Udhar',     icon: '📋' },
    ],
    scanMsg: '', scanOk: true, scanTimer: null,
    cameraOpen: false, cameraSupported: false,
    cameraStatus: '', lastCameraResult: '',
    cameraStream: null, barcodeDetector: null, scanInterval: null,

    init() { this.cameraSupported = 'BarcodeDetector' in window; },

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
          (p.barcode && p.barcode.toLowerCase().includes(q))
        );
      }
      return list.slice(0, 40);
    },

    get subtotal() { return this.cart.reduce((s, i) => s + i.qty * i.price, 0); },
    get total()    { return Math.max(0, this.subtotal - Number(this.discount || 0)); },
    get change()   { return Math.max(0, Number(this.amountPaid || 0) - this.total); },
    setFullPay()   { this.amountPaid = this.total; },

    addToCart(p) {
      if (p.stock_qty <= 0) return;
      const ex = this.cart.find(i => i.id == p.id);
      if (ex) { if (ex.qty < p.stock_qty) ex.qty++; }
      else this.cart.push({ id: p.id, name: p.name, unit: p.unit, price: parseFloat(p.sale_price), qty: 1, stock: p.stock_qty });
    },

    removeFromCart(id) { this.cart = this.cart.filter(i => i.id != id); },

    tryBarcodeEnter() {
      const q = this.searchQ.trim();
      if (!q) return;
      const exact = this.products.find(p => (p.barcode && p.barcode === q) || (p.sku && p.sku === q));
      if (exact) { this.addToCart(exact); this.flash('✓ ' + exact.name, true); this.searchQ = ''; }
    },

    addByBarcode(code) {
      const p = this.products.find(p => (p.barcode && p.barcode === code) || (p.sku && p.sku === code));
      if (p) {
        if (p.stock_qty <= 0) { this.flash('Out of stock: ' + p.name, false); return; }
        this.addToCart(p); this.flash('✓ ' + p.name, true);
      } else { this.flash('Not found: ' + code, false); }
    },

    flash(msg, ok) {
      this.scanMsg = msg; this.scanOk = ok;
      clearTimeout(this.scanTimer);
      this.scanTimer = setTimeout(() => { this.scanMsg = ''; }, 2200);
    },

    async openCamera() {
      if (!this.cameraSupported) return;
      this.cameraOpen = true; this.lastCameraResult = '';
      this.cameraStatus = 'Camera permission maang raha hai...';
      try {
        this.cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } } });
        this.$refs.cameraVideo.srcObject = this.cameraStream;
        this.cameraStatus = 'Scanning...';
        this.barcodeDetector = new BarcodeDetector({ formats: ['ean_13','ean_8','code_128','code_39','qr_code','upc_a','upc_e'] });
        this.scanInterval = setInterval(() => this.detectFrame(), 250);
      } catch (e) { this.cameraStatus = 'Camera access deny'; }
    },

    async detectFrame() {
      if (!this.$refs.cameraVideo || !this.barcodeDetector) return;
      try {
        const codes = await this.barcodeDetector.detect(this.$refs.cameraVideo);
        if (codes.length > 0) {
          const code = codes[0].rawValue;
          this.lastCameraResult = code;
          this.closeCamera();
          this.addByBarcode(code);
        }
      } catch (_) {}
    },

    closeCamera() {
      clearInterval(this.scanInterval); this.scanInterval = null;
      if (this.cameraStream) { this.cameraStream.getTracks().forEach(t => t.stop()); this.cameraStream = null; }
      this.cameraOpen = false;
    },
  };
}
</script>
@endsection
