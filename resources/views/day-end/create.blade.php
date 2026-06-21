@extends('layouts.app')
@section('title','Day End Entry')
@section('content')
<div class="max-w-3xl mx-auto space-y-5">
  <div class="flex items-center gap-3">
    <a href="{{ route('day-end.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Din Band Karo</h1>
      <p class="text-sm text-slate-500">Day End Entry — {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
    </div>
  </div>

  <form method="GET" class="flex gap-3 items-end">
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Date</label>
      <input type="date" name="date" value="{{ $date }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    </div>
    <button type="submit" class="bg-slate-700 text-white px-4 py-2 text-sm rounded-lg">Load</button>
  </form>

  @php
    $sup  = $supplyTotals[$ct->id] ?? ['dressed_kg'=>0,'revenue'=>0];
    $pur  = $purchaseTotals[$ct->id] ?? ['live_kg'=>0,'cost'=>0];
    $prev = $prevRecords[$ct->id] ?? null;
  @endphp
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="bg-slate-800 px-5 py-3">
      <h2 class="text-white font-semibold">{{ $ct->name }}</h2>
    </div>
    <form method="POST" action="{{ route('day-end.store') }}" class="p-6 space-y-5">
      @csrf
      <input type="hidden" name="date" value="{{ $date }}">

      {{-- Opening Stock (auto from yesterday) --}}
      <div class="bg-slate-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Opening Stock (from yesterday)</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Live Stock (kg)</label>
            <input type="number" name="opening_stock_live_kg" value="{{ old('opening_stock_live_kg.'.$ct->id, $prev?->closing_stock_live_kg ?? 0) }}" step="0.001" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Dressed Stock (kg)</label>
            <input type="number" name="opening_stock_dressed_kg" value="{{ old('opening_stock_dressed_kg.'.$ct->id, $prev?->closing_stock_dressed_kg ?? 0) }}" step="0.001" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
        </div>
      </div>

      {{-- Purchases today (auto-filled) --}}
      <div class="bg-blue-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Today's Purchases</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Live Weight Purchased (kg)</label>
            <input type="number" name="total_purchased_live_kg" value="{{ old('total_purchased_live_kg.'.$ct->id, $pur['live_kg']) }}" step="0.001" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Purchase Cost (PKR)</label>
            <input type="number" name="purchase_cost" value="{{ old('purchase_cost.'.$ct->id, $pur['cost']) }}" step="0.01" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          </div>
        </div>
      </div>

      {{-- Supply to hotels (auto-filled from supply orders) --}}
      <div class="bg-green-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-1">Supply to Hotels / Companies</h3>
        <p class="text-xs text-slate-500 mb-3">Auto-filled from today's supply orders</p>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Dressed Weight Supplied (kg)</label>
            <input type="number" name="total_supply_dressed_kg" value="{{ old('total_supply_dressed_kg.'.$ct->id, $sup['dressed_kg']) }}" step="0.001" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Supply Revenue (PKR)</label>
            <input type="number" name="total_supply_revenue" value="{{ old('total_supply_revenue.'.$ct->id, $sup['revenue']) }}" step="0.01" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          </div>
        </div>
      </div>

      {{-- Counter cash (retail) --}}
      <div class="bg-yellow-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-1">Counter Cash (Retail)</h3>
        <p class="text-xs text-slate-500 mb-3">Total cash collected at counter today</p>
        <div>
          <label class="block text-xs text-slate-500 mb-1">Counter Cash (PKR) <span class="text-red-500">*</span></label>
          <div class="relative max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="counter_cash" value="{{ old('counter_cash.'.$ct->id, 0) }}" step="0.01" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          </div>
        </div>
      </div>

      {{-- Closing stock --}}
      <div class="bg-slate-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Closing Stock (at Day End)</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Live Stock Remaining (kg)</label>
            <input type="number" name="closing_stock_live_kg" value="{{ old('closing_stock_live_kg.'.$ct->id, 0) }}" step="0.001" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Dressed Stock Remaining (kg)</label>
            <input type="number" name="closing_stock_dressed_kg" value="{{ old('closing_stock_dressed_kg.'.$ct->id, 0) }}" step="0.001" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs text-slate-500 mb-1">Closing Stock Value (PKR)</label>
            <div class="relative max-w-xs">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
              <input type="number" name="closing_stock_value" value="{{ old('closing_stock_value.'.$ct->id, 0) }}" step="0.01" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
            </div>
          </div>
        </div>
      </div>

      {{-- Waste --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Dead (kg)</label>
          <input type="number" name="dead_kg" value="{{ old('dead_kg', 0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Spoilage (kg)</label>
          <input type="number" name="spoilage_kg" value="{{ old('spoilage_kg', 0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>

      {{-- Expenses --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Total Expenses Today (PKR)</label>
        <p class="text-xs text-slate-500 mb-1">All expenses for today from Expenses module</p>
        <div class="relative max-w-xs">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
          <input type="number" name="total_expenses" value="{{ old('total_expenses', $totalExpenses) }}" step="0.01" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('notes') }}</textarea>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Save Day End</button>
      </div>
    </form>
  </div>
  @endforeach
</div>
@endsection
