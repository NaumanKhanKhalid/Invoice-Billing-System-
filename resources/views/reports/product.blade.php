@extends('layouts.app')
@section('title','Reports')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Reports & Analytics</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
    </div>
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
      <p class="text-xs text-slate-400 mt-1">{{ $salesCount }} sales · {{ formatCurrency($discountTotal) }} discount diya</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Purchase Cost</p>
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i data-lucide="shopping-cart" class="w-4 h-4 text-red-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-red-600">{{ formatCurrency($purchaseTotal) }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ formatCurrency($purchaseDue) }} due to suppliers</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Expenses & Salaries</p>
        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
          <i data-lucide="wallet" class="w-4 h-4 text-orange-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-orange-600">{{ formatCurrency($expensesTotal + $salariesTotal) }}</p>
      <p class="text-xs text-slate-400 mt-1">{{ formatCurrency($salariesTotal) }} salaries</p>
    </div>

    <div class="bg-{{ $netProfit >= 0 ? 'green' : 'red' }}-50 rounded-xl border border-{{ $netProfit >= 0 ? 'green' : 'red' }}-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-{{ $netProfit >= 0 ? 'green' : 'red' }}-500 uppercase tracking-wider">Net Profit</p>
        <div class="w-8 h-8 rounded-lg bg-{{ $netProfit >= 0 ? 'green' : 'red' }}-100 flex items-center justify-center">
          <i data-lucide="{{ $netProfit >= 0 ? 'circle-dollar-sign' : 'trending-down' }}" class="w-4 h-4 text-{{ $netProfit >= 0 ? 'green' : 'red' }}-600"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-{{ $netProfit >= 0 ? 'green-700' : 'red-700' }}">{{ ($netProfit < 0 ? '− ' : '') . formatCurrency(abs($netProfit)) }}</p>
      <p class="text-xs text-{{ $netProfit >= 0 ? 'green' : 'red' }}-500 mt-1">{{ $netProfit >= 0 ? 'Net profit' : 'Net loss' }} · returns {{ formatCurrency($returnsTotal) }}</p>
    </div>
  </div>

  {{-- Stock Valuation --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>Current Stock Value
    </h2>
    <div class="grid grid-cols-3 gap-4">
      <div class="text-center p-4 bg-slate-50 rounded-xl">
        <p class="text-xs text-slate-500 font-semibold uppercase mb-1">Cost Value</p>
        <p class="text-xl font-bold text-slate-700">{{ formatCurrency($stockValueCost) }}</p>
      </div>
      <div class="text-center p-4 bg-blue-50 rounded-xl">
        <p class="text-xs text-blue-500 font-semibold uppercase mb-1">Sale Value</p>
        <p class="text-xl font-bold text-blue-700">{{ formatCurrency($stockValueSale) }}</p>
      </div>
      <div class="text-center p-4 bg-green-50 rounded-xl">
        <p class="text-xs text-green-600 font-semibold uppercase mb-1">Potential Margin</p>
        <p class="text-xl font-bold text-green-700">{{ formatCurrency($stockValueSale - $stockValueCost) }}</p>
      </div>
    </div>
  </div>

  {{-- Monthly Trend Chart --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i data-lucide="bar-chart-3" class="w-4 h-4 text-slate-400"></i>6-Month Trend
    </h2>
    <div class="h-64"><canvas id="trendChart"></canvas></div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Top Products --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="bg-slate-50 border-b border-slate-100 px-5 py-3">
        <h2 class="font-semibold text-slate-800 flex items-center gap-2">
          <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i>Top Selling Products
        </h2>
      </div>
      @if($topProducts->isNotEmpty())
      <div class="divide-y divide-slate-100">
        @foreach($topProducts as $i => $p)
        <div class="px-5 py-3 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
            <div>
              <p class="text-sm font-medium text-slate-900">{{ $p->product_name }}</p>
              <p class="text-xs text-slate-400">{{ rtrim(rtrim(number_format($p->total_qty, 2), '0'), '.') }} sold</p>
            </div>
          </div>
          <p class="text-sm font-semibold text-green-600">{{ formatCurrency($p->total_revenue) }}</p>
        </div>
        @endforeach
      </div>
      @else
      <div class="px-5 py-10 text-center text-slate-400 text-sm">No sales in this period</div>
      @endif
    </div>

    <div class="space-y-6">
      {{-- Payment Methods --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 px-5 py-3">
          <h2 class="font-semibold text-slate-800 flex items-center gap-2">
            <i data-lucide="credit-card" class="w-4 h-4 text-slate-400"></i>Payment Methods
          </h2>
        </div>
        @if($paymentBreakdown->isNotEmpty())
        <div class="divide-y divide-slate-100">
          @foreach($paymentBreakdown as $pm)
          <div class="px-5 py-3 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-900">{{ payment_label($pm->payment_method) }}</p>
              <p class="text-xs text-slate-400">{{ $pm->orders }} sales</p>
            </div>
            <p class="text-sm font-semibold text-slate-700">{{ formatCurrency($pm->total) }}</p>
          </div>
          @endforeach
        </div>
        @else
        <div class="px-5 py-8 text-center text-slate-400 text-sm">No sales in this period</div>
        @endif
      </div>

      {{-- Expense Breakdown --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 px-5 py-3">
          <h2 class="font-semibold text-slate-800 flex items-center gap-2">
            <i data-lucide="wallet" class="w-4 h-4 text-slate-400"></i>Expenses by Category
          </h2>
        </div>
        @if($expenseByCategory->isNotEmpty())
        <div class="divide-y divide-slate-100">
          @foreach($expenseByCategory as $ec)
          <div class="px-5 py-3 flex items-center justify-between">
            <p class="text-sm font-medium text-slate-900">{{ ucwords(str_replace('_', ' ', $ec->category)) }}</p>
            <p class="text-sm font-semibold text-orange-600">{{ formatCurrency($ec->total) }}</p>
          </div>
          @endforeach
        </div>
        @else
        <div class="px-5 py-8 text-center text-slate-400 text-sm">No expenses in this period</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Low Stock Alert --}}
  @if($lowStockProducts->isNotEmpty())
  <div class="bg-white rounded-xl border border-red-100 shadow-sm overflow-hidden">
    <div class="bg-red-50 border-b border-red-100 px-5 py-3">
      <h2 class="font-semibold text-red-800 flex items-center gap-2">
        <i data-lucide="alert-triangle" class="w-4 h-4"></i>Low / Out of Stock ({{ $lowStockProducts->count() }})
      </h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Product</th>
            <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Stock</th>
            <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Alert Level</th>
            <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($lowStockProducts as $p)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $p->name }}</td>
            <td class="px-4 py-2.5 text-right font-bold {{ $p->stock_qty <= 0 ? 'text-red-600' : 'text-orange-600' }}">{{ rtrim(rtrim(number_format($p->stock_qty, 2), '0'), '.') }} {{ $p->unit }}</td>
            <td class="px-4 py-2.5 text-right text-slate-500">{{ $p->low_stock_alert }}</td>
            <td class="px-4 py-2.5 text-right">
              <a href="{{ route('products.show', $p) }}" class="text-xs text-blue-600 hover:underline">View</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof Chart === 'undefined') return;
  new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
      labels: @json($monthlyLabels),
      datasets: [
        { label: 'Sales',     data: @json($monthlySales),     backgroundColor: 'rgba(34,197,94,0.7)',  borderRadius: 4 },
        { label: 'Purchases', data: @json($monthlyPurchases), backgroundColor: 'rgba(239,68,68,0.6)',  borderRadius: 4 },
        { label: 'Expenses',  data: @json($monthlyExpenses),  backgroundColor: 'rgba(249,115,22,0.6)', borderRadius: 4 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
      scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }
    }
  });
});
</script>
@endsection
