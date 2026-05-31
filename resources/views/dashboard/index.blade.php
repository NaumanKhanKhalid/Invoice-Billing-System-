@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Page header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
        </div>
        <a href="{{ route('invoices.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            New Invoice
        </a>
    </div>

    <!-- Overdue alert -->
    @if($overdueInvoices->count() > 0)
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
        <span>
            <strong>{{ $overdueInvoices->count() }} overdue invoice(s)</strong> require your attention.
            <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="underline ml-1">View all</a>
        </span>
    </div>
    @endif

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Clients"
            value="{{ number_format($totalClients) }}"
            icon="users"
            color="blue"
        />
        <x-stat-card
            title="Total Invoices"
            value="{{ number_format($totalInvoices) }}"
            icon="file-text"
            color="purple"
        />
        <x-stat-card
            title="Total Revenue"
            value="PKR {{ number_format($totalRevenue, 0) }}"
            icon="trending-up"
            color="green"
        />
        <x-stat-card
            title="Pending Amount"
            value="PKR {{ number_format(max(0,$pendingAmount), 0) }}"
            icon="clock"
            color="orange"
        />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Revenue Chart -->
        <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Revenue Overview</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Last 6 months</p>
                </div>
            </div>
            <canvas id="revenueChart" height="220"></canvas>
        </div>

        <!-- Overdue invoices panel -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Overdue Invoices</h2>
            @if($overdueInvoices->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-emerald-300"></i>
                    <p class="text-sm">No overdue invoices</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($overdueInvoices as $inv)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $inv->invoice_number }}</p>
                            <p class="text-xs text-slate-500">{{ $inv->client->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-red-600">PKR {{ number_format($inv->total, 0) }}</p>
                            <p class="text-xs text-slate-400">Due {{ $inv->due_date->format('d M') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Recent Invoices</h2>
            <a href="{{ route('invoices.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                View all →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentInvoices as $invoice)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $invoice->client->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $invoice->issue_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-medium text-slate-900">PKR {{ number_format($invoice->total, 0) }}</td>
                        <td class="px-5 py-3.5">
                            <x-badge :status="$invoice->status" />
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('invoices.show', $invoice) }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50 transition-all">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">No invoices yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($monthlyLabels),
        datasets: [{
            label: 'Revenue (PKR)',
            data: @json($monthlyRevenue),
            backgroundColor: 'rgba(99, 102, 241, 0.15)',
            borderColor: '#6366f1',
            borderWidth: 2,
            borderRadius: 6,
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
