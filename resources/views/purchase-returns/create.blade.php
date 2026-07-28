@extends('layouts.app')
@section('title', 'Return to Supplier')
@section('content')
<div class="space-y-5">

  <div class="flex items-center gap-3">
    <a href="{{ route('product-purchases.show', $purchase) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Return to Supplier</h1>
      <p class="text-sm text-slate-500">
        Purchase: <span class="font-medium text-green-600">{{ $purchase->invoice_number ?: '#'.$purchase->id }}</span>
        · {{ $purchase->supplier?->name ?? 'No Supplier' }}
        · {{ $purchase->date->format('d M Y') }}
      </p>
    </div>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
    {{ $errors->first() }}
  </div>
  @endif

  <form method="POST" action="{{ route('purchase-returns.store', $purchase) }}" class="space-y-5">
    @csrf

    {{-- Items --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-900">Items to Return</h2>
        <p class="text-xs text-slate-400 mt-0.5">Jo items supplier ko wapas kar rahe hain unhe select karein</p>
      </div>
      <div class="divide-y divide-slate-100">
        @foreach($purchase->items as $item)
        <div class="flex items-center gap-4 px-5 py-4">
          <input type="checkbox" name="items[{{ $loop->index }}][return]" value="1"
                 id="item_{{ $item->id }}"
                 class="w-4 h-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                 onchange="toggleQty(this, '{{ $item->id }}')">
          <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
          <label for="item_{{ $item->id }}" class="flex-1 cursor-pointer">
            <p class="text-sm font-medium text-slate-900">{{ $item->product?->name ?? 'Unknown' }}</p>
            <p class="text-xs text-slate-400">PKR {{ number_format($item->unit_price) }} × {{ $item->qty }}</p>
          </label>
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">Qty:</span>
            <input type="number" name="items[{{ $loop->index }}][qty]" id="qty_{{ $item->id }}"
                   value="{{ $item->qty }}" min="0.01" max="{{ $item->qty }}" step="0.01"
                   disabled style="opacity:0.4"
                   class="w-20 px-2 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none text-center">
          </div>
          <div class="text-right w-24">
            <p class="text-sm font-semibold text-slate-900">PKR {{ number_format($item->total) }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Return details --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
      <h2 class="font-semibold text-slate-900">Return Details</h2>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Return Date</label>
          <input type="date" name="date" value="{{ today()->toDateString() }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Adjustment Method</label>
          <div class="relative">
            <select name="adjustment_method" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
              <option value="deduct_balance">Supplier Balance Kam Karo</option>
              <option value="cash_refund">Cash Wapas Lo</option>
              <option value="exchange">Exchange (Replacement)</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          </div>
          <p class="text-xs text-slate-400 mt-1">"Supplier Balance" = jo unhe dena tha us mein se minus hoga</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Reason for Return</label>
        <input type="text" name="reason" placeholder="e.g. Defective, Wrong item, Quality issue..."
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
        <textarea name="notes" rows="2" placeholder="Additional notes..."
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none"></textarea>
      </div>
    </div>

    <div class="flex items-center justify-between">
      <a href="{{ route('product-purchases.show', $purchase) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
        Cancel
      </a>
      <button type="submit" class="inline-flex items-center gap-2 bg-orange-600 hover:bg-orange-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
        Process Return & Reduce Stock
      </button>
    </div>
  </form>
</div>

<script>
function toggleQty(checkbox, itemId) {
    const qtyInput = document.getElementById('qty_' + itemId);
    qtyInput.disabled = !checkbox.checked;
    qtyInput.style.opacity = checkbox.checked ? '1' : '0.4';
}
</script>
@endsection
