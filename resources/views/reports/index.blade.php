@extends('layouts.app')
@section('title','Reports')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Reports & Analytics</h1>
      <p class="text-sm text-slate-500 mt-0.5">Business performance overview</p>
    </div>
    {{-- Date Filter --}}
    <form method="GET" class="flex flex-wrap items-end gap-2">
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
        <input type="date" name="from_date" value="{{ $from }}"
               class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
        <input type="date" name="to_date" value="{{ $to }}"
               class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      </div>
      <button type="submit"
              class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="filter" class="w-4 h-4"></i>Apply
      </button>
      <a href="{{ route('reports.index', ['from_date' => $from, 'to_date' => $to, 'export' => 'csv']) }}"
         class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="download" class="w-4 h-4"></i>Export CSV
      </a>
    </form>
  </div>

  {{-- P&L Summary Cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sales Revenue</p>
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
          <i data-lucide="trending-up" class="w-4 h-4 text-green-600"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-green-700">{{ formatCurrency($salesTotal) }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ number_format($salesKg, 1) }} kg sold</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Purchase Cost</p>
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i data-lucide="shopping-cart" class="w-4 h-4 text-red-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-red-600">{{ formatCurrency($purchaseTotal) }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ number_format($purchaseKg, 1) }} kg live</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Expenses & Salaries</p>
        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
          <i data-lucide="wallet" class="w-4 h-4 text-orange-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-orange-600">{{ formatCurrency($expensesTotal + $salariesTotal) }}</p>
      <p class="text-xs text-slate-400 mt-1">Exp: {{ formatCurrency($expensesTotal) }} · Sal: {{ formatCurrency($salariesTotal) }}</p>
    </div>

    <div class="bg-{{ $netProfit >= 0 ? 'green' : 'red' }}-50 rounded-xl border border-{{ $netProfit >= 0 ? 'green' : 'red' }}-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-{{ $netProfit >= 0 ? 'green' : 'red' }}-500 uppercase tracking-wider">Net Profit</p>
        <div class="w-8 h-8 rounded-lg bg-{{ $netProfit >= 0 ? 'green' : 'red' }}-100 flex items-center justify-center">
          <i data-lucide="{{ $netProfit >= 0 ? 'circle-dollar-sign' : 'trending-down' }}" class="w-4 h-4 text-{{ $netProfit >= 0 ? 'green' : 'red' }}-600"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-{{ $netProfit >= 0 ? 'green-700' : 'red-700' }}">{{ formatCurrency(abs($netProfit)) }}</p>
      <p class="text-xs text-{{ $netProfit >= 0 ? 'green' : 'red' }}-500 mt-1">{{ $netProfit >= 0 ? 'Profit' : 'Loss' }} · Gross: {{ formatCurrency($grossProfit) }}</p>
    </div>
  </div>

  {{-- Additional KPIs --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
        <i data-lucide="clock" class="w-5 h-5 text-blue-600"></i>
      </div>
      <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sales Due</p>
        <p class="text-lg font-bold text-blue-600 mt-0.5">{{ formatCurrency($salesDue) }}</p>
        <p class="text-xs text-slate-400">Pending collection</p>
      </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
        <i data-lucide="percent" class="w-5 h-5 text-purple-600"></i>
      </div>
      <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gross Margin</p>
        <p class="text-lg font-bold text-purple-600 mt-0.5">
          {{ $salesTotal > 0 ? number_format(($grossProfit / $salesTotal) * 100, 1) : 0 }}%
        </p>
        <p class="text-xs text-slate-400">Of sales revenue</p>
      </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
        <i data-lucide="scale" class="w-5 h-5 text-slate-600"></i>
      </div>
      <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Avg Sale/kg</p>
        <p class="text-lg font-bold text-slate-700 mt-0.5">
          {{ $salesKg > 0 ? formatCurrency($salesTotal / $salesKg) : '—' }}
        </p>
        <p class="text-xs text-slate-400">Per kg dressed weight</p>
      </div>
    </div>
  </div>

  {{-- 6-Month Chart --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="font-semibold text-slate-900">6-Month Trend</h2>
        <p class="text-xs text-slate-400 mt-0.5">Sales vs Purchases vs Expenses</p>
      </div>
      <div class="flex items-center gap-4 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>Sales</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>Purchases</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>Expenses</span>
      </div>
    </div>
    <canvas id="trendChart" height="80"></canvas>
  </div>

  {{-- Tables --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Top Customers --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
        <i data-lucide="crown" class="w-4 h-4 text-amber-500"></i>
        <h2 class="font-semibold text-slate-900">Top Customers</h2>
      </div>
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Orders</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Revenue</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($topCustomers as $i => $c)
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3">
              <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center
                {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400') }}">
                {{ $i + 1 }}
              </span>
            </td>
            <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $c->customer?->name ?? 'Walk-in' }}</td>
            <td class="px-5 py-3 text-sm text-right text-slate-500">{{ $c->orders }}</td>
            <td class="px-5 py-3 text-sm text-right font-semibold text-green-600">{{ formatCurrency($c->total) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="px-5 py-10 text-center">
              <i data-lucide="users" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
              <p class="text-sm text-slate-400">No sales in this period</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Expenses by Category --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
        <i data-lucide="pie-chart" class="w-4 h-4 text-orange-500"></i>
        <h2 class="font-semibold text-slate-900">Expenses by Category</h2>
      </div>
      @php $expTotal = $expenseByCategory->sum('total') ?: 1; @endphp
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Share</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($expenseByCategory as $e)
          @php $pct = ($e->total / $expTotal) * 100; @endphp
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>
                <span class="text-sm text-slate-700">{{ $e->category }}</span>
              </div>
              <div class="mt-1.5 h-1 bg-slate-100 rounded-full overflow-hidden w-full max-w-[140px]">
                <div class="h-full bg-orange-400 rounded-full" style="width: {{ $pct }}%"></div>
              </div>
            </td>
            <td class="px-5 py-3 text-sm text-right font-semibold text-slate-900">{{ formatCurrency($e->total) }}</td>
            <td class="px-5 py-3 text-right">
              <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">
                {{ number_format($pct, 1) }}%
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="3" class="px-5 py-10 text-center">
              <i data-lucide="receipt" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
              <p class="text-sm text-slate-400">No expenses in this period</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

@push('scripts')
<script>
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json($monthlyLabels),
        datasets: [
            {
                label: 'Sales',
                data: @json($monthlySales),
                backgroundColor: 'rgba(22,163,74,0.75)',
                borderColor: '#16a34a',
                borderWidth: 0,
                borderRadius: 4,
            },
            {
                label: 'Purchases',
                data: @json($monthlyPurchases),
                backgroundColor: 'rgba(220,38,38,0.75)',
                borderColor: '#dc2626',
                borderWidth: 0,
                borderRadius: 4,
            },
            {
                label: 'Expenses',
                data: @json($monthlyExpenses),
                backgroundColor: 'rgba(245,158,11,0.75)',
                borderColor: '#f59e0b',
                borderWidth: 0,
                borderRadius: 4,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { color: '#94a3b8', font: { size: 11 } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8', font: { size: 11 } }
            }
        }
    }
});
</script>
@endpush
@endsection
