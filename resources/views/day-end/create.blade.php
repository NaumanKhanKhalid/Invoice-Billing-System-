@extends('layouts.app')
@section('title','Day End Entry')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('day-end.index') }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-slate-700 hover:border-slate-300 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <div>
        <h1 class="text-xl font-bold text-slate-900">Close Day</h1>
        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
      </div>
    </div>
    <form method="GET" class="flex items-center gap-2">
      <input type="date" name="date" value="{{ $date }}"
             class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <button type="submit" class="px-3 py-1.5 text-sm bg-slate-700 hover:bg-slate-800 text-white rounded-lg transition-colors">Load</button>
    </form>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 flex items-start gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form method="POST" action="{{ route('day-end.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    {{-- Top row: Opening Stock | Today's Purchases | Supply --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

      {{-- Opening Stock --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 bg-slate-50">
          <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i data-lucide="package" class="w-3.5 h-3.5 text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-800">Opening Stock</p>
            <p class="text-xs text-slate-400">Yesterday's closing</p>
          </div>
        </div>
        <div class="p-4 space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Live Stock (kg)</label>
            <input type="number" name="opening_stock_live_kg"
                   value="{{ old('opening_stock_live_kg', $prevRecord?->closing_stock_live_kg ?? 0) }}"
                   step="0.001" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-slate-50">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Dressed Stock (kg)</label>
            <input type="number" name="opening_stock_dressed_kg"
                   value="{{ old('opening_stock_dressed_kg', $prevRecord?->closing_stock_dressed_kg ?? 0) }}"
                   step="0.001" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-slate-50">
          </div>
        </div>
      </div>

      {{-- Today's Purchases --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-blue-100 bg-blue-50">
          <div class="w-7 h-7 rounded-lg bg-blue-200 flex items-center justify-center flex-shrink-0">
            <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-blue-700"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-blue-900">Today's Purchases</p>
            <p class="text-xs text-blue-500">From purchase orders</p>
          </div>
        </div>
        <div class="p-4 space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Live Weight (kg)</label>
            <input type="number" name="total_purchased_live_kg"
                   value="{{ old('total_purchased_live_kg', $purchaseLiveKg) }}"
                   step="0.001" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-300 outline-none">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Purchase Cost (PKR)</label>
            <input type="number" name="purchase_cost"
                   value="{{ old('purchase_cost', $purchaseCost) }}"
                   step="0.01" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-300 outline-none">
          </div>
        </div>
      </div>

      {{-- Supply --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-green-100 bg-green-50">
          <div class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center flex-shrink-0">
            <i data-lucide="truck" class="w-3.5 h-3.5 text-green-700"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-green-900">Hotel / Company Supply</p>
            <p class="text-xs text-green-500">From supply orders</p>
          </div>
        </div>
        <div class="p-4 space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Dressed Weight (kg)</label>
            <input type="number" name="total_supply_dressed_kg"
                   value="{{ old('total_supply_dressed_kg', $supplyKg) }}"
                   step="0.001" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Revenue (PKR)</label>
            <input type="number" name="total_supply_revenue"
                   value="{{ old('total_supply_revenue', $supplyTotal) }}"
                   step="0.01" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
        </div>
      </div>
    </div>

    {{-- Middle row: Counter Cash | Closing Stock --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

      {{-- Counter Cash --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-yellow-100 bg-yellow-50">
          <div class="w-7 h-7 rounded-lg bg-yellow-200 flex items-center justify-center flex-shrink-0">
            <i data-lucide="banknote" class="w-3.5 h-3.5 text-yellow-700"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-yellow-900">Counter Cash (Retail)</p>
            <p class="text-xs text-yellow-600">Cash collected at counter today</p>
          </div>
        </div>
        <div class="p-4">
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Counter Cash (PKR) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">PKR</span>
            <input type="number" name="counter_cash"
                   value="{{ old('counter_cash', 0) }}"
                   step="0.01" min="0" required
                   class="w-full pl-14 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-yellow-300 outline-none">
          </div>
        </div>
      </div>

      {{-- Closing Stock --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-100 bg-slate-50">
          <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i data-lucide="archive" class="w-3.5 h-3.5 text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-800">Closing Stock</p>
            <p class="text-xs text-slate-400">Remaining stock at end of day</p>
          </div>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Live (kg)</label>
            <input type="number" name="closing_stock_live_kg"
                   value="{{ old('closing_stock_live_kg', 0) }}"
                   step="0.001" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Dressed (kg)</label>
            <input type="number" name="closing_stock_dressed_kg"
                   value="{{ old('closing_stock_dressed_kg', 0) }}"
                   step="0.001" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Value (PKR)</label>
            <input type="number" name="closing_stock_value"
                   value="{{ old('closing_stock_value', 0) }}"
                   step="0.01" min="0" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
        </div>
      </div>
    </div>

    {{-- Bottom row: Waste | Expenses | Notes --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

      {{-- Waste --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-4">
          <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400"></i>
          <p class="text-sm font-semibold text-slate-800">Loss / Waste</p>
        </div>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Dead (kg)</label>
            <input type="number" name="dead_kg" value="{{ old('dead_kg', 0) }}" step="0.001" min="0"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-red-200 outline-none">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Spoilage (kg)</label>
            <input type="number" name="spoilage_kg" value="{{ old('spoilage_kg', 0) }}" step="0.001" min="0"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-red-200 outline-none">
          </div>
        </div>
      </div>

      {{-- Expenses --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-4">
          <i data-lucide="receipt" class="w-4 h-4 text-orange-400"></i>
          <p class="text-sm font-semibold text-slate-800">Today's Expenses</p>
        </div>
        <label class="block text-xs font-medium text-slate-500 mb-1.5">Total Expenses (PKR) <span class="text-red-500">*</span></label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">PKR</span>
          <input type="number" name="total_expenses"
                 value="{{ old('total_expenses', $totalExpenses) }}"
                 step="0.01" min="0" required
                 class="w-full pl-14 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-orange-200 outline-none">
        </div>
        <p class="text-xs text-slate-400 mt-2">Expenses module se auto-filled</p>
      </div>

      {{-- Notes --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-4">
          <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
          <p class="text-sm font-semibold text-slate-800">Notes</p>
        </div>
        <textarea name="notes" rows="4" placeholder="Any special notes..."
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none">{{ old('notes') }}</textarea>
      </div>
    </div>

    {{-- Live Profit Preview --}}
    <div class="bg-slate-900 rounded-xl p-5 text-white" id="profit-preview">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Projected P&L Summary</p>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
        <div>
          <p class="text-xs text-slate-400">Total Revenue</p>
          <p class="text-lg font-bold text-green-400" id="prev-revenue">PKR 0</p>
        </div>
        <div>
          <p class="text-xs text-slate-400">Total Cost</p>
          <p class="text-lg font-bold text-red-400" id="prev-cost">PKR 0</p>
        </div>
        <div>
          <p class="text-xs text-slate-400">Expenses</p>
          <p class="text-lg font-bold text-orange-400" id="prev-expenses">PKR 0</p>
        </div>
        <div>
          <p class="text-xs text-slate-400">Net Profit</p>
          <p class="text-xl font-bold" id="prev-profit">PKR 0</p>
        </div>
      </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-between pt-1 pb-6">
      <a href="{{ route('day-end.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
      <button type="submit"
              class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        Close Day
      </button>
    </div>
  </form>

  <script>
  function updatePreview() {
    const supplyRev  = parseFloat(document.querySelector('[name=total_supply_revenue]')?.value) || 0;
    const counterCash= parseFloat(document.querySelector('[name=counter_cash]')?.value) || 0;
    const purchCost  = parseFloat(document.querySelector('[name=purchase_cost]')?.value) || 0;
    const expenses   = parseFloat(document.querySelector('[name=total_expenses]')?.value) || 0;
    const revenue    = supplyRev + counterCash;
    const profit     = revenue - purchCost - expenses;
    const fmt = v => 'PKR ' + Math.abs(v).toLocaleString('en-PK', {minimumFractionDigits:0, maximumFractionDigits:0});
    document.getElementById('prev-revenue').textContent  = fmt(revenue);
    document.getElementById('prev-cost').textContent     = fmt(purchCost);
    document.getElementById('prev-expenses').textContent = fmt(expenses);
    const profitEl = document.getElementById('prev-profit');
    profitEl.textContent = (profit < 0 ? '- ' : '') + fmt(profit);
    profitEl.className = 'text-xl font-bold ' + (profit >= 0 ? 'text-green-400' : 'text-red-400');
  }
  document.querySelectorAll('input[type=number]').forEach(el => el.addEventListener('input', updatePreview));
  updatePreview();
  </script>
</div>
@endsection
