@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Reports</h1>
            <p class="text-sm text-slate-500 mt-1">Business analytics and insights</p>
        </div>
    </div>

    <!-- Date filter -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                       class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                       class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                Apply Filter
            </button>
        </form>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-stat-card
            title="Revenue (Period)"
            value="PKR {{ number_format($totalRevenue, 0) }}"
            icon="trending-up"
            color="green"
        />
        <x-stat-card
            title="Pending (Period)"
            value="PKR {{ number_format($totalPending, 0) }}"
            icon="clock"
            color="orange"
        />
        <x-stat-card
            title="Paid Invoices"
            value="{{ $statusSummary['paid']->count ?? 0 }}"
            icon="check-circle-2"
            color="green"
        />
        <x-stat-card
            title="Overdue Invoices"
            value="{{ $statusSummary['overdue']->count ?? 0 }}"
            icon="alert-circle"
            color="red"
        />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <!-- Revenue chart -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Revenue by Month (Last 12 Months)</h2>
            <canvas id="revenueChart" height="250"></canvas>
        </div>

        <!-- Invoice status summary -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Invoice Status Summary</h2>
            <div class="space-y-3">
                @foreach(['draft','sent','paid','overdue','cancelled'] as $s)
                @php $item = $statusSummary[$s] ?? null; @endphp
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50">
                    <div class="flex items-center gap-3">
                        <x-badge :status="$s" />
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-slate-900">{{ $item ? number_format($item->count) : 0 }}</p>
                        <p class="text-xs text-slate-500">PKR {{ $item ? number_format($item->total, 0) : '0' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top clients -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Top 5 Clients by Revenue</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rank</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Company</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Billed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($topClients as $i => $client)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                                {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : ($i === 1 ? 'bg-slate-100 text-slate-600' : 'bg-orange-50 text-orange-600') }}">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('clients.show', $client) }}" class="font-medium text-slate-900 hover:text-indigo-600">
                                {{ $client->name }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $client->company_name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-right font-semibold text-slate-900">
                            PKR {{ number_format($client->invoices_sum_total ?? 0, 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-400">No data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly revenue table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Monthly Revenue Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Month</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Revenue</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @php $totalMonth = array_sum($monthlyData); @endphp
                    @foreach($monthlyLabels as $i => $label)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-slate-700">{{ $label }}</td>
                        <td class="px-5 py-3 text-right font-medium text-slate-900">PKR {{ number_format($monthlyData[$i], 0) }}</td>
                        <td class="px-5 py-3 text-right text-slate-500">
                            {{ $totalMonth > 0 ? number_format($monthlyData[$i] / $totalMonth * 100, 1) : '0.0' }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t border-slate-200">
                        <td class="px-5 py-3 font-bold text-slate-900">Total</td>
                        <td class="px-5 py-3 text-right font-bold text-slate-900">PKR {{ number_format($totalMonth, 0) }}</td>
                        <td class="px-5 py-3 text-right text-slate-500">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($monthlyLabels),
        datasets: [{
            label: 'Revenue (PKR)',
            data: @json($monthlyData),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#6366f1',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { callback: v => 'PKR ' + v.toLocaleString() }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
