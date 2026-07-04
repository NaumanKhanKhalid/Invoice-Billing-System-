@extends('layouts.app')
@section('title', $openTab->tab_number . ' — ' . $openTab->customer_name)
@section('content')

<div class="flex gap-4 min-h-0" style="height:calc(100vh - 4rem)"
     x-data="tabManager({{ $openTab->id }}, {{ $openTab->status === 'closed' ? 'true' : 'false' }})">

  {{-- LEFT: Item Entry --}}
  <div class="flex flex-col flex-1 min-w-0 gap-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <a href="{{ route('open-tabs.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-slate-700 transition-colors">
          <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-lg font-bold text-slate-900">{{ $openTab->customer_name }}</h1>
            @if($openTab->customer_phone)
            <span class="text-xs text-slate-400">{{ $openTab->customer_phone }}</span>
            @endif
          </div>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs font-semibold text-slate-400">{{ $openTab->tab_number }}</span>
            @if($openTab->notes)
            <span class="text-xs text-slate-400">· {{ $openTab->notes }}</span>
            @endif
            @if($openTab->status === 'open')
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
              <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Open
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
              <i data-lucide="lock" class="w-3 h-3"></i>Closed {{ $openTab->closed_at?->format('d M H:i') }}
            </span>
            @endif
          </div>
        </div>
      </div>
      @if($openTab->status === 'open')
      <button onclick="document.getElementById('closeTabModal').classList.remove('hidden')"
              :disabled="items.length === 0"
              class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 disabled:opacity-40 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        <i data-lucide="receipt" class="w-4 h-4"></i>Close & Bill
      </button>
      @else
      <a href="{{ route('open-tabs.receipt', $openTab) }}"
         class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i>Print Receipt
      </a>
      @endif
    </div>

    @if($openTab->status === 'open')
    {{-- Product Search --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4" x-data="productSearch()">
      <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Add Item</p>
      <div class="flex gap-3">
        <div class="relative flex-1">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
          <input type="text" x-model="query" @input.debounce.200ms="search()"
                 @keydown.escape="reset()"
                 class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none"
                 placeholder="Search product by name, barcode, SKU…" autocomplete="off">
          {{-- Dropdown --}}
          <div x-show="results.length > 0" x-cloak
               class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-30 overflow-hidden">
            <template x-for="p in results" :key="p.id">
              <button type="button" @click="select(p)"
                      class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-green-50 text-left border-b border-slate-50 last:border-0">
                <div>
                  <p class="text-sm font-medium text-slate-900" x-text="p.name"></p>
                  <p class="text-xs text-slate-400" x-text="p.sku + (p.barcode ? ' · ' + p.barcode : '')"></p>
                </div>
                <div class="text-right ml-4">
                  <p class="text-sm font-bold text-green-700" x-text="'PKR ' + Number(p.sale_price).toLocaleString()"></p>
                  <p class="text-xs text-slate-400" x-text="p.stock_qty + ' ' + p.unit + ' in stock'"></p>
                </div>
              </button>
            </template>
          </div>
        </div>
      </div>

      {{-- Add Item Form (shown after selecting product or manually) --}}
      <div x-show="selected" x-cloak class="mt-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
        <form @submit.prevent="addItem({{ $openTab->id }})">
          <div class="grid grid-cols-12 gap-2 items-end">
            <div class="col-span-5">
              <label class="text-xs text-slate-500 mb-1 block">Item Name</label>
              <input type="text" x-model="form.product_name" required
                     class="w-full px-2.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none bg-white">
            </div>
            <div class="col-span-2">
              <label class="text-xs text-slate-500 mb-1 block">Qty</label>
              <input type="number" x-model="form.qty" min="0.001" step="any" required
                     class="w-full px-2.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none bg-white text-center">
            </div>
            <div class="col-span-3">
              <label class="text-xs text-slate-500 mb-1 block">Price (PKR)</label>
              <input type="number" x-model="form.price" min="0" step="any" required
                     class="w-full px-2.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none bg-white">
            </div>
            <div class="col-span-2 flex gap-1.5">
              <button type="submit" :disabled="loading"
                      class="flex-1 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white py-2 rounded-lg text-sm font-semibold transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mx-auto"></i>
              </button>
              <button type="button" @click="reset()"
                      class="px-2 py-2 border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
          <input type="hidden" x-model="form.product_id">
        </form>
      </div>

      {{-- Manual item button --}}
      <button type="button" x-show="!selected" @click="selectManual()"
              class="mt-2 text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 transition-colors">
        <i data-lucide="pencil" class="w-3 h-3"></i>Add custom item manually
      </button>
    </div>
    @endif

    {{-- Items List --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex-1 min-h-0 flex flex-col">
      <div class="bg-slate-50 border-b border-slate-100 px-4 py-3 flex items-center justify-between flex-shrink-0">
        <p class="text-sm font-semibold text-slate-700 flex items-center gap-2">
          <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
          Items (<span x-text="items.length">{{ $openTab->items->count() }}</span>)
        </p>
        <p class="text-xs text-slate-400">{{ $openTab->created_at->format('d M Y, h:i A') }}</p>
      </div>
      <div class="overflow-y-auto flex-1">
        <table class="w-full">
          <thead class="bg-slate-50 border-b border-slate-100 sticky top-0">
            <tr>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Item</th>
              <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 uppercase">Qty</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">Price</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">Total</th>
              @if($openTab->status === 'open')
              <th class="px-4 py-2.5 w-10"></th>
              @endif
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template x-for="(item, index) in items" :key="item.id">
              <tr class="hover:bg-slate-50 text-sm">
                <td class="px-4 py-2.5 text-xs text-slate-400" x-text="index + 1"></td>
                <td class="px-4 py-2.5">
                  <p class="font-medium text-slate-800" x-text="item.product_name"></p>
                  <p class="text-xs text-slate-400" x-text="item.unit" x-show="item.unit && item.unit !== 'pcs'"></p>
                </td>
                <td class="px-4 py-2.5 text-center text-slate-700" x-text="Number(item.qty).toLocaleString()"></td>
                <td class="px-4 py-2.5 text-right text-slate-700" x-text="'PKR ' + Number(item.price).toLocaleString()"></td>
                <td class="px-4 py-2.5 text-right font-semibold text-slate-900" x-text="'PKR ' + Number(item.total).toLocaleString()"></td>
                @if($openTab->status === 'open')
                <td class="px-4 py-2.5 text-center">
                  <button @click="removeItem({{ $openTab->id }}, item.id)"
                          class="text-slate-300 hover:text-red-500 transition-colors">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                  </button>
                </td>
                @endif
              </tr>
            </template>
            <template x-if="items.length === 0">
              <tr>
                <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                  <i data-lucide="package" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                  No items yet — search and add products above
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      {{-- Running Total Footer --}}
      <div class="border-t border-slate-200 px-4 py-3 bg-slate-50 flex-shrink-0">
        <div class="flex items-center justify-between">
          <span class="text-sm text-slate-500">Running Total</span>
          <span class="text-2xl font-bold text-slate-900" x-text="'PKR ' + Math.round(total).toLocaleString()"></span>
        </div>
        @if($openTab->discount > 0)
        <div class="flex items-center justify-between mt-1">
          <span class="text-xs text-slate-400">Discount</span>
          <span class="text-sm font-medium text-red-500">−PKR {{ number_format($openTab->discount) }}</span>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Delete button for open tabs --}}
  @if($openTab->status === 'open')
  <div class="absolute bottom-6 left-6">
    <form method="POST" action="{{ route('open-tabs.destroy', $openTab) }}"
          data-confirm-title="Cancel Tab?" data-confirm-message="Delete this tab for {{ $openTab->customer_name }}? Items will be lost." data-confirm-text="Yes, Cancel Tab" data-confirm-danger="true">
      @csrf @method('DELETE')
      <button type="submit" class="text-xs text-slate-400 hover:text-red-500 flex items-center gap-1 transition-colors">
        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Cancel Tab
      </button>
    </form>
  </div>
  @endif
</div>

{{-- Close Tab Modal --}}
@if($openTab->status === 'open')
<div id="closeTabModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(15,23,42,0.5);backdrop-filter:blur(4px)">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-slate-900">Close Tab & Generate Bill</h3>
      <button onclick="document.getElementById('closeTabModal').classList.add('hidden')"
              class="text-slate-400 hover:text-slate-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('open-tabs.close', $openTab) }}" class="p-6 space-y-4">
      @csrf
      <div class="bg-slate-50 rounded-xl p-4 text-center">
        <p class="text-xs text-slate-500 mb-1">{{ $openTab->customer_name }}</p>
        <p class="text-3xl font-bold text-slate-900" x-data x-text="window.tabTotal ? 'PKR ' + Math.round(window.tabTotal).toLocaleString() : 'PKR {{ number_format($openTab->total) }}'">
          PKR {{ number_format($openTab->total) }}
        </p>
        <p class="text-xs text-slate-400 mt-1" x-data x-text="(window.tabItems ? window.tabItems : {{ $openTab->items->count() }}) + ' items'">
          {{ $openTab->items->count() }} items
        </p>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Discount (PKR)</label>
        <input type="number" name="discount" min="0" value="0" step="any"
               class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Amount Received (PKR) *</label>
        <input type="number" name="amount_paid" min="0" step="any" required
               class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none"
               placeholder="Enter cash received">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Payment Method</label>
        <select name="payment_method"
                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="cash">💵 Cash</option>
          <option value="online">📱 Online Transfer</option>
          <option value="card">💳 Card</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('closeTabModal').classList.add('hidden')"
                class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50">
          Cancel
        </button>
        <button type="submit"
                class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors">
          <i data-lucide="receipt" class="w-4 h-4 inline mr-1"></i>Generate Bill
        </button>
      </div>
    </form>
  </div>
</div>
@endif

@push('scripts')
<script>
function tabManager(tabId, isClosed) {
    return {
        tabId,
        isClosed,
        items: @json($openTab->items),
        loading: false,
        get total() {
            return this.items.reduce((s, i) => s + parseFloat(i.total), 0);
        },
        init() {
            window.tabTotal = this.total;
            window.tabItems = this.items.length;
            this.$watch('items', () => {
                window.tabTotal = this.total;
                window.tabItems = this.items.length;
                if (window.lucide) lucide.createIcons({ icons: lucide.icons });
            });
        },
        async removeItem(tabId, itemId) {
            if (!confirm('Remove this item?')) return;
            this.loading = true;
            try {
                const res = await fetch(`/open-tabs/${tabId}/items/${itemId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.items = data.items;
            } finally { this.loading = false; }
        }
    }
}

function productSearch() {
    return {
        query: '',
        results: [],
        selected: false,
        form: { product_id: null, product_name: '', qty: 1, price: '', unit: 'pcs' },
        loading: false,
        async search() {
            if (this.query.length < 1) { this.results = []; return; }
            const res = await fetch(`/open-tabs/search-products?q=${encodeURIComponent(this.query)}`);
            this.results = await res.json();
        },
        select(product) {
            this.form.product_id   = product.id;
            this.form.product_name = product.name;
            this.form.price        = product.sale_price;
            this.form.unit         = product.unit || 'pcs';
            this.form.qty          = 1;
            this.selected = true;
            this.results = [];
            this.query = product.name;
        },
        selectManual() {
            this.form = { product_id: null, product_name: '', qty: 1, price: '', unit: 'pcs' };
            this.selected = true;
            this.query = '';
        },
        reset() {
            this.query = '';
            this.results = [];
            this.selected = false;
            this.form = { product_id: null, product_name: '', qty: 1, price: '', unit: 'pcs' };
        },
        async addItem(tabId) {
            this.loading = true;
            try {
                const res = await fetch(`/open-tabs/${tabId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (!res.ok) {
                    const msg = data.error || (data.errors ? Object.values(data.errors)[0][0] : 'Could not add item.');
                    if (typeof showToast === 'function') showToast(msg, 'error'); else alert(msg);
                    return;
                }
                // Update parent tabManager
                const mgr = Alpine.$data(document.querySelector('[x-data^="tabManager"]'));
                if (mgr) mgr.items = data.items;
                this.reset();
            } finally { this.loading = false; }
        }
    }
}
</script>
@endpush
@endsection
