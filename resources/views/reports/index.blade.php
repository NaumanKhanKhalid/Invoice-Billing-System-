@extends('layouts.app')
@section('title','Reports')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">Reports & Analytics</h1><p class="text-sm text-slate-500 mt-0.5">Business performance overview</p></div>
  </div>

  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
      <input type="date" name="from_date" value="{{ $from }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
      <input type="date" name="to_date" value="{{ $to }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    </div>
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Apply</button>
  </form>

  {{-- P&L Summary --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Sales Revenue</p>
      <p class="text-xl font-bold text-green-700 mt-1">{{ formatCurrency($salesTotal) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">{{ number_format($salesKg,1) }} kg sold</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Purchase Cost</p>
      <p class="text-xl font-bold text-red-600 mt-1">{{ formatCurrency($purchaseTotal) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">{{ number_format($purchaseKg,1) }} kg live</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Expenses + Salaries</p>
      <p class="text-xl font-bold text-orange-600 mt-1">{{ formatCurrency($expensesTotal+$salariesTotal) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Exp: {{ number_format($expensesTotal,0) }} | Sal: {{ number_format($salariesTotal,0) }}</p>
    </div>
    <div class="bg-{{ $netProfit>=0?'green':'red' }}-50 rounded-xl border border-{{ $netProfit>=0?'green':'red' }}-200 shadow-sm p-4">
      <p class="text-xs text-{{ $netProfit>=0?'green':'red' }}-600 font-medium uppercase tracking-wider">Net Profit</p>
      <p class="text-xl font-bold text-{{ $netProfit>=0?'green-700':'red-700' }} mt-1">{{ formatCurrency(abs($netProfit)) }}</p>
      <p class="text-xs text-{{ $netProfit>=0?'green':'red' }}-500 mt-0.5">{{ $netProfit>=0?'Profit':'Loss' }} | Gross: {{ number_format($grossProfit,0) }}</p>
    </div>
  </div>

  {{-- Chart --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h2 class="font-semibold text-slate-900 mb-4">6-Month Trend</h2>
    <canvas id="trendChart" height="80"></canvas>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Top Customers --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-900">Top Customers</h2></div>
      <table class="w-full">
        <thead class="bg-slate-50"><tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Orders</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($topCustomers as $c)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $c->customer?->name ?? 'Walk-in' }}</td>
            <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $c->orders }}</td>
            <td class="px-4 py-3 text-sm text-right font-medium text-green-600">{{ formatCurrency($c->total) }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400 text-sm">No sales in period.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Expenses by category --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-900">Expenses by Category</h2></div>
      <table class="w-full">
        <thead class="bg-slate-50"><tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Category</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">%</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          @php $expTotal = $expenseByCategory->sum('total') ?: 1; @endphp
          @forelse($expenseByCategory as $e)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 text-sm text-slate-700">{{ $e->category }}</td>
            <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($e->total) }}</td>
            <td class="px-4 py-3 text-sm text-right text-slate-500">{{ number_format(($e->total/$expTotal)*100,1) }}%</td>
          </tr>
          @empty
          <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400 text-sm">No expenses in period.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('trendChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($monthlyLabels),
        datasets: [
            { label: 'Sales', data: @json($monthlySales), backgroundColor: '#16a34a99', borderColor: '#16a34a', borderWidth: 1 },
            { label: 'Purchases', data: @json($monthlyPurchases), backgroundColor: '#dc262699', borderColor: '#dc2626', borderWidth: 1 },
            { label: 'Expenses', data: @json($monthlyExpenses), backgroundColor: '#f59e0b99', borderColor: '#f59e0b', borderWidth: 1 },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
@endsection
