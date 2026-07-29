@extends('layouts.app')
@section('title','Admin — Dashboard')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Admin Dashboard</h1>
      <p class="text-sm text-slate-500 mt-0.5">Platform overview — revenue, growth &amp; tenants</p>
    </div>
    <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="store" class="w-4 h-4"></i>Manage Tenants
    </a>
  </div>

  {{-- KPI cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">MRR (est.)</p>
        <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center"><i data-lucide="trending-up" class="w-4 h-4 text-green-600"></i></span>
      </div>
      <p class="text-2xl font-bold text-slate-900 mt-2 tabular-nums">PKR {{ number_format($stats['mrr']) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Active paid plans / month</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">This Month</p>
        <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center"><i data-lucide="banknote" class="w-4 h-4 text-blue-600"></i></span>
      </div>
      <p class="text-2xl font-bold text-slate-900 mt-2 tabular-nums">PKR {{ number_format($stats['revenue_month']) }}</p>
      <p class="text-xs text-slate-400 mt-0.5">Total: PKR {{ number_format($stats['revenue_total']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Active Shops</p>
        <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center"><i data-lucide="check-circle-2" class="w-4 h-4 text-green-600"></i></span>
      </div>
      <p class="text-2xl font-bold text-green-600 mt-2">{{ $stats['active'] }}<span class="text-base text-slate-400 font-medium"> / {{ $stats['total'] }}</span></p>
      <p class="text-xs text-red-500 mt-0.5">{{ $stats['expired'] }} expired</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">New (This Month)</p>
        <span class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center"><i data-lucide="user-plus" class="w-4 h-4 text-indigo-600"></i></span>
      </div>
      <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['new_this_month'] }}</p>
      <p class="text-xs text-slate-400 mt-0.5">New signups</p>
    </div>
  </div>

  {{-- Charts --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <h2 class="text-sm font-bold text-slate-800 mb-4">Revenue &amp; Signups — last 6 months</h2>
      <canvas id="trendChart" height="110"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <h2 class="text-sm font-bold text-slate-800 mb-4">Plan Distribution</h2>
      <canvas id="planChart" height="180"></canvas>
      <div class="mt-4 space-y-1.5">
        @foreach($planDist as $plan => $cnt)
        <div class="flex items-center justify-between text-xs">
          <span class="capitalize text-slate-600 font-medium">{{ $plan }}</span>
          <span class="font-bold text-slate-800">{{ $cnt }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Shop-type breakdown + Expiring --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <h2 class="text-sm font-bold text-slate-800 mb-4">Shops by Type</h2>
      @php $maxType = $typeDist->max() ?: 1; @endphp
      <div class="space-y-2.5">
        @forelse($typeDist as $type => $cnt)
        <div>
          <div class="flex items-center justify-between text-xs mb-1">
            <span class="capitalize font-medium text-slate-600">{{ $type }}</span>
            <span class="font-bold text-slate-700">{{ $cnt }}</span>
          </div>
          <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full rounded-full bg-green-500" style="width: {{ round($cnt / $maxType * 100) }}%"></div>
          </div>
        </div>
        @empty
        <p class="text-sm text-slate-400">No shops yet</p>
        @endforelse
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-slate-800">Expiring within 7 days</h2>
        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $expiringSoon->count() }}</span>
      </div>
      <div class="space-y-2">
        @forelse($expiringSoon as $t)
        <div class="flex items-center justify-between gap-2 bg-amber-50/60 border border-amber-100 rounded-lg px-3 py-2">
          <div class="min-w-0">
            <a href="{{ route('admin.tenants.show', $t) }}" class="text-sm font-medium text-slate-900 hover:text-green-600 truncate block">{{ $t->shop_name }}</a>
            <p class="text-xs text-slate-400">{{ $t->owner_name }}</p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <span class="text-xs text-amber-700 font-semibold whitespace-nowrap">{{ $t->plan_expires_at->format('d M') }}</span>
            @if($t->owner_phone)
            <a href="https://wa.me/{{ wa_number($t->owner_phone) }}?text={{ urlencode('Assalam o Alaikum ' . $t->owner_name . ' bhai! Aapka ShopSaas plan ' . $t->plan_expires_at->format('d M Y') . ' ko expire ho raha hai.') }}" target="_blank"
               class="inline-flex items-center px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-xs font-medium">WA</a>
            @endif
          </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 py-6 text-center">Koi plan is hafte expire nahi ho raha ✓</p>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Recent signups --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100"><h2 class="text-sm font-bold text-slate-800">Recent Signups</h2></div>
    <table class="w-full">
      <tbody class="divide-y divide-slate-100">
        @forelse($recent as $t)
        <tr class="hover:bg-slate-50">
          <td class="px-5 py-3">
            <a href="{{ route('admin.tenants.show', $t) }}" class="text-sm font-semibold text-slate-900 hover:text-green-600">{{ $t->shop_name }}</a>
            <p class="text-xs text-slate-400">{{ $t->owner_name }} · {{ ucfirst($t->shop_type) }}</p>
          </td>
          <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold uppercase {{ $t->plan === 'business' ? 'bg-purple-100 text-purple-700' : ($t->plan === 'pro' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">{{ $t->plan }}</span></td>
          <td class="px-5 py-3 text-right text-xs text-slate-400 whitespace-nowrap">{{ $t->created_at ? $t->created_at->format('d M Y') : '—' }}</td>
        </tr>
        @empty
        <tr><td class="px-5 py-8 text-center text-sm text-slate-400">No tenants yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Chart === 'undefined') return;
  const money = v => 'PKR ' + Number(v).toLocaleString();

  new Chart(document.getElementById('trendChart'), {
    data: {
      labels: @json($labels),
      datasets: [
        { type: 'bar', label: 'Revenue (PKR)', data: @json($revenue), backgroundColor: 'rgba(34,197,94,0.75)', borderRadius: 6, yAxisID: 'y', order: 2 },
        { type: 'line', label: 'Signups', data: @json($signups), borderColor: '#6366f1', backgroundColor: '#6366f1', tension: 0.35, yAxisID: 'y1', order: 1, pointRadius: 3 },
      ],
    },
    options: {
      responsive: true, maintainAspectRatio: true,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
        tooltip: { callbacks: { label: c => c.dataset.yAxisID === 'y' ? c.dataset.label + ': ' + money(c.raw) : c.dataset.label + ': ' + c.raw } } },
      scales: {
        y:  { beginAtZero: true, position: 'left', ticks: { callback: v => v >= 1000 ? (v/1000)+'k' : v, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
        y1: { beginAtZero: true, position: 'right', ticks: { precision: 0, font: { size: 10 } }, grid: { drawOnChartArea: false } },
        x:  { grid: { display: false }, ticks: { font: { size: 11 } } },
      },
    },
  });

  new Chart(document.getElementById('planChart'), {
    type: 'doughnut',
    data: {
      labels: @json($planDist->keys()),
      datasets: [{ data: @json($planDist->values()), backgroundColor: ['#94a3b8', '#3b82f6', '#a855f7', '#22c55e', '#f59e0b'], borderWidth: 0 }],
    },
    options: { responsive: true, cutout: '62%', plugins: { legend: { display: false } } },
  });
});
</script>
@endpush
@endsection
