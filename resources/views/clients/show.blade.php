@extends('layouts.app')
@section('title', $client->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('clients.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $client->name }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ $client->company_name ?? $client->email }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.edit', $client) }}"
               class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                Edit
            </a>
            <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                New Invoice
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <!-- Client info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
                    {{ strtoupper(substr($client->name, 0, 2)) }}
                </div>
                <div>
                    <p class="font-semibold text-slate-900">{{ $client->name }}</p>
                    <x-badge :status="$client->is_active ? 'active' : 'inactive'" />
                </div>
            </div>

            <div class="space-y-2 text-sm">
                @if($client->company_name)
                <div class="flex items-center gap-2 text-slate-600">
                    <i data-lucide="building-2" class="w-4 h-4 text-slate-400"></i>
                    {{ $client->company_name }}
                </div>
                @endif
                <div class="flex items-center gap-2 text-slate-600">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
                    {{ $client->email }}
                </div>
                @if($client->phone)
                <div class="flex items-center gap-2 text-slate-600">
                    <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                    {{ $client->phone }}
                </div>
                @endif
                @if($client->address)
                <div class="flex items-start gap-2 text-slate-600">
                    <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 mt-0.5"></i>
                    <span>{{ $client->address }}, {{ $client->city }}, {{ $client->country }}</span>
                </div>
                @endif
                @if($client->tax_number)
                <div class="flex items-center gap-2 text-slate-600">
                    <i data-lucide="hash" class="w-4 h-4 text-slate-400"></i>
                    NTN: {{ $client->tax_number }}
                </div>
                @endif
            </div>

            @if($client->notes)
            <div class="bg-slate-50 rounded-lg p-3 text-xs text-slate-600">
                {{ $client->notes }}
            </div>
            @endif
        </div>

        <!-- Stats -->
        <div class="xl:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 content-start">
            <x-stat-card
                title="Total Billed"
                value="PKR {{ number_format($totalBilled, 0) }}"
                icon="file-text"
                color="blue"
            />
            <x-stat-card
                title="Total Paid"
                value="PKR {{ number_format($totalPaid, 0) }}"
                icon="check-circle-2"
                color="green"
            />
            <x-stat-card
                title="Outstanding"
                value="PKR {{ number_format(max(0, $totalBilled - $totalPaid), 0) }}"
                icon="clock"
                color="orange"
            />
            <x-stat-card
                title="Total Invoices"
                value="{{ $invoices->total() }}"
                icon="layers"
                color="purple"
            />
        </div>
    </div>

    <!-- Invoices -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Invoices</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Issue Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3.5 font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $invoice->issue_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $invoice->due_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-medium text-slate-900">PKR {{ number_format($invoice->total, 0) }}</td>
                        <td class="px-5 py-3.5"><x-badge :status="$invoice->status" /></td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('invoices.show', $invoice) }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">No invoices for this client yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
@endsection
