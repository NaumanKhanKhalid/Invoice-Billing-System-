@extends('layouts.app')
@section('title', 'Process Return')
@section('content')
<div class="max-w-2xl mx-auto space-y-5">

  <div class="flex items-center gap-3">
    <a href="{{ route('pos.show', $sale) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Process Return</h1>
      <p class="text-sm text-slate-500">Original Sale: <span class="font-medium text-green-600">{{ $sale->sale_number }}</span> · {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</p>
    </div>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
    {{ $errors->first() }}
  </div>
  @endif

  <form method="POST" action="{{ route('sale-returns.store', $sale) }}" class="space-y-5">
    @csrf

    {{-- Select items --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-900">Select Items to Return</h2>
        <p class="text-xs text-slate-400 mt-0.5">Jo items wapas aa rahe hain unhe check karein aur qty set karein</p>
      </div>
      <div class="divide-y divide-slate-100">
        @foreach($sale->items as $item)
        <div class="flex items-center gap-4 px-5 py-4">
          <input type="checkbox" name="items[{{ $loop->index }}][return]" value="1"
                 id="item_{{ $item->id }}"
                 class="w-4 h-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                 onchange="toggleQty(this, '{{ $item->id }}')" checked>
          <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
          <label for="item_{{ $item->id }}" class="flex-1 cursor-pointer">
            <p class="text-sm font-medium text-slate-900">{{ $item->product_name }}</p>
            <p class="text-xs text-slate-400">PKR {{ number_format($item->unit_price) }} × {{ $item->qty }} {{ $item->product?->unit ?? '' }}</p>
          </label>
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">Qty:</span>
            <input type="number" name="items[{{ $loop->index }}][qty]" id="qty_{{ $item->id }}"
                   value="{{ $item->qty }}" min="0.01" max="{{ $item->qty }}" step="0.01"
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
          <label class="block text-sm font-medium text-slate-700 mb-1">Refund Method</label>
          <div class="relative">
            <select name="refund_method" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
              <option value="cash" selected>Cash</option>
              <option value="jazzcash">JazzCash</option>
              <option value="easypaisa">EasyPaisa</option>
              <option value="bank">Bank Transfer</option>
              <option value="credit">Credit (Adjustment)</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Reason for Return</label>
        <input type="text" name="reason" placeholder="e.g. Defective, Wrong item, Customer changed mind..."
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
        <textarea name="notes" rows="2" placeholder="Additional notes..."
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none"></textarea>
      </div>
    </div>

    <div class="flex items-center justify-between">
      <a href="{{ route('pos.show', $sale) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
        Cancel
      </a>
      <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
        Process Return & Restore Stock
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
