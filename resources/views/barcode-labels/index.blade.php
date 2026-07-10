@extends('layouts.app')
@section('title','Print Barcode Labels')
@section('content')
<div class="space-y-6" x-data="barcodeLabels()">
  <div class="flex items-center justify-between print:hidden">
    <div>
      <h1 class="text-xl font-bold text-slate-900">Print Barcode Labels</h1>
      <p class="text-sm text-slate-500">{{ count($products) }} active products</p>
    </div>
    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>Back to Products
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 print:hidden">
    {{-- Product picker --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-36">
          <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
          <input type="text" x-model="search" placeholder="Search products..."
                 class="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
          <input type="checkbox" @change="toggleAll($event.target.checked)" class="rounded border-slate-300 text-green-600 focus:ring-green-300">
          Select all
        </label>
      </div>
      <div class="max-h-96 overflow-y-auto divide-y divide-slate-100">
        <template x-for="p in filtered()" :key="p.id">
          <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer">
            <input type="checkbox" :value="p.id" x-model="selected" class="rounded border-slate-300 text-green-600 focus:ring-green-300">
            <span class="flex-1 text-sm font-medium text-slate-900" x-text="p.name"></span>
            <span class="text-xs text-slate-400" x-text="p.code"></span>
            <span class="text-sm font-semibold text-slate-700" x-text="'PKR ' + Number(p.price).toLocaleString()"></span>
          </label>
        </template>
        <p class="px-4 py-6 text-center text-sm text-slate-400" x-show="!filtered().length">No products found</p>
      </div>
    </div>

    {{-- Options --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 space-y-4 self-start">
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Labels per product</label>
        <input type="number" min="1" max="100" x-model.number="perProduct"
               class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900" x-text="selected.length"></span> products, <span class="font-semibold text-slate-900" x-text="totalLabels()"></span> labels</p>
      @if(feature_enabled('receipt_print'))
      <button type="button" @click="printLabels()" :disabled="!selected.length"
              class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="printer" class="w-4 h-4"></i>Print Labels
      </button>
      @else
      <p class="text-xs text-slate-400">Printing is not enabled on your plan.</p>
      @endif
    </div>
  </div>

  {{-- Labels sheet --}}
  <div id="labels-sheet" class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 print:p-0 print:border-0 print:shadow-none" x-show="selected.length" x-cloak>
    <p class="text-xs text-slate-400 uppercase tracking-wider mb-3 print:hidden">Preview</p>
    <div class="labels-grid">
      <template x-for="label in labels()" :key="label.key">
        <div class="label-item">
          <p class="label-shop">{{ \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name')) }}</p>
          <p class="label-name" x-text="label.name"></p>
          <p class="label-price" x-text="'PKR ' + Number(label.price).toLocaleString()"></p>
          <svg class="label-barcode" x-effect="renderBarcode($el, label.code)"></svg>
        </div>
      </template>
    </div>
  </div>
</div>

<style>
[x-cloak] { display: none !important; }
.labels-grid {
  display: grid;
  grid-template-columns: repeat(3, 38mm);
  gap: 3mm;
}
.label-item {
  width: 38mm;
  height: 25mm;
  border: 1px dashed #cbd5e1;
  padding: 1mm 1.5mm;
  overflow: hidden;
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: #fff;
}
.label-shop { font-size: 5pt; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.label-name { font-size: 6.5pt; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.label-price { font-size: 8pt; font-weight: 700; color: #000; }
.label-barcode { width: 100%; height: 9mm; }
@media print {
  @page { size: A4; margin: 5mm; }
  aside, nav, header, footer, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: white !important; padding: 0 !important; margin: 0 !important; }
  body * { visibility: hidden; }
  #labels-sheet, #labels-sheet * { visibility: visible; }
  #labels-sheet {
    position: absolute; top: 0; left: 0;
    margin: 0 !important; padding: 0 !important;
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
    background: #fff !important;
  }
  .label-item { border: 1px solid #e2e8f0; break-inside: avoid; page-break-inside: avoid; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
function barcodeLabels() {
  return {
    search: '',
    perProduct: 1,
    selected: [],
    products: @json($products),
    filtered() {
      const s = this.search.toLowerCase().trim();
      if (!s) return this.products;
      return this.products.filter(p => p.name.toLowerCase().includes(s) || String(p.code).toLowerCase().includes(s));
    },
    toggleAll(checked) {
      const ids = this.filtered().map(p => p.id);
      this.selected = checked ? [...new Set([...this.selected, ...ids])] : this.selected.filter(id => !ids.includes(id));
    },
    totalLabels() {
      return this.selected.length * Math.max(this.perProduct || 1, 1);
    },
    labels() {
      const per = Math.max(this.perProduct || 1, 1);
      const out = [];
      this.products.filter(p => this.selected.includes(p.id)).forEach(p => {
        for (let i = 0; i < per; i++) out.push({ key: p.id + '-' + i, name: p.name, price: p.price, code: String(p.code) });
      });
      return out;
    },
    renderBarcode(el, code) {
      try {
        JsBarcode(el, code, { format: 'CODE128', displayValue: true, fontSize: 8, height: 22, width: 1, margin: 0 });
      } catch (e) { /* invalid code */ }
    },
    printLabels() {
      if (!this.selected.length) return;
      this.$nextTick(() => window.print());
    },
  };
}
</script>
@endsection
