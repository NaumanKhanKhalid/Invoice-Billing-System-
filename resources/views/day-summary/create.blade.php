@extends('layouts.app')
@section('title', 'Close Day — ' . \Carbon\Carbon::parse($date)->format('d M Y'))
@section('content')

<div class="space-y-6" x-data="{
    openingCash: {{ $openingCash }},
    posRevenue: {{ $posRevenue }},
    totalExpenses: {{ $totalExpenses }},
    cashReceived: {{ $existing?->cash_received ?? 0 }},
    get expectedCash() { return this.openingCash + this.posRevenue - this.totalExpenses; },
    get difference() { return this.cashReceived - this.expectedCash; },
    get diffLabel() { return this.difference > 0 ? 'Surplus' : (this.difference < 0 ? 'Shortage' : 'Exact'); }
}">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-3">
      <a href="{{ route('day-summary.index') }}"
         class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-slate-700 transition-colors">
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

  @if($existing?->is_closed)
  <div class="bg-slate-100 border border-slate-300 rounded-xl px-4 py-3 flex items-center gap-3 text-slate-700 text-sm">
    <i data-lucide="lock" class="w-4 h-4"></i>
    This day is <strong>closed</strong> on {{ $existing->closed_at->format('d M Y H:i') }}.
    <a href="{{ route('day-summary.show', $existing) }}" class="ml-auto text-green-600 hover:underline font-medium">View Details →</a>
  </div>
  @endif

  {{-- Auto-fetched summary cards --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">POS Transactions</p>
      <p class="text-2xl font-bold text-slate-900">{{ $posSalesCount }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Today's sales</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-4">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">POS Revenue</p>
      <p class="kpi-value money text-2xl font-bold text-green-700">PKR {{ number_format($posRevenue) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Auto from POS</p>
    </div>
    <div class="bg-white rounded-xl border border-orange-200 shadow-sm p-4">
      <p class="text-xs text-orange-500 font-semibold uppercase tracking-wider mb-1">Expenses</p>
      <p class="kpi-value money text-2xl font-bold text-orange-600">PKR {{ number_format($totalExpenses) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Auto from expenses</p>
    </div>
    <div class="bg-white rounded-xl border border-blue-200 shadow-sm p-4">
      <p class="text-xs text-blue-500 font-semibold uppercase tracking-wider mb-1">Purchases</p>
      <p class="kpi-value money text-2xl font-bold text-blue-600">PKR {{ number_format($purchaseCost) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Stock purchased</p>
    </div>
  </div>

  {{-- Form --}}
  <form method="POST" action="{{ route('day-summary.store') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    {{-- Cash Section --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="bg-slate-50 border-b border-slate-100 px-5 py-3 flex items-center gap-2">
        <i data-lucide="banknote" class="w-4 h-4 text-slate-600"></i>
        <span class="font-semibold text-slate-800">Cash Reconciliation</span>
      </div>
      <div class="p-5 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Opening Cash (PKR)</label>
            <input type="number" name="opening_cash" step="0.01" min="0" required
                   value="{{ old('opening_cash', $existing?->opening_cash ?? $openingCash) }}"
                   x-model.number="openingCash"
                   class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none"
                   placeholder="0">
            <p class="text-xs text-slate-400 mt-1">Cash at start of day (yesterday's closing)</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Cash Received / Counted (PKR)</label>
            <input type="number" name="cash_received" step="0.01" min="0" required
                   value="{{ old('cash_received', $existing?->cash_received ?? 0) }}"
                   x-model.number="cashReceived"
                   class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none"
                   placeholder="0">
            <p class="text-xs text-slate-400 mt-1">Physical cash counted at day end</p>
          </div>
        </div>

        {{-- Live Calculation --}}
        <div class="rounded-2xl p-4 border transition-colors"
             :class="difference > 0 ? 'bg-green-50 border-green-200' : (difference < 0 ? 'bg-red-50 border-red-200' : 'bg-slate-50 border-slate-100')">
          <div class="grid grid-cols-3 grid-stack-sm gap-4 text-center">
            <div>
              <p class="text-xs text-slate-500 mb-1 font-medium">Expected Cash</p>
              <p class="text-lg font-extrabold text-slate-900 tabular-nums" x-text="'PKR ' + Math.round(expectedCash).toLocaleString()"></p>
              <p class="text-[11px] text-slate-400">Opening + Revenue − Expenses</p>
            </div>
            <div>
              <p class="text-xs text-slate-500 mb-1 font-medium">Counted Cash</p>
              <p class="text-lg font-extrabold text-slate-900 tabular-nums" x-text="'PKR ' + Math.round(cashReceived).toLocaleString()"></p>
              <p class="text-[11px] text-slate-400">Physical count</p>
            </div>
            <div>
              <p class="text-xs text-slate-500 mb-1 font-medium">Difference</p>
              <p class="text-lg font-extrabold tabular-nums" :class="difference > 0 ? 'text-green-700' : difference < 0 ? 'text-red-600' : 'text-slate-700'"
                 x-text="(difference >= 0 ? '+' : '−') + 'PKR ' + Math.round(Math.abs(difference)).toLocaleString()"></p>
              <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full mt-0.5"
                    :class="difference > 0 ? 'bg-green-100 text-green-700' : difference < 0 ? 'bg-red-100 text-red-600' : 'bg-slate-200 text-slate-500'"
                    x-text="diffLabel"></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Notes --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="bg-slate-50 border-b border-slate-100 px-5 py-3 flex items-center gap-2">
        <i data-lucide="file-text" class="w-4 h-4 text-slate-600"></i>
        <span class="font-semibold text-slate-800">Notes <span class="text-slate-400 font-normal">(optional)</span></span>
      </div>
      <div class="p-5">
        <textarea name="notes" rows="3" placeholder="Any notes about today…"
                  class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-green-300 outline-none resize-none">{{ old('notes', $existing?->notes) }}</textarea>
      </div>
    </div>

    @if(!$existing?->is_closed)
    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('day-summary.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900 transition-colors">Cancel</a>
      <button type="submit"
              class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm">
        <i data-lucide="moon" class="w-4 h-4"></i>
        {{ $existing ? 'Update Summary' : 'Save & Close Day' }}
      </button>
    </div>
    @endif
  </form>
</div>
@endsection
