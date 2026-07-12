@extends('layouts.app')
@section('title', 'New Quotation')
@section('content')
<script>window.__QUOTE_PRODUCTS__ = @json($products);</script>
<div class="max-w-3xl mx-auto space-y-5"
     x-data="quoteForm()"
     x-init="init()">

  <div class="flex items-center gap-3">
    <a href="{{ route('quotations.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <h1 class="text-xl font-bold text-slate-900">New Quotation</h1>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form method="POST" action="{{ route('quotations.store') }}">
    @csrf

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
      <h2 class="font-semibold text-slate-900">Quote Details</h2>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Quote Number</label>
          <input type="text" name="quote_number" value="{{ old('quote_number', $quoteNumber) }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-slate-50 font-mono font-semibold">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Date</label>
          <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Customer Name</label>
          <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Optional"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Customer Phone</label>
          <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Optional"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Valid Until</label>
          <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Discount (PKR)</label>
          <input type="number" name="discount" value="{{ old('discount', 0) }}" min="0" step="0.01"
                 x-model="discount" @input="calcTotal()"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Notes</label>
        <textarea name="notes" rows="2" placeholder="Optional notes for customer..."
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none">{{ old('notes') }}</textarea>
      </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Items</h2>
        <button type="button" @click="addRow()"
                class="inline-flex items-center gap-1.5 text-green-600 hover:text-green-700 text-sm font-medium">
          <i data-lucide="plus" class="w-4 h-4"></i> Add Item
        </button>
      </div>

      {{-- Product search --}}
      <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
        <input type="text" x-model="search" @input="filterProducts()"
               placeholder="Search products to add..."
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
        <div x-show="search && filtered.length" class="mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-40 overflow-y-auto">
          <template x-for="p in filtered" :key="p.id">
            <button type="button" @click="addProduct(p)"
                    class="w-full text-left px-3 py-2 text-sm hover:bg-green-50 flex items-center justify-between">
              <span x-text="p.name"></span>
              <span class="text-xs text-slate-400" x-text="'PKR ' + Number(p.sale_price).toLocaleString()"></span>
            </button>
          </template>
        </div>
        <p x-show="search && !filtered.length" class="mt-1 text-xs text-slate-400 px-1">No products match</p>
      </div>

      <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
            <th class="px-4 py-2 text-center text-xs font-semibold text-slate-500 uppercase w-20">Qty</th>
            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase w-32">Unit Price</th>
            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase w-28">Total</th>
            <th class="px-4 py-2 w-10"></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="(row, i) in rows" :key="i">
            <tr class="border-b border-slate-100">
              <td class="px-4 py-2">
                <input type="hidden" :name="'items['+i+'][product_id]'" :value="row.product_id">
                <input type="hidden" :name="'items['+i+'][unit]'" :value="row.unit">
                <input type="text" :name="'items['+i+'][product_name]'" x-model="row.name" required
                       class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-green-300 outline-none">
              </td>
              <td class="px-4 py-2">
                <input type="number" :name="'items['+i+'][qty]'" x-model="row.qty"
                       @input="calcRow(i)" min="0.01" step="0.01" required
                       class="w-full px-2 py-1.5 text-sm text-center border border-slate-200 rounded focus:ring-1 focus:ring-green-300 outline-none">
              </td>
              <td class="px-4 py-2">
                <input type="number" :name="'items['+i+'][unit_price]'" x-model="row.price"
                       @input="calcRow(i)" min="0" step="0.01" required
                       class="w-full px-2 py-1.5 text-sm text-right border border-slate-200 rounded focus:ring-1 focus:ring-green-300 outline-none">
              </td>
              <td class="px-4 py-2 text-sm text-right font-medium text-slate-700"
                  x-text="'PKR ' + Number(row.total).toLocaleString()"></td>
              <td class="px-4 py-2 text-center">
                <button type="button" @click="removeRow(i)" x-show="rows.length > 1"
                        class="text-red-400 hover:text-red-600">
                  <i data-lucide="x" class="w-4 h-4"></i>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 space-y-1.5">
        <div class="flex justify-between text-sm text-slate-600">
          <span>Subtotal</span>
          <span x-text="'PKR ' + Number(subtotal).toLocaleString()"></span>
        </div>
        <div class="flex justify-between text-sm text-slate-600">
          <span>Discount</span>
          <span class="text-red-500" x-text="'- PKR ' + Number(discount).toLocaleString()"></span>
        </div>
        <div class="flex justify-between text-base font-bold text-slate-900 pt-1 border-t border-slate-200">
          <span>Total</span>
          <span x-text="'PKR ' + Number(grandTotal).toLocaleString()"></span>
        </div>
      </div>
    </div>

    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold transition-colors">
      Save Quotation
    </button>
  </form>
</div>

<script>
function quoteForm() {
  return {
    products: window.__QUOTE_PRODUCTS__ || [],
    filtered: [],
    search: '',
    rows: [{ product_id: null, name: '', unit: '', qty: 1, price: 0, total: 0 }],
    subtotal: 0,
    discount: 0,
    grandTotal: 0,

    init() {
      this.calcTotal();
    },

    filterProducts() {
      const q = this.search.toLowerCase();
      this.filtered = q ? this.products.filter(p => p.name.toLowerCase().includes(q)).slice(0, 8) : [];
    },

    addProduct(p) {
      this.rows.push({ product_id: p.id, name: p.name, unit: p.unit || '', qty: 1, price: parseFloat(p.sale_price) || 0, total: parseFloat(p.sale_price) || 0 });
      this.search = '';
      this.filtered = [];
      this.calcTotal();
    },

    addRow() {
      this.rows.push({ product_id: null, name: '', unit: '', qty: 1, price: 0, total: 0 });
    },

    removeRow(i) {
      if (this.rows.length > 1) { this.rows.splice(i, 1); this.calcTotal(); }
    },

    calcRow(i) {
      const r = this.rows[i];
      r.total = Math.round(parseFloat(r.qty || 0) * parseFloat(r.price || 0) * 100) / 100;
      this.calcTotal();
    },

    calcTotal() {
      this.subtotal  = this.rows.reduce((s, r) => s + (parseFloat(r.total) || 0), 0);
      this.grandTotal = Math.max(0, this.subtotal - (parseFloat(this.discount) || 0));
    },
  };
}
</script>
@endsection
