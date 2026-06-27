@extends('layouts.app')
@section('title','Point of Sale')
@section('content')
<script>window.__POS_PRODUCTS__ = @json($products);</script>
<div class="h-[calc(100vh-8rem)] flex flex-col"
     x-data="posApp()"
     x-init="init()">

  <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <div class="flex items-center gap-3">
      <a href="{{ route('pos.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <h1 class="text-xl font-bold text-slate-900">Point of Sale</h1>
    </div>
    <a href="{{ route('pos.index') }}" class="text-sm text-slate-500 hover:text-green-600">Sales History →</a>
  </div>

  {{-- Scan toast --}}
  <div x-show="scanMsg" x-transition
       :class="scanOk ? 'bg-green-50 border-green-300 text-green-800' : 'bg-red-50 border-red-300 text-red-700'"
       class="mb-3 flex items-center gap-2 border rounded-lg px-4 py-2.5 text-sm font-medium">
    <span x-text="scanMsg"></span>
  </div>

  <div class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-4 min-h-0">

    {{-- Products Panel --}}
    <div class="lg:col-span-2 flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

      <div class="p-3 border-b border-slate-100 space-y-2">
        {{-- Barcode input --}}
        <div class="flex gap-2">
          <div class="relative flex-1">
            <i data-lucide="scan-barcode" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
            <input type="text" x-ref="barcodeInput"
                   x-model="barcodeVal"
                   @keydown.enter.prevent="scanBarcode()"
                   placeholder="Barcode scan karein (USB scanner ya type + Enter)..."
                   class="w-full pl-9 pr-3 py-2 text-sm border border-green-300 bg-green-50 rounded-lg focus:ring-2 focus:ring-green-400 outline-none font-mono"
                   autofocus>
          </div>
          <button type="button" @click="openCamera()"
                  x-show="cameraSupported"
                  class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium rounded-lg transition-colors shrink-0">
            <i data-lucide="camera" class="w-4 h-4"></i> Camera
          </button>
        </div>

        {{-- Name / SKU search --}}
        <div class="relative">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
          <input type="text" x-model="searchQ"
                 placeholder="Product name ya SKU se search karein..."
                 class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>

      <div class="flex-1 overflow-y-auto p-3">
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2">
          <template x-for="p in filtered" :key="p.id">
            <button type="button" @click="addToCart(p)"
                    :disabled="p.stock_qty <= 0"
                    :class="p.stock_qty <= 0 ? 'opacity-40 cursor-not-allowed' : 'hover:border-green-400 hover:bg-green-50 cursor-pointer'"
                    class="text-left border border-slate-200 rounded-xl p-3 transition-colors bg-white">
              <p class="text-sm font-semibold text-slate-900 leading-tight truncate" x-text="p.name"></p>
              <p class="text-xs text-slate-400 mt-0.5" x-text="p.sku ?? ''"></p>
              <p class="text-sm font-bold text-green-600 mt-1.5" x-text="'PKR ' + Number(p.sale_price).toLocaleString()"></p>
              <p class="text-xs mt-0.5" :class="p.stock_qty <= 5 ? 'text-amber-500' : 'text-slate-400'" x-text="'Stock: ' + p.stock_qty + ' ' + p.unit"></p>
            </button>
          </template>
          <template x-if="filtered.length === 0">
            <p class="col-span-4 text-center text-slate-400 text-sm py-8">Koi product nahi mila</p>
          </template>
        </div>
      </div>
    </div>

    {{-- Cart & Checkout --}}
    <div class="flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-900 text-sm flex items-center justify-between">
        <span>Cart</span>
        <span class="text-xs text-slate-400" x-text="cart.length + ' items'"></span>
      </div>

      <div class="flex-1 overflow-y-auto p-3 space-y-2">
        <template x-if="cart.length === 0">
          <p class="text-center text-slate-400 text-sm py-8">Products add karein</p>
        </template>
        <template x-for="(item, idx) in cart" :key="item.id">
          <div class="flex items-center gap-2 bg-slate-50 rounded-lg px-3 py-2">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-slate-900 truncate" x-text="item.name"></p>
              <p class="text-xs text-green-600" x-text="'PKR ' + item.price"></p>
            </div>
            <div class="flex items-center gap-1">
              <button type="button" @click="item.qty > 1 ? item.qty-- : removeFromCart(item.id)"
                      class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold flex items-center justify-center">−</button>
              <span class="w-8 text-center text-sm font-bold" x-text="item.qty"></span>
              <button type="button" @click="item.qty < item.stock ? item.qty++ : null"
                      :disabled="item.qty >= item.stock"
                      class="w-6 h-6 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-bold flex items-center justify-center disabled:opacity-40">+</button>
            </div>
            <p class="text-xs font-bold text-slate-900 w-16 text-right" x-text="'PKR ' + (item.qty * item.price).toLocaleString()"></p>
            <button type="button" @click="removeFromCart(item.id)" class="text-red-400 hover:text-red-600 ml-1">
              <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
          </div>
        </template>
      </div>

      <form method="POST" action="{{ route('pos.store') }}" class="border-t border-slate-100 p-4 space-y-3">
        @csrf
        <template x-for="(item, idx) in cart">
          <div>
            <input type="hidden" :name="'items['+idx+'][product_id]'" :value="item.id">
            <input type="hidden" :name="'items['+idx+'][qty]'" :value="item.qty">
            <input type="hidden" :name="'items['+idx+'][unit_price]'" :value="item.price">
          </div>
        </template>

        <div class="space-y-1 text-sm">
          <div class="flex justify-between text-slate-600">
            <span>Subtotal</span><span x-text="'PKR ' + subtotal.toLocaleString()"></span>
          </div>
          <div class="flex justify-between items-center text-slate-600">
            <span>Discount</span>
            <input type="number" name="discount" x-model="discount" min="0" step="1"
                   class="w-24 text-right px-2 py-0.5 border border-slate-200 rounded text-sm outline-none focus:ring-1 focus:ring-green-300">
          </div>
          <div class="flex justify-between font-bold text-slate-900 border-t border-slate-100 pt-1">
            <span>Total</span><span x-text="'PKR ' + total.toLocaleString()"></span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Customer (optional)</label>
          <input type="text" name="customer_name" placeholder="Customer name..."
                 class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div>
          <div class="flex justify-between items-center mb-1">
            <label class="text-xs font-medium text-slate-600">Cash Received</label>
            <button type="button" @click="setFullPay()" class="text-xs text-green-600 hover:underline">Exact</button>
          </div>
          <input type="number" name="amount_paid" x-model="amountPaid" min="0" step="0.01" required
                 class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div x-show="change > 0" class="bg-green-50 border border-green-200 rounded-lg px-3 py-2 flex justify-between text-sm font-bold">
          <span class="text-slate-700">Change</span>
          <span class="text-green-600" x-text="'PKR ' + change.toLocaleString()"></span>
        </div>

        <div class="relative">
          <select name="payment_method" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
            <option value="cash">💵 Cash</option>
            <option value="jazzcash">📱 JazzCash</option>
            <option value="easypaisa">📱 EasyPaisa</option>
            <option value="bank">🏦 Bank Transfer</option>
            <option value="credit">📋 Credit (Udhar)</option>
          </select>
          <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
        </div>

        <button type="submit" :disabled="cart.length === 0"
                class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed text-white py-3 rounded-xl text-sm font-bold transition-colors">
          Complete Sale →
        </button>
      </form>
    </div>
  </div>

  {{-- Camera Scanner Modal --}}
  <div x-show="cameraOpen" x-transition
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
       @keydown.escape.window="closeCamera()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
          <h3 class="font-bold text-slate-900">Camera Barcode Scanner</h3>
          <p class="text-xs text-slate-400 mt-0.5" x-text="cameraStatus"></p>
        </div>
        <button @click="closeCamera()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
      <div class="relative bg-black">
        <video x-ref="cameraVideo" autoplay playsinline muted class="w-full aspect-video object-cover"></video>
        {{-- Viewfinder overlay --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div class="w-52 h-36 relative">
            <span class="absolute top-0 left-0 w-5 h-5 border-t-2 border-l-2 border-green-400"></span>
            <span class="absolute top-0 right-0 w-5 h-5 border-t-2 border-r-2 border-green-400"></span>
            <span class="absolute bottom-0 left-0 w-5 h-5 border-b-2 border-l-2 border-green-400"></span>
            <span class="absolute bottom-0 right-0 w-5 h-5 border-b-2 border-r-2 border-green-400"></span>
            <div class="absolute inset-x-0 top-1/2 h-0.5 bg-green-400 opacity-60 animate-pulse"></div>
          </div>
        </div>
      </div>
      <div class="px-5 py-4 text-center space-y-1.5">
        <p class="text-xs text-slate-500">Barcode ko camera ke saamne rakhen — auto detect hoga</p>
        <p x-show="lastCameraResult" class="text-sm font-mono font-semibold text-green-700" x-text="'Scanned: ' + lastCameraResult"></p>
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
    barcodeVal: '',
    discount: 0,
    amountPaid: 0,
    scanMsg: '',
    scanOk: true,
    scanTimer: null,
    cameraOpen: false,
    cameraSupported: false,
    cameraStatus: 'Camera initialising...',
    lastCameraResult: '',
    cameraStream: null,
    barcodeDetector: null,
    scanInterval: null,

    init() {
      this.cameraSupported = 'BarcodeDetector' in window;
    },

    get filtered() {
      if (!this.searchQ) return this.products.slice(0, 24);
      const q = this.searchQ.toLowerCase();
      return this.products.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.sku && p.sku.toLowerCase().includes(q)) ||
        (p.barcode && p.barcode.toLowerCase().includes(q))
      ).slice(0, 24);
    },

    get subtotal() { return this.cart.reduce((s, i) => s + i.qty * i.price, 0); },
    get total()    { return Math.max(0, this.subtotal - Number(this.discount || 0)); },
    get change()   { return Math.max(0, Number(this.amountPaid || 0) - this.total); },
    setFullPay()   { this.amountPaid = this.total; },

    addToCart(p) {
      const ex = this.cart.find(i => i.id == p.id);
      if (ex) { if (ex.qty < p.stock_qty) ex.qty++; }
      else this.cart.push({ id: p.id, name: p.name, unit: p.unit, price: parseFloat(p.sale_price), qty: 1, stock: p.stock_qty });
    },

    removeFromCart(id) { this.cart = this.cart.filter(i => i.id != id); },

    scanBarcode() {
      const code = this.barcodeVal.trim();
      if (!code) return;
      this.barcodeVal = '';
      this.addByBarcode(code);
      this.$nextTick(() => this.$refs.barcodeInput.focus());
    },

    addByBarcode(code) {
      const product = this.products.find(p =>
        (p.barcode && p.barcode === code) ||
        (p.sku && p.sku === code)
      );
      if (product) {
        if (product.stock_qty <= 0) { this.flash('Out of stock: ' + product.name, false); return; }
        this.addToCart(product);
        this.flash('✓ ' + product.name + ' added', true);
      } else {
        this.flash('Barcode not found: ' + code, false);
      }
    },

    flash(msg, ok) {
      this.scanMsg = msg;
      this.scanOk  = ok;
      clearTimeout(this.scanTimer);
      this.scanTimer = setTimeout(() => { this.scanMsg = ''; }, 2500);
    },

    async openCamera() {
      if (!this.cameraSupported) return;
      this.cameraOpen = true;
      this.lastCameraResult = '';
      this.cameraStatus = 'Camera permission maang raha hai...';
      try {
        this.cameraStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } }
        });
        this.$refs.cameraVideo.srcObject = this.cameraStream;
        this.cameraStatus = 'Scanning — barcode ke saamne rakhen...';
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
          this.$nextTick(() => this.$refs.barcodeInput.focus());
        }
      } catch (_) {}
    },

    closeCamera() {
      clearInterval(this.scanInterval);
      this.scanInterval = null;
      if (this.cameraStream) {
        this.cameraStream.getTracks().forEach(t => t.stop());
        this.cameraStream = null;
      }
      this.cameraOpen = false;
    },
  };
}
</script>
@endsection
