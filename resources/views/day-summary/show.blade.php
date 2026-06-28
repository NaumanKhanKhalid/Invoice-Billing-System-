@extends('layouts.app')
@section('title', 'Day Summary — ' . $daySummary->date->format('d M Y'))
@section('content')

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('day-summary.index') }}"
         class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-slate-700 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $daySummary->date->format('d M Y') }}</h1>
        <p class="text-sm text-slate-500">{{ $daySummary->date->format('l') }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2 no-print">
      <button onclick="window.print()"
              class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i>Print
      </button>
      @if(!$daySummary->is_closed)
      <a href="{{ route('day-summary.create', ['date' => $daySummary->date->toDateString()]) }}"
         class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="pencil" class="w-4 h-4"></i>Edit
      </a>
      <form method="POST" action="{{ route('day-summary.close', $daySummary) }}"
            data-confirm-title="Lock Day?" data-confirm-message="Day will be locked and cannot be edited. Continue?" data-confirm-text="Yes, Lock" data-confirm-danger="true">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <i data-lucide="lock" class="w-4 h-4"></i>Lock Day
        </button>
      </form>
      @else
      <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-sm">
        <i data-lucide="lock" class="w-4 h-4"></i>Locked {{ $daySummary->closed_at->format('d M H:i') }}
      </span>
      @endif
    </div>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-4 text-center">
      <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">POS Revenue</p>
      <p class="text-2xl font-bold text-green-700">PKR {{ number_format($daySummary->pos_revenue) }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ $daySummary->pos_sales_count }} transactions</p>
    </div>
    <div class="bg-white rounded-xl border border-orange-200 shadow-sm p-4 text-center">
      <p class="text-xs text-orange-500 font-semibold uppercase tracking-wider mb-1">Expenses</p>
      <p class="text-2xl font-bold text-orange-600">PKR {{ number_format($daySummary->total_expenses) }}</p>
      <p class="text-xs text-slate-400 mt-1">Total outflow</p>
    </div>
    <div class="bg-white rounded-xl border border-blue-200 shadow-sm p-4 text-center">
      <p class="text-xs text-blue-500 font-semibold uppercase tracking-wider mb-1">Cash Received</p>
      <p class="text-2xl font-bold text-blue-700">PKR {{ number_format($daySummary->cash_received) }}</p>
      @php $diff = $daySummary->cash_difference; @endphp
      <p class="text-xs font-medium mt-1 {{ $diff >= 0 ? 'text-green-500' : 'text-red-500' }}">
        {{ $diff >= 0 ? '+' : '' }}PKR {{ number_format(abs($diff)) }} {{ $diff > 0 ? 'Surplus' : ($diff < 0 ? 'Shortage' : 'Exact') }}
      </p>
    </div>
    <div class="bg-{{ $daySummary->net_profit >= 0 ? 'green' : 'red' }}-50 rounded-xl border border-{{ $daySummary->net_profit >= 0 ? 'green' : 'red' }}-200 shadow-sm p-4 text-center">
      <p class="text-xs text-{{ $daySummary->net_profit >= 0 ? 'green' : 'red' }}-600 font-semibold uppercase tracking-wider mb-1">Net Profit</p>
      <p class="text-2xl font-bold text-{{ $daySummary->net_profit >= 0 ? 'green-700' : 'red-600' }}">
        {{ $daySummary->net_profit >= 0 ? '+' : '' }}PKR {{ number_format($daySummary->net_profit) }}
      </p>
      <p class="text-xs text-slate-400 mt-1">Revenue − Cost − Expenses</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- P&L Breakdown --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="bar-chart-2" class="w-4 h-4 text-slate-500"></i>Profit & Loss
      </h2>
      <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
          <tr>
            <td class="py-2.5 text-slate-500">POS Sales Revenue</td>
            <td class="py-2.5 text-right font-medium text-green-600">+PKR {{ number_format($daySummary->pos_revenue) }}</td>
          </tr>
          <tr>
            <td class="py-2.5 text-slate-500">Purchase Cost</td>
            <td class="py-2.5 text-right font-medium text-red-500">−PKR {{ number_format($daySummary->purchase_cost) }}</td>
          </tr>
          <tr>
            <td class="py-2.5 text-slate-500">Total Expenses</td>
            <td class="py-2.5 text-right font-medium text-orange-500">−PKR {{ number_format($daySummary->total_expenses) }}</td>
          </tr>
          <tr class="border-t-2 border-slate-200">
            <td class="py-3 font-bold text-base">Net Profit</td>
            <td class="py-3 text-right font-bold text-lg {{ $daySummary->net_profit >= 0 ? 'text-green-700' : 'text-red-600' }}">
              {{ $daySummary->net_profit >= 0 ? '+' : '' }}PKR {{ number_format($daySummary->net_profit) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Cash Reconciliation --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="banknote" class="w-4 h-4 text-slate-500"></i>Cash Reconciliation
      </h2>
      <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
          <tr>
            <td class="py-2.5 text-slate-500">Opening Cash</td>
            <td class="py-2.5 text-right font-medium">PKR {{ number_format($daySummary->opening_cash) }}</td>
          </tr>
          <tr>
            <td class="py-2.5 text-slate-500">+ POS Revenue</td>
            <td class="py-2.5 text-right font-medium text-green-600">+PKR {{ number_format($daySummary->pos_revenue) }}</td>
          </tr>
          <tr>
            <td class="py-2.5 text-slate-500">− Expenses</td>
            <td class="py-2.5 text-right font-medium text-orange-500">−PKR {{ number_format($daySummary->total_expenses) }}</td>
          </tr>
          <tr class="bg-slate-50">
            <td class="py-2.5 font-semibold px-2 rounded-l">Expected Cash</td>
            <td class="py-2.5 text-right font-bold px-2 rounded-r">PKR {{ number_format($daySummary->expected_cash) }}</td>
          </tr>
          <tr>
            <td class="py-2.5 text-slate-500">Physical Count</td>
            <td class="py-2.5 text-right font-medium text-blue-600">PKR {{ number_format($daySummary->cash_received) }}</td>
          </tr>
          <tr class="border-t-2 border-slate-200">
            <td class="py-3 font-bold text-base">Difference</td>
            <td class="py-3 text-right font-bold text-lg {{ $diff >= 0 ? 'text-green-700' : 'text-red-600' }}">
              {{ $diff >= 0 ? '+' : '' }}PKR {{ number_format($diff) }}
              <span class="text-sm font-normal">{{ $diff > 0 ? '(Surplus)' : ($diff < 0 ? '(Shortage)' : '(Exact)') }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Today's POS Transactions --}}
  @if($posSales->isNotEmpty())
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 px-5 py-3 flex items-center justify-between">
      <h2 class="font-semibold text-slate-800 flex items-center gap-2">
        <i data-lucide="shopping-cart" class="w-4 h-4 text-slate-500"></i>Today's POS Sales
      </h2>
      <span class="text-xs text-slate-500">{{ $daySummary->pos_sales_count }} total transactions</span>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50 border-b border-slate-100">
        <tr>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Sale #</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
          <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">Total</th>
          <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">Paid</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Method</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($posSales as $sale)
        <tr class="hover:bg-slate-50 text-sm">
          <td class="px-4 py-2.5 font-medium text-slate-700">{{ $sale->sale_number }}</td>
          <td class="px-4 py-2.5 text-slate-600">{{ $sale->customer_name ?: '—' }}</td>
          <td class="px-4 py-2.5 text-right font-semibold text-slate-900">PKR {{ number_format($sale->total) }}</td>
          <td class="px-4 py-2.5 text-right text-green-600">PKR {{ number_format($sale->amount_paid) }}</td>
          <td class="px-4 py-2.5 text-slate-500">{{ $sale->payment_method ?? 'Cash' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($daySummary->pos_sales_count > 20)
    <div class="px-4 py-2 text-xs text-slate-400 border-t border-slate-100">Showing last 20 of {{ $daySummary->pos_sales_count }} transactions</div>
    @endif
  </div>
  @endif

  {{-- Today's Expenses --}}
  @if($expenses->isNotEmpty())
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 px-5 py-3 flex items-center gap-2">
      <i data-lucide="wallet" class="w-4 h-4 text-slate-500"></i>
      <h2 class="font-semibold text-slate-800">Today's Expenses</h2>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50 border-b border-slate-100">
        <tr>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Category</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
          <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($expenses as $expense)
        <tr class="hover:bg-slate-50 text-sm">
          <td class="px-4 py-2.5">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">{{ ucfirst($expense->category) }}</span>
          </td>
          <td class="px-4 py-2.5 text-slate-600">{{ $expense->description ?: '—' }}</td>
          <td class="px-4 py-2.5 text-right font-semibold text-orange-600">PKR {{ number_format($expense->amount) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  {{-- Notes --}}
  @if($daySummary->notes)
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <p class="text-xs text-slate-500 font-semibold uppercase mb-2">Notes</p>
    <p class="text-sm text-slate-700">{{ $daySummary->notes }}</p>
  </div>
  @endif

</div>

<style>
@media print {
  .no-print { display: none !important; }
  body { background: white; }
}
</style>
@endsection
