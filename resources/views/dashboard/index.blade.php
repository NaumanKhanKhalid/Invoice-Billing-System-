@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
      <p class="text-sm text-slate-500 mt-0.5">Anwar Chicken Center — {{ now()->format('d M Y') }}</p>
    </div>
  </div>

  {{-- Quick Actions --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <a href="{{ route('supply.create') }}" class="group flex flex-col items-center gap-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl p-4 transition-colors shadow-sm">
      <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center group-hover:bg-white/30 transition-colors">
        <i data-lucide="plus" class="w-5 h-5"></i>
      </div>
      <span class="text-sm font-semibold">New Supply Order</span>
    </a>
    <a href="{{ route('purchases.create') }}" class="group flex flex-col items-center gap-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl p-4 transition-colors shadow-sm">
      <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center group-hover:bg-white/30 transition-colors">
        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
      </div>
      <span class="text-sm font-semibold">New Purchase</span>
    </a>
    <a href="{{ route('supply.schedule') }}" class="group flex flex-col items-center gap-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl p-4 transition-colors shadow-sm">
      <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center group-hover:bg-white/30 transition-colors">
        <i data-lucide="calendar-clock" class="w-5 h-5"></i>
      </div>
      <span class="text-sm font-semibold">Aaj ka Schedule</span>
    </a>
    <a href="{{ route('day-end.create') }}" class="group flex flex-col items-center gap-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl p-4 transition-colors shadow-sm">
      <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center group-hover:bg-white/30 transition-colors">
        <i data-lucide="moon" class="w-5 h-5"></i>
      </div>
      <span class="text-sm font-semibold">Din Band Karo</span>
    </a>
  </div>

  {{-- Today's stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
      <p class="text-xs text-green-600 font-medium uppercase tracking-wider">Today Supply</p>
      <p class="text-xl font-bold text-green-700 mt-1">{{ formatCurrency($todaySupply) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 text-center shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Counter Cash</p>
      <p class="text-xl font-bold text-slate-900 mt-1">{{ formatCurrency($todayCounter) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 text-center shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Today Purchases</p>
      <p class="text-xl font-bold text-orange-600 mt-1">{{ formatCurrency($todayPurchases) }}</p>
    </div>
    <div class="bg-{{ $todayProfit>=0?'green':'red' }}-50 border border-{{ $todayProfit>=0?'green':'red' }}-200 rounded-xl p-4 text-center">
      <p class="text-xs text-{{ $todayProfit>=0?'green':'red' }}-600 font-medium uppercase tracking-wider">Today Profit</p>
      <p class="text-xl font-bold text-{{ $todayProfit>=0?'green-700':'red-700' }} mt-1">{{ formatCurrency(abs($todayProfit)) }}</p>
    </div>
  </div>

  {{-- Monthly / balance stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Month Supply</p>
      <p class="text-lg font-bold text-green-600 mt-1">{{ formatCurrency($monthSupply) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Supplier Due</p>
      <p class="text-lg font-bold text-slate-900 mt-1">{{ formatCurrency($supplierDue) }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Customer Due</p>
      <p class="text-lg font-bold text-orange-600 mt-1">{{ formatCurrency($customerDue) }}</p>
    </div>
    <div class="bg-{{ $overdueCount>0?'red':'white' }}-50 border border-{{ $overdueCount>0?'red':'slate' }}-200 rounded-xl p-4 shadow-sm">
      <p class="text-xs text-{{ $overdueCount>0?'red':'slate' }}-500 font-medium uppercase tracking-wider">Overdue Orders</p>
      <p class="text-lg font-bold text-{{ $overdueCount>0?'red-600':'slate-400' }} mt-1">{{ $overdueCount }} orders</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Sales chart --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <h2 class="font-semibold text-slate-900 mb-4">6-Month Supply Trend</h2>
      <canvas id="salesChart" height="100"></canvas>
    </div>

    {{-- Overdue orders --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Overdue Payments</h2>
        <a href="{{ route('supply.index') }}?status=unpaid" class="text-xs text-green-600 hover:underline">View all</a>
      </div>
      <table class="w-full">
        <tbody class="divide-y divide-slate-100">
          @forelse($overdueOrders as $o)
          <tr class="hover:bg-red-50">
            <td class="px-4 py-3">
              <p class="text-sm font-medium text-slate-900">{{ $o->customer?->name ?? '—' }}</p>
              <p class="text-xs text-slate-500">{{ $o->invoice_number }}</p>
            </td>
            <td class="px-4 py-3 text-right">
              <p class="text-sm font-bold text-red-600">{{ formatCurrency($o->amount_due) }}</p>
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

  {{-- Recent supply orders --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Recent Supply Orders</h2>
      <a href="{{ route('supply.index') }}" class="text-xs text-green-600 hover:underline">View all</a>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Invoice</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($recentOrders as $s)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3"><a href="{{ route('supply.show',$s) }}" class="text-sm font-medium text-green-600 hover:underline">{{ $s->invoice_number }}</a><p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($s->date)->format('d M Y') }}</p></td>
          <td class="px-4 py-3 text-sm text-slate-700">{{ $s->customer?->name ?? '—' }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($s->total_amount) }}</td>
          <td class="px-4 py-3">
            @if($s->payment_status==='paid')<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Paid</span>
            @elseif($s->payment_status==='partial')<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Partial</span>
            @else<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Unpaid</span>@endif
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400 text-sm">No supply orders yet.</td></tr>
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
        datasets: [{ label: 'Supply Revenue (PKR)', data: @json($monthlySales), borderColor: '#16a34a', backgroundColor: '#16a34a22', tension: 0.4, fill: true }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
@endsection
