@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ now()->format('l, d M Y') }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('supply.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>New Supply Order
      </a>
      <a href="{{ route('day-end.create') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="sunset" class="w-4 h-4"></i>Day End
      </a>
    </div>
  </div>

  {{-- Today KPIs --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Sales</p>
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-4 h-4 text-green-600"></i>
        </div>
      </div>
      <p class="kpi-value money text-2xl font-bold text-slate-900">{{ formatCurrency($todaySales) }}</p>
      <p class="text-xs text-slate-400 mt-1">Supply orders today</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Purchases</p>
        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
          <i data-lucide="package-search" class="w-4 h-4 text-orange-600"></i>
        </div>
      </div>
      <p class="kpi-value money text-2xl font-bold text-orange-600">{{ formatCurrency($todayPurchases) }}</p>
      <p class="text-xs text-slate-400 mt-1">Murgi purchased today</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Expenses</p>
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i data-lucide="wallet" class="w-4 h-4 text-red-500"></i>
        </div>
      </div>
      <p class="kpi-value money text-2xl font-bold text-red-500">{{ formatCurrency($todayExpenses) }}</p>
      <p class="text-xs text-slate-400 mt-1">Daily expenses</p>
    </div>

    <div class="bg-{{ $todayProfit >= 0 ? 'green' : 'red' }}-50 rounded-xl border border-{{ $todayProfit >= 0 ? 'green' : 'red' }}-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-{{ $todayProfit >= 0 ? 'green' : 'red' }}-500 uppercase tracking-wider">Today's Profit</p>
        <div class="w-8 h-8 rounded-lg bg-{{ $todayProfit >= 0 ? 'green' : 'red' }}-100 flex items-center justify-center">
          <i data-lucide="{{ $todayProfit >= 0 ? 'circle-dollar-sign' : 'trending-down' }}" class="w-4 h-4 text-{{ $todayProfit >= 0 ? 'green' : 'red' }}-600"></i>
        </div>
      </div>
      <p class="kpi-value money text-2xl font-bold text-{{ $todayProfit >= 0 ? 'green-700' : 'red-700' }}">{{ ($todayProfit < 0 ? '− ' : '') . formatCurrency(abs($todayProfit)) }}</p>
      <p class="text-xs text-{{ $todayProfit >= 0 ? 'green' : 'red' }}-500 mt-1">{{ $todayProfit >= 0 ? 'Net profit' : 'Net loss' }} today</p>
    </div>
  </div>

  {{-- Monthly + Dues --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Month Sales</p>
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
          <i data-lucide="calendar" class="w-4 h-4 text-green-600"></i>
        </div>
      </div>
      <p class="money text-xl font-bold text-green-600">{{ formatCurrency($monthSales) }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ now()->format('M Y') }} supply revenue</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Month Profit</p>
        <div class="w-8 h-8 rounded-lg bg-{{ $monthProfit >= 0 ? 'green' : 'red' }}-100 flex items-center justify-center">
          <i data-lucide="bar-chart-2" class="w-4 h-4 text-{{ $monthProfit >= 0 ? 'green' : 'red' }}-600"></i>
        </div>
      </div>
      <p class="money text-xl font-bold text-{{ $monthProfit >= 0 ? 'green-700' : 'red-700' }}">{{ ($monthProfit < 0 ? '− ' : '') . formatCurrency(abs($monthProfit)) }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ $monthProfit >= 0 ? 'Profit' : 'Loss' }} this month</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Supplier Due</p>
        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
          <i data-lucide="truck" class="w-4 h-4 text-purple-600"></i>
        </div>
      </div>
      <p class="money text-xl font-bold text-slate-900">{{ formatCurrency($supplierDue) }}</p>
      <p class="text-xs text-slate-400 mt-1">Payable to suppliers</p>
    </div>

    <div class="bg-{{ $overdueCount > 0 ? 'red' : 'white' }}-50 rounded-xl border border-{{ $overdueCount > 0 ? 'red' : 'slate' }}-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-{{ $overdueCount > 0 ? 'red' : 'slate' }}-400 uppercase tracking-wider">Overdue</p>
        <div class="w-8 h-8 rounded-lg bg-{{ $overdueCount > 0 ? 'red' : 'slate' }}-100 flex items-center justify-center">
          <i data-lucide="alert-triangle" class="w-4 h-4 text-{{ $overdueCount > 0 ? 'red' : 'slate' }}-500"></i>
        </div>
      </div>
      <p class="text-xl font-bold text-{{ $overdueCount > 0 ? 'red-600' : 'slate-400' }}">{{ $overdueCount }}</p>
      <p class="text-xs text-{{ $overdueCount > 0 ? 'red' : 'slate' }}-400 mt-1">Overdue supply orders</p>
    </div>
  </div>

  {{-- Udhar --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-slate-900 flex items-center gap-2">
        <i data-lucide="book-open" class="w-4 h-4 text-green-600"></i> Credit Book (Udhar)
      </h2>
      <a href="{{ route('udhar.index') }}" class="text-xs text-green-600 hover:underline font-medium">View all →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="text-center p-3 rounded-lg bg-{{ $udharTotalDue > 0 ? 'red' : 'slate' }}-50 border border-{{ $udharTotalDue > 0 ? 'red' : 'slate' }}-100">
        <p class="money text-lg font-bold text-{{ $udharTotalDue > 0 ? 'red-600' : 'slate-400' }}">{{ formatCurrency($udharTotalDue) }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Total Pending</p>
      </div>
      <div class="text-center p-3 rounded-lg bg-{{ $udharOverdueCount > 0 ? 'red' : 'slate' }}-50 border border-{{ $udharOverdueCount > 0 ? 'red' : 'slate' }}-100">
        <p class="text-lg font-bold text-{{ $udharOverdueCount > 0 ? 'red-600' : 'slate-400' }}">{{ $udharOverdueCount }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Overdue</p>
      </div>
      <div class="text-center p-3 rounded-lg bg-{{ $udharDueTodayCount > 0 ? 'amber' : 'slate' }}-50 border border-{{ $udharDueTodayCount > 0 ? 'amber' : 'slate' }}-100">
        <p class="text-lg font-bold text-{{ $udharDueTodayCount > 0 ? 'amber-600' : 'slate-400' }}">{{ $udharDueTodayCount }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Due Today</p>
      </div>
      <div class="text-center p-3 rounded-lg bg-slate-50 border border-slate-100">
        <p class="text-lg font-bold text-slate-700">{{ $udharDueThisWeek }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Due This Week</p>
      </div>
    </div>
  </div>

  {{-- Chart + Overdue --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="font-semibold text-slate-900">6-Month Sales</h2>
          <p class="text-xs text-slate-400 mt-0.5">Supply revenue per month</p>
        </div>
        <p class="text-lg font-bold text-green-600">{{ formatCurrency(array_sum($monthlySales)) }}</p>
      </div>
      <canvas id="salesChart" height="140"></canvas>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900 flex items-center gap-2">
          <i data-lucide="clock" class="w-4 h-4 text-red-500"></i> Overdue Payments
        </h2>
        <a href="{{ route('supply.index') }}" class="text-xs text-green-600 hover:underline font-medium">View all</a>
      </div>
      @forelse($overdueOrders as $o)
      <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 hover:bg-red-50 transition-colors">
        <div>
          <p class="text-sm font-medium text-slate-900">{{ $o->customer?->name ?? '—' }}</p>
          <p class="text-xs text-slate-400">{{ $o->invoice_number }} · Due {{ \Carbon\Carbon::parse($o->due_date)->diffForHumans() }}</p>
        </div>
        <div class="text-right">
          <p class="text-sm font-bold text-red-600">{{ formatCurrency($o->amount_due) }}</p>
          <a href="{{ route('supply.show', $o) }}" class="text-xs text-green-600 hover:underline">View</a>
        </div>
      </div>
      @empty
      <div class="px-5 py-10 text-center">
        <i data-lucide="check-circle" class="w-8 h-8 text-green-400 mx-auto mb-2"></i>
        <p class="text-sm text-slate-400">No overdue payments</p>
      </div>
      @endforelse
    </div>
  </div>

  {{-- Recent Supply Orders --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Recent Supply Orders</h2>
      <a href="{{ route('supply.index') }}" class="text-xs text-green-600 hover:underline font-medium">View all →</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($recentOrders as $s)
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3">
              <p class="text-sm font-semibold text-green-600">{{ $s->invoice_number }}</p>
              <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($s->date)->format('d M Y') }}</p>
            </td>
            <td class="px-5 py-3 text-sm text-slate-700">{{ $s->customer?->name ?? '—' }}</td>
            <td class="px-5 py-3 text-sm font-semibold text-slate-900 text-right">{{ formatCurrency($s->total_amount) }}</td>
            <td class="px-5 py-3">
              @php $sc = ['paid'=>'green','partial'=>'yellow','unpaid'=>'red'][$s->payment_status] ?? 'slate' @endphp
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $sc }}-100 text-{{ $sc }}-700">
                <span class="w-1.5 h-1.5 rounded-full bg-{{ $sc }}-500"></span>{{ ucfirst($s->payment_status) }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <a href="{{ route('supply.show', $s) }}" class="text-slate-400 hover:text-green-600 transition-colors">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">No supply orders yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@push('scripts')
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: @json($monthlyLabels),
        datasets: [{ label: 'Sales (PKR)', data: @json($monthlySales), backgroundColor: 'rgba(22,163,74,0.7)', borderColor: '#16a34a', borderWidth: 0, borderRadius: 6 }]
    },
    options: { responsive: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' PKR ' + ctx.raw.toLocaleString() } } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v >= 1000 ? (v/1000).toFixed(0)+'k' : v, color: '#94a3b8' }, grid: { color: '#f1f5f9' }, border: { display: false } }, x: { grid: { display: false }, ticks: { color: '#94a3b8' }, border: { display: false } } } }
});
</script>
@endpush
@endsection
