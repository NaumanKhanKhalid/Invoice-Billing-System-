@extends('layouts.app')
@section('title', 'Invoices')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Invoices</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $invoices->total() }} total invoices</p>
        </div>
        <a href="{{ route('invoices.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            New Invoice
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap gap-3">
            <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                <option value="">All Status</option>
                @foreach(['draft','sent','paid','overdue','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>

            <select name="client_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                <option value="">All Clients</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <input type="date" name="from_date" value="{{ request('from_date') }}"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">

            <input type="date" name="to_date" value="{{ request('to_date') }}"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['status','client_id','from_date','to_date']))
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Issue Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Due Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Amount Due</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $invoice->client->name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 hidden md:table-cell">{{ $invoice->issue_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 text-slate-500 hidden md:table-cell {{ $invoice->status === 'overdue' ? 'text-red-500 font-medium' : '' }}">
                            {{ $invoice->due_date->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-slate-900">PKR {{ number_format($invoice->total, 0) }}</td>
                        <td class="px-5 py-3.5 font-medium hidden lg:table-cell {{ $invoice->amount_due > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            PKR {{ number_format($invoice->amount_due, 0) }}
                        </td>
                        <td class="px-5 py-3.5"><x-badge :status="$invoice->status" /></td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('invoices.show', $invoice) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span class="hidden sm:inline">View</span>
                                </a>
                                <a href="{{ route('invoices.pdf', $invoice) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-slate-400">
                            <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-3 text-slate-200"></i>
                            <p class="font-medium">No invoices found</p>
                        </td>
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
