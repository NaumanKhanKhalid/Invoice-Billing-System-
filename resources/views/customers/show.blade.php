@extends('layouts.app')
@section('title', $customer->name)
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $customer->name }}</h1>
                <p class="text-sm text-slate-500">{{ $customer->phone }}@if($customer->address) · {{ $customer->address }}@endif</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit
            </a>
            <form method="POST" action="{{ route('customers.toggle-blacklist', $customer) }}">
                @csrf
                @if(!$customer->is_blacklisted)
                <input type="hidden" name="reason" value="">
                <button type="submit" onclick="this.previousElementSibling.value = prompt('Reason for blacklisting (optional):') || ''" class="inline-flex items-center gap-2 bg-white border border-red-200 hover:bg-red-50 text-red-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i data-lucide="ban" class="w-4 h-4"></i> Blacklist
                </button>
                @else
                <button type="submit" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Remove Blacklist
                </button>
                @endif
            </form>
        </div>
    </div>

    @if($customer->is_blacklisted)
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
        <i data-lucide="ban" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="font-medium">This customer is blacklisted</p>
            @if($customer->blacklist_reason)<p class="mt-0.5 text-red-600">Reason: {{ $customer->blacklist_reason }}</p>@endif
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Billed</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">PKR {{ number_format($totalBilled, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Paid</p>
            <p class="text-2xl font-bold text-green-600 mt-1">PKR {{ number_format($totalPaid, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Outstanding</p>
            <p class="text-2xl font-bold text-red-600 mt-1">PKR {{ number_format($outstanding, 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Sales History</h2>
            <span class="text-sm text-slate-500">Credit: {{ $customer->credit_days }} days</span>
        </div>
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Invoice #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Chicken Type</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Weight (kg)</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Paid</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Due</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $order->invoice_number }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $order->chickenType->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-right text-slate-700">{{ number_format($order->net_weight_kg ?? $order->total_weight_kg ?? 0, 1) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">PKR {{ number_format($order->total_amount, 0) }}</td>
                    <td class="px-4 py-3 text-sm text-right text-green-600">PKR {{ number_format($order->amount_paid, 0) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-red-600">PKR {{ number_format($order->amount_due, 0) }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $order->payment_status === 'paid' ? 'badge-green' : ($order->payment_status === 'partial' ? 'badge-yellow' : 'badge-red') }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400 text-sm">No sales orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
