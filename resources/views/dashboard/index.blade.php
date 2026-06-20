@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="space-y-6">
  <div><h1 class="text-2xl font-bold text-slate-900">Dashboard</h1><p class="text-sm text-slate-500 mt-0.5">Anwar Chicken Center — Today: {{ now()->format('d M Y') }}</p></div>

  {{-- Today's stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
      <p class="text-xs text-green-600 font-medium uppercase tracking-wider">Today Sales</p>
      <p class="text-xl font-bold text-green-700 mt-1">PKR {{ number_format($todaySales,0) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 text-center shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Today Purchases</p>
      <p class="text-xl font-bold text-slate-900 mt-1">PKR {{ number_format($todayPurchases,0) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 text-center shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Today Expenses</p>
      <p class="text-xl font-bold text-orange-600 mt-1">PKR {{ number_format($todayExpenses,0) }}</p>
    </div>
    <div class="bg-{{ $todayProfit>=0?'green':'red' }}-50 border border-{{ $todayProfit>=0?'green':'red' }}-200 rounded-xl p-4 text-center">
      <p class="text-xs text-{{ $todayProfit>=0?'green':'red' }}-600 font-medium uppercase tracking-wider">Today Profit</p>
      <p class="text-xl font-bold text-{{ $todayProfit>=0?'green-700':'red-700' }} mt-1">PKR {{ number_format(abs($todayProfit),0) }}</p>
    </div>
  </div>

  {{-- Monthly stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Month Sales</p>
      <p class="text-lg font-bold text-green-600 mt-1">PKR {{ number_format($monthSales,0) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Supplier Due</p>
      <p class="text-lg font-bold text-slate-900 mt-1">PKR {{ number_format($supplierDue,0) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Customer Due</p>
      <p class="text-lg font-bold text-orange-600 mt-1">PKR {{ number_format($customerDue,0) }}</p>
    </div>
    <div class="bg-{{ $overdueSales>0?'red':'white' }}-50 border border-{{ $overdueSales>0?'red':'slate' }}-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-{{ $overdueSales>0?'red':'slate' }}-500 font-medium uppercase tracking-wider">Overdue Sales</p>
      <p class="text-lg font-bold text-{{ $overdueSales>0?'red-600':'slate-400' }} mt-1">{{ $overdueSales }} orders</p>
    </div>
  </div>

  {{-- Alerts --}}
  @if($lowStock->isNotEmpty())
  <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-4 h-4 text-yellow-600"></i><p class="text-sm font-semibold text-yellow-800">Low Stock Alert</p></div>
    <div class="flex flex-wrap gap-2">
      @foreach($lowStock as $s)
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $s->chickenType?->name ?? '-' }}: {{ number_format($s->closing_stock_kg,1) }} kg</span>
      @endforeach
    </div>
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Sales chart --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <h2 class="font-semibold text-slate-900 mb-4">6-Month Sales Trend</h2>
      <canvas id="salesChart" height="100"></canvas>
    </div>

    {{-- Overdue orders --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Overdue Payments</h2>
        <a href="{{ route('sales.index') }}?status=unpaid" class="text-xs text-green-600 hover:underline">View all</a>
      </div>
      <table class="w-full">
        <tbody class="divide-y divide-slate-100">
          @forelse($overdueOrders as $o)
          <tr class="hover:bg-red-50">
            <td class="px-4 py-3">
              <p class="text-sm font-medium text-slate-900">{{ $o->customer?->name ?? 'Walk-in' }}</p>
              <p class="text-xs text-slate-500">{{ $o->invoice_number }}</p>
            </td>
            <td class="px-4 py-3 text-right">
              <p class="text-sm font-bold text-red-600">PKR {{ number_format($o->amount_due,0) }}</p>
              <p class="text-xs text-slate-400">Due {{ \Carbon\Carbon::parse($o->due_date)->diffForHumans() }}</p>
            </td>
          </tr>
          @empty
          <tr><td colspan="2" class="px-4 py-6 text-center text-slate-400 text-sm">No overdue payments.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Recent sales --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Recent Sales</h2>
      <a href="{{ route('sales.index') }}" class="text-xs text-green-600 hover:underline">View all</a>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Invoice</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($recentSales as $s)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3"><a href="{{ route('sales.show',$s) }}" class="text-sm font-medium text-green-600 hover:underline">{{ $s->invoice_number }}</a><p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($s->date)->format('d M Y') }}</p></td>
          <td class="px-4 py-3 text-sm text-slate-700">{{ $s->customer?->name ?? 'Walk-in' }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">PKR {{ number_format($s->total_amount,0) }}</td>
          <td class="px-4 py-3">
            @if($s->payment_status==='paid')<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Paid</span>
            @elseif($s->payment_status==='partial')<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Partial</span>
            @else<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Unpaid</span>@endif
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400 text-sm">No sales yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('salesChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: @json($monthlyLabels),
        datasets: [{ label: 'Sales (PKR)', data: @json($monthlySales), borderColor: '#16a34a', backgroundColor: '#16a34a22', tension: 0.4, fill: true }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
@endsection
