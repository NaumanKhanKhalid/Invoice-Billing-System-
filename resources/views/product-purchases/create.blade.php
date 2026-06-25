@extends('layouts.app')
@section('title','New Product Purchase')
@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
       items: [],
       addItem(product) {
         const existing = this.items.find(i => i.product_id == product.id);
         if (existing) { existing.qty++; existing.total = existing.qty * existing.unit_price; }
         else this.items.push({ product_id: product.id, name: product.name, unit: product.unit, stock: product.stock_qty, qty: 1, unit_price: product.sale_price, total: product.sale_price });
         this.recalc();
       },
       removeItem(idx) { this.items.splice(idx, 1); this.recalc(); },
       recalc() { /* totals computed via template */ },
       get subtotal() { return this.items.reduce((s,i) => s + (i.qty * i.unit_price), 0); },
       amountPaid: 0,
       get due() { return Math.max(0, this.subtotal - this.amountPaid); },
       searchQ: '',
       products: @json($products),
       get filtered() { return this.searchQ.length < 1 ? this.products.slice(0,20) : this.products.filter(p => p.name.toLowerCase().includes(this.searchQ.toLowerCase()) || (p.sku && p.sku.toLowerCase().includes(this.searchQ.toLowerCase()))).slice(0,20); }
     }">

  <div class="flex items-center gap-3">
    <a href="{{ route('product-purchases.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <h1 class="text-xl font-bold text-slate-900">New Product Purchase</h1>
  </div>

  <form method="POST" action="{{ route('product-purchases.store') }}">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      {{-- Left: Product selector --}}
      <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h2 class="font-semibold text-slate-900 mb-3">Select Products</h2>
          <div class="relative mb-3">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
            <input type="text" x-model="searchQ" placeholder="Search product or SKU..."
                   class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto">
            <template x-for="p in filtered" :key="p.id">
              <button type="button" @click="addItem(p)"
                      class="flex items-center justify-between text-left px-3 py-2.5 rounded-lg border border-slate-100 hover:border-green-300 hover:bg-green-50 transition-colors text-sm">
                <div>
                  <p class="font-medium text-slate-900" x-text="p.name"></p>
                  <p class="text-xs text-slate-400" x-text="'PKR ' + p.sale_price + ' · Stock: ' + p.stock_qty + ' ' + p.unit"></p>
                </div>
                <i data-lucide="plus-circle" class="w-4 h-4 text-green-500 flex-shrink-0"></i>
              </button>
            </template>
          </div>
        </div>

        {{-- Cart --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h2 class="font-semibold text-slate-900 mb-3">Purchase Items</h2>
          <template x-if="items.length === 0">
            <p class="text-sm text-slate-400 text-center py-6">Upar se product add karein</p>
          </template>
          <template x-if="items.length > 0">
            <div>
              <table class="w-full text-sm mb-3">
                <thead>
                  <tr class="text-xs text-slate-500 uppercase border-b border-slate-100">
                    <th class="pb-2 text-left">Product</th>
                    <th class="pb-2 text-center w-24">Qty</th>
                    <th class="pb-2 text-right w-28">Cost/Unit</th>
                    <th class="pb-2 text-right w-24">Total</th>
                    <th class="pb-2 w-8"></th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="(item, idx) in items" :key="idx">
                    <tr class="border-b border-slate-50">
                      <td class="py-2">
                        <p class="font-medium text-slate-900" x-text="item.name"></p>
                        <input type="hidden" :name="'items['+idx+'][product_id]'" :value="item.product_id">
                      </td>
                      <td class="py-2 text-center">
                        <input type="number" :name="'items['+idx+'][qty]'" x-model.number="item.qty" min="1"
                               @input="item.total = item.qty * item.unit_price"
                               class="w-20 text-center px-2 py-1 border border-slate-200 rounded text-sm outline-none focus:ring-1 focus:ring-green-300">
                      </td>
                      <td class="py-2 text-right">
                        <input type="number" :name="'items['+idx+'][unit_price]'" x-model.number="item.unit_price" min="0" step="0.01"
                               @input="item.total = item.qty * item.unit_price"
                               class="w-24 text-right px-2 py-1 border border-slate-200 rounded text-sm outline-none focus:ring-1 focus:ring-green-300">
                      </td>
                      <td class="py-2 text-right font-semibold text-slate-900" x-text="'PKR ' + (item.qty * item.unit_price).toLocaleString()"></td>
                      <td class="py-2 text-right">
                        <button type="button" @click="removeItem(idx)" class="text-red-400 hover:text-red-600">
                          <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                      </td>
                    </tr>
                  </template>
                </tbody>
                <tfoot>
                  <tr class="border-t border-slate-200">
                    <td colspan="3" class="pt-2 text-sm font-bold text-slate-900">Total</td>
                    <td class="pt-2 text-right font-bold text-green-600" x-text="'PKR ' + subtotal.toLocaleString()"></td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </template>
        </div>
      </div>

      {{-- Right: Details --}}
      <div class="space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
          <h2 class="font-semibold text-slate-900">Purchase Details</h2>

          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
            <div class="relative">
              <select name="supplier_id" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
                <option value="">No Supplier</option>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
              </select>
              <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Date *</label>
            <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Supplier Invoice #</label>
            <input type="text" name="invoice_number" placeholder="Optional"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Amount Paid (PKR) *</label>
            <input type="number" name="amount_paid" x-model.number="amountPaid" min="0" step="0.01" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>

          <div x-show="due > 0">
            <label class="block text-xs font-medium text-slate-600 mb-1">Due Date</label>
            <input type="date" name="due_date"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>

          <div class="bg-slate-50 rounded-lg p-3 space-y-1 text-sm">
            <div class="flex justify-between text-slate-600">
              <span>Subtotal</span>
              <span x-text="'PKR ' + subtotal.toLocaleString()"></span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Paid</span>
              <span class="text-green-600" x-text="'PKR ' + amountPaid.toLocaleString()"></span>
            </div>
            <div class="flex justify-between font-bold border-t border-slate-200 pt-1 mt-1">
              <span>Due</span>
              <span :class="due > 0 ? 'text-red-600' : 'text-green-600'" x-text="'PKR ' + due.toLocaleString()"></span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none"></textarea>
          </div>

          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-bold transition-colors">
            Save Purchase & Update Stock
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
