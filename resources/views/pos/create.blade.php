@extends('layouts.app')
@section('title','Point of Sale')
@section('content')
<script>window.__POS_PRODUCTS__ = @json($products);</script>

<style>
  /* Override layout padding for full-screen POS */
  #main-content { padding: 0 !important; }
</style>

<div class="flex h-[calc(100vh-4rem)] bg-slate-100" x-data="posApp()" x-init="init()">

  {{-- ══ LEFT: Products ══ --}}
  <div class="flex flex-col flex-1 min-w-0">

    {{-- Top bar --}}
    <div class="bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-3">
      <a href="{{ route('pos.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-700 shrink-0">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>

      {{-- Unified search / barcode --}}
      <div class="relative flex-1">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="text" x-ref="mainInput" x-model="searchQ"
               @keydown.enter.prevent="tryBarcodeEnter()"
               placeholder="Search products or scan barcode..."
               class="w-full pl-9 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-300 focus:bg-white outline-none transition"
               autofocus>
      </div>

      <button type="button" @click="openCamera()" x-show="cameraSupported"
              class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition shrink-0">
        <i data-lucide="scan-barcode" class="w-4 h-4"></i> Scan
      </button>

      <a href="{{ route('pos.index') }}" class="text-xs text-slate-400 hover:text-slate-600 shrink-0 hidden sm:block">History →</a>
    </div>

    {{-- Scan toast --}}
    <div x-show="scanMsg" x-transition.opacity
         :class="scanOk ? 'bg-emerald-500' : 'bg-red-500'"
         class="mx-4 mt-3 px-4 py-2 rounded-xl text-white text-sm font-medium flex items-center gap-2 shadow-lg">
      <i :data-lucide="scanOk ? 'check' : 'x'" class="w-4 h-4 shrink-0"></i>
      <span x-text="scanMsg"></span>
    </div>

    {{-- Product grid --}}
    <div class="flex-1 overflow-y-auto p-4">
      {{-- Category filter tabs --}}
      <div class="flex gap-2 mb-3 flex-wrap" x-show="!searchQ">
        <button @click="activeCategory = null"
                :class="activeCategory === null ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 transition">All</button>
        <template x-for="cat in categories" :key="cat">
          <button @click="activeCategory = (activeCategory === cat ? null : cat)"
                  :class="activeCategory === cat ? 'bg-green-600 text-white border-green-600' : 'bg-white text-slate-600 hover:bg-slate-50'"
                  class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 transition"
                  x-text="cat"></button>
        </template>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        <template x-for="p in filtered" :key="p.id">
          <button type="button" @click="addToCart(p)"
                  :disabled="p.stock_qty <= 0"
                  class="group relative text-left rounded-2xl border-2 bg-white transition-all duration-150 overflow-hidden"
                  :class="p.stock_qty <= 0
                    ? 'border-slate-100 opacity-50 cursor-not-allowed'
                    : 'border-transparent hover:border-green-400 hover:shadow-md cursor-pointer active:scale-95'">

            {{-- Color accent strip --}}
            <div class="h-1.5 w-full" :class="stockColor(p)"></div>

            <div class="p-3">
              {{-- Product name --}}
              <p class="text-sm font-bold text-slate-900 leading-tight line-clamp-2 min-h-[2.5rem]" x-text="p.name"></p>

              {{-- SKU --}}
              <p class="text-[10px] text-slate-400 mt-1 font-mono truncate" x-text="p.sku || ''"></p>

              {{-- Price --}}
              <p class="text-base font-extrabold text-green-600 mt-2" x-text="'PKR ' + Number(p.sale_price).toLocaleString()"></p>

              {{-- Stock badge --}}
              <div class="mt-1.5 flex items-center justify-between">
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                      :class="p.stock_qty <= 0 ? 'bg-red-100 text-red-600' : (p.stock_qty <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500')"
                      x-text="p.stock_qty <= 0 ? 'Out of stock' : p.stock_qty + ' ' + p.unit"></span>

                {{-- Add badge on hover --}}
                <span class="opacity-0 group-hover:opacity-100 transition w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold">+</span>
              </div>
            </div>
          </button>
        </template>

        <template x-if="filtered.length === 0">
          <div class="col-span-5 flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
              <i data-lucide="package-x" class="w-7 h-7 text-slate-300"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium">Koi product nahi mila</p>
            <p class="text-slate-300 text-xs mt-1">Search change karein ya product add karein</p>
          </div>
        </template>
      </div>
    </div>

    {{-- Bottom status bar --}}
    <div class="bg-white border-t border-slate-200 px-4 py-2 flex items-center gap-4 text-xs text-slate-400">
      <span x-text="products.length + ' products'"></span>
      <span>·</span>
      <span x-text="filtered.length + ' shown'"></span>
      <span x-show="cart.length > 0" class="ml-auto text-green-600 font-semibold" x-text="cart.length + ' item(s) in cart'"></span>
    </div>
  </div>

  {{-- ══ RIGHT: Cart + Checkout ══ --}}
  <div class="w-80 xl:w-96 flex flex-col bg-slate-900 shrink-0 border-l border-slate-800">

    {{-- Cart header --}}
    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i data-lucide="shopping-cart" class="w-4 h-4 text-green-400"></i>
        <span class="text-white font-bold text-sm">Cart</span>
        <span x-show="cart.length > 0"
              class="bg-green-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center"
              x-text="cart.length"></span>
      </div>
      <button @click="cart = []" x-show="cart.length > 0"
              class="text-slate-500 hover:text-red-400 transition text-xs font-medium flex items-center gap-1">
        <i data-lucide="trash-2" class="w-3 h-3"></i> Clear
      </button>
    </div>

    {{-- Cart items --}}
    <div class="flex-1 overflow-y-auto py-3 px-3 space-y-2">
      <template x-if="cart.length === 0">
        <div class="flex flex-col items-center justify-center h-full text-center py-12">
          <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mb-3">
            <i data-lucide="shopping-bag" class="w-8 h-8 text-slate-600"></i>
          </div>
          <p class="text-slate-500 text-sm">Cart khali hai</p>
          <p class="text-slate-600 text-xs mt-1">Products click karein ya barcode scan karein</p>
        </div>
      </template>

      <template x-for="(item, idx) in cart" :key="item.id">
        <div class="bg-slate-800 rounded-xl p-3 flex gap-3">
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm font-semibold leading-tight truncate" x-text="item.name"></p>
            <p class="text-green-400 text-xs mt-0.5 font-medium" x-text="'PKR ' + Number(item.price).toLocaleString() + ' / ' + item.unit"></p>
          </div>
          <div class="flex flex-col items-end gap-1.5 shrink-0">
            <div class="flex items-center gap-1.5">
              <button type="button" @click="item.qty > 1 ? item.qty-- : removeFromCart(item.id)"
                      class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold text-sm flex items-center justify-center transition">−</button>
              <span class="text-white font-bold text-sm w-7 text-center" x-text="item.qty"></span>
              <button type="button" @click="item.qty < item.stock ? item.qty++ : null"
                      :disabled="item.qty >= item.stock"
                      class="w-7 h-7 rounded-lg bg-green-600 hover:bg-green-500 text-white font-bold text-sm flex items-center justify-center transition disabled:opacity-30">+</button>
            </div>
            <p class="text-white font-extrabold text-sm" x-text="'PKR ' + (item.qty * item.price).toLocaleString()"></p>
          </div>
        </div>
      </template>
    </div>

    {{-- Checkout form --}}
    <form method="POST" action="{{ route('pos.store') }}" class="border-t border-slate-800 bg-slate-900">
      @csrf
      <template x-for="(item, idx) in cart">
        <div>
          <input type="hidden" :name="'items['+idx+'][product_id]'" :value="item.id">
          <input type="hidden" :name="'items['+idx+'][qty]'" :value="item.qty">
          <input type="hidden" :name="'items['+idx+'][unit_price]'" :value="item.price">
        </div>
      </template>

      {{-- Totals --}}
      <div class="px-5 pt-4 pb-3 space-y-1.5">
        <div class="flex justify-between text-sm text-slate-400">
          <span>Subtotal</span>
          <span x-text="'PKR ' + subtotal.toLocaleString()"></span>
        </div>
        <div class="flex justify-between items-center text-sm text-slate-400">
          <span>Discount</span>
          <div class="flex items-center gap-1">
            <span class="text-slate-500 text-xs">PKR</span>
            <input type="number" name="discount" x-model="discount" min="0" step="1"
                   class="w-24 text-right px-2 py-1 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm outline-none focus:border-green-500 transition">
          </div>
        </div>
        <div class="flex justify-between items-baseline pt-2 border-t border-slate-800">
          <span class="text-slate-300 font-semibold">Total</span>
          <span class="text-white text-2xl font-extrabold" x-text="'PKR ' + total.toLocaleString()"></span>
        </div>
      </div>

      {{-- Payment method --}}
      <div class="px-5 pb-3">
        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Payment Method</p>
        <input type="hidden" name="payment_method" x-model="payMethod">
        <div class="grid grid-cols-3 gap-1.5">
          <template x-for="pm in payMethods" :key="pm.value">
            <button type="button" @click="payMethod = pm.value"
                    :class="payMethod === pm.value ? 'bg-green-600 border-green-500 text-white' : 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-500'"
                    class="py-2 px-1 rounded-xl border text-xs font-semibold transition text-center leading-tight">
              <span x-text="pm.icon" class="block text-base mb-0.5"></span>
              <span x-text="pm.label"></span>
            </button>
          </template>
        </div>
      </div>

      {{-- Customer + Cash --}}
      <div class="px-5 pb-3 space-y-2.5">
        <input type="text" name="customer_name" placeholder="Customer name (optional)"
               class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm placeholder-slate-500 outline-none focus:border-green-500 transition">

        <div class="flex gap-2 items-center">
          <input type="number" name="amount_paid" x-model="amountPaid" min="0" step="0.01" required
                 placeholder="Cash received..."
                 class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm outline-none focus:border-green-500 transition">
          <button type="button" @click="setFullPay()"
                  class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-semibold rounded-xl transition whitespace-nowrap">
            Exact
          </button>
        </div>

        {{-- Change --}}
        <div x-show="change > 0" class="flex justify-between items-center bg-emerald-950 border border-emerald-800 rounded-xl px-3 py-2">
          <span class="text-emerald-400 text-sm font-semibold">Change</span>
          <span class="text-emerald-300 font-extrabold" x-text="'PKR ' + change.toLocaleString()"></span>
        </div>
      </div>

      {{-- Submit --}}
      <div class="px-5 pb-5">
        <button type="submit" :disabled="cart.length === 0"
                class="w-full bg-green-600 hover:bg-green-500 disabled:bg-slate-700 disabled:text-slate-500 text-white py-3.5 rounded-2xl font-extrabold text-base transition flex items-center justify-center gap-2">
          <i data-lucide="check-circle" class="w-5 h-5"></i>
          <span x-text="cart.length === 0 ? 'Add Products to Cart' : 'Complete Sale — PKR ' + total.toLocaleString()"></span>
        </button>
      </div>
    </form>
  </div>

  {{-- Camera Scanner Modal --}}
  <div x-show="cameraOpen" x-transition
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
       @keydown.escape.window="closeCamera()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="font-bold text-slate-900">Camera Scanner</h3>
          <p class="text-xs text-slate-400 mt-0.5" x-text="cameraStatus"></p>
        </div>
        <button @click="closeCamera()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
      <div class="relative bg-black">
        <video x-ref="cameraVideo" autoplay playsinline muted class="w-full aspect-video object-cover"></video>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div class="w-52 h-32 relative">
            <span class="absolute top-0 left-0 w-6 h-6 border-t-3 border-l-3 border-green-400" style="border-width:3px"></span>
            <span class="absolute top-0 right-0 w-6 h-6 border-t-3 border-r-3 border-green-400" style="border-width:3px"></span>
            <span class="absolute bottom-0 left-0 w-6 h-6 border-b-3 border-l-3 border-green-400" style="border-width:3px"></span>
            <span class="absolute bottom-0 right-0 w-6 h-6 border-b-3 border-r-3 border-green-400" style="border-width:3px"></span>
            <div class="absolute inset-x-4 top-1/2 h-px bg-green-400 opacity-80 animate-pulse"></div>
          </div>
        </div>
      </div>
      <div class="px-5 py-4 text-center">
        <p class="text-xs text-slate-500">Barcode ko camera ke saamne rakhen</p>
        <p x-show="lastCameraResult" class="mt-2 text-sm font-mono font-bold text-green-700" x-text="lastCameraResult"></p>
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
    scanMsg: '',
    scanOk: true,
    scanTimer: null,
    cameraOpen: false,
    cameraSupported: false,
    cameraStatus: '',
    lastCameraResult: '',
    cameraStream: null,
    barcodeDetector: null,
    scanInterval: null,

    init() {
      this.cameraSupported = 'BarcodeDetector' in window;
    },

    get categories() {
      const cats = [...new Set(this.products.map(p => p.category).filter(Boolean))].sort();
      return cats;
    },

    get filtered() {
      let list = this.products;
      if (this.activeCategory && !this.searchQ) {
        list = list.filter(p => p.category === this.activeCategory);
      }
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

    stockColor(p) {
      if (p.stock_qty <= 0)  return 'bg-red-400';
      if (p.stock_qty <= 5)  return 'bg-amber-400';
      return 'bg-green-400';
    },

    get subtotal() { return this.cart.reduce((s, i) => s + i.qty * i.price, 0); },
    get total()    { return Math.max(0, this.subtotal - Number(this.discount || 0)); },
    get change()   { return Math.max(0, Number(this.amountPaid || 0) - this.total); },
    setFullPay()   { this.amountPaid = this.total; },

    addToCart(p) {
      if (p.stock_qty <= 0) return;
      const ex = this.cart.find(i => i.id == p.id);
      if (ex) { if (ex.qty < p.stock_qty) ex.qty++; }
      else this.cart.push({ id: p.id, name: p.name, unit: p.unit, price: parseFloat(p.sale_price), qty: 1, stock: p.stock_qty, category: p.category });
    },

    removeFromCart(id) { this.cart = this.cart.filter(i => i.id != id); },

    tryBarcodeEnter() {
      const q = this.searchQ.trim();
      if (!q) return;
      const exact = this.products.find(p =>
        (p.barcode && p.barcode === q) || (p.sku && p.sku === q)
      );
      if (exact) {
        this.addToCart(exact);
        this.flash('✓ ' + exact.name, true);
        this.searchQ = '';
      }
    },

    addByBarcode(code) {
      const product = this.products.find(p =>
        (p.barcode && p.barcode === code) || (p.sku && p.sku === code)
      );
      if (product) {
        if (product.stock_qty <= 0) { this.flash('Out of stock: ' + product.name, false); return; }
        this.addToCart(product);
        this.flash('✓ ' + product.name, true);
      } else {
        this.flash('Not found: ' + code, false);
      }
    },

    flash(msg, ok) {
      this.scanMsg = msg; this.scanOk = ok;
      clearTimeout(this.scanTimer);
      this.scanTimer = setTimeout(() => { this.scanMsg = ''; }, 2200);
    },

    async openCamera() {
      if (!this.cameraSupported) return;
      this.cameraOpen = true;
      this.lastCameraResult = '';
      this.cameraStatus = 'Camera access maang raha hai...';
      try {
        this.cameraStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } }
        });
        this.$refs.cameraVideo.srcObject = this.cameraStream;
        this.cameraStatus = 'Scanning...';
        this.barcodeDetector = new BarcodeDetector({
          formats: ['ean_13','ean_8','code_128','code_39','qr_code','upc_a','upc_e','itf','codabar','data_matrix']
        });
        this.scanInterval = setInterval(() => this.detectFrame(), 250);
      } catch (e) {
        this.cameraStatus = 'Camera access deny — USB scanner use karein';
      }
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
          this.$nextTick(() => this.$refs.mainInput.focus());
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
