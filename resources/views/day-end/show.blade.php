@extends('layouts.app')
@section('title','Day Record — ' . \Carbon\Carbon::parse($dayEnd->date)->format('d M Y'))
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('day-end.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ \Carbon\Carbon::parse($dayEnd->date)->format('d M Y') }}</h1>
      </div>
    </div>
    @if(!$dayEnd->is_closed)
    <form method="POST" action="{{ route('day-end.close',$dayEnd) }}">
      @csrf
      <button type="submit" onclick="return confirm('Close this day? This cannot be undone.')"
        class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="lock" class="w-4 h-4"></i>Close Day
      </button>
    </form>
    @else
    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-sm">
      <i data-lucide="lock" class="w-4 h-4"></i>Closed {{ \Carbon\Carbon::parse($dayEnd->closed_at)->format('d M Y H:i') }}
    </span>
    @endif
  </div>

  {{-- P&L Summary --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase">Supply Revenue</p>
      <p class="text-xl font-bold text-blue-600 mt-1">{{ formatCurrency($dayEnd->total_supply_revenue) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">{{ formatKg($dayEnd->total_supply_dressed_kg) }} kg</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase">Counter Cash</p>
      <p class="text-xl font-bold text-slate-900 mt-1">{{ formatCurrency($dayEnd->counter_cash) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Retail</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase">Total Revenue</p>
      <p class="text-xl font-bold text-green-700 mt-1">{{ formatCurrency($dayEnd->total_revenue) }}</p>
    </div>
    <div class="bg-{{ $dayEnd->net_profit>=0?'green':'red' }}-50 rounded-xl border border-{{ $dayEnd->net_profit>=0?'green':'red' }}-200 shadow-sm p-4">
      <p class="text-xs text-{{ $dayEnd->net_profit>=0?'green':'red' }}-600 font-medium uppercase">Net Profit</p>
      <p class="text-xl font-bold text-{{ $dayEnd->net_profit>=0?'green-700':'red-700' }} mt-1">
        {{ $dayEnd->net_profit>=0?'+':'' }}{{ formatCurrency(abs($dayEnd->net_profit)) }}
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Stock --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <h2 class="font-semibold text-slate-900 mb-4">Stock Movement</h2>
      <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
          <tr><td class="py-2 text-slate-500">Opening Live Stock</td><td class="py-2 text-right font-medium">{{ formatKg($dayEnd->opening_stock_live_kg) }} kg</td></tr>
          <tr><td class="py-2 text-slate-500">Opening Dressed Stock</td><td class="py-2 text-right font-medium">{{ formatKg($dayEnd->opening_stock_dressed_kg) }} kg</td></tr>
          <tr><td class="py-2 text-slate-500">Purchased Live</td><td class="py-2 text-right font-medium text-blue-600">+{{ formatKg($dayEnd->total_purchased_live_kg) }} kg</td></tr>
          <tr><td class="py-2 text-slate-500">Supplied (Dressed)</td><td class="py-2 text-right font-medium text-red-500">-{{ formatKg($dayEnd->total_supply_dressed_kg) }} kg</td></tr>
          <tr><td class="py-2 text-slate-500">Dead / Spoilage</td><td class="py-2 text-right text-orange-500">-{{ formatKg($dayEnd->dead_kg+$dayEnd->spoilage_kg) }} kg</td></tr>
          <tr class="border-t-2 border-slate-200"><td class="py-2 font-semibold">Closing Live Stock</td><td class="py-2 text-right font-bold">{{ formatKg($dayEnd->closing_stock_live_kg) }} kg</td></tr>
          <tr><td class="py-2 font-semibold">Closing Dressed Stock</td><td class="py-2 text-right font-bold">{{ formatKg($dayEnd->closing_stock_dressed_kg) }} kg</td></tr>
          <tr><td class="py-2 text-slate-500">Closing Stock Value</td><td class="py-2 text-right font-medium text-green-600">{{ formatCurrency($dayEnd->closing_stock_value) }}</td></tr>
        </tbody>
      </table>
    </div>

    {{-- P&L Detail --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <h2 class="font-semibold text-slate-900 mb-4">Profit & Loss</h2>
      <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
          <tr><td class="py-2 text-slate-500">Supply Revenue</td><td class="py-2 text-right font-medium text-green-600">{{ formatCurrency($dayEnd->total_supply_revenue) }}</td></tr>
          <tr><td class="py-2 text-slate-500">Counter Cash</td><td class="py-2 text-right font-medium text-green-600">{{ formatCurrency($dayEnd->counter_cash) }}</td></tr>
          <tr class="bg-green-50"><td class="py-2 font-semibold text-green-700 px-2 rounded-l">Total Revenue</td><td class="py-2 text-right font-bold text-green-700 px-2 rounded-r">{{ formatCurrency($dayEnd->total_revenue) }}</td></tr>
          <tr><td class="py-2 text-slate-500">Purchase Cost</td><td class="py-2 text-right font-medium text-red-500">-{{ formatCurrency($dayEnd->purchase_cost) }}</td></tr>
          <tr><td class="py-2 text-slate-500">Closing Stock Value</td><td class="py-2 text-right font-medium text-green-600">+{{ formatCurrency($dayEnd->closing_stock_value) }}</td></tr>
          <tr><td class="py-2 text-slate-500">Net Cost</td><td class="py-2 text-right font-medium text-red-500">-{{ formatCurrency($dayEnd->total_cost) }}</td></tr>
          <tr class="bg-slate-50"><td class="py-2 font-semibold px-2 rounded-l">Gross Profit</td><td class="py-2 text-right font-bold px-2 rounded-r {{ $dayEnd->gross_profit>=0?'text-green-700':'text-red-600' }}">{{ formatCurrency($dayEnd->gross_profit) }}</td></tr>
          <tr><td class="py-2 text-slate-500">Total Expenses</td><td class="py-2 text-right font-medium text-orange-600">-{{ formatCurrency($dayEnd->total_expenses) }}</td></tr>
          <tr class="border-t-2 border-slate-300 bg-{{ $dayEnd->net_profit>=0?'green':'red' }}-50">
            <td class="py-3 font-bold text-lg px-2 rounded-l">Net Profit</td>
            <td class="py-3 text-right font-bold text-xl px-2 rounded-r {{ $dayEnd->net_profit>=0?'text-green-700':'text-red-600' }}">
              {{ $dayEnd->net_profit>=0?'+':'' }}{{ formatCurrency($dayEnd->net_profit) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  @if($dayEnd->notes)
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <p class="text-xs text-slate-500 mb-1">Notes</p>
    <p class="text-sm text-slate-700">{{ $dayEnd->notes }}</p>
  </div>
  @endif
</div>
@endsection
