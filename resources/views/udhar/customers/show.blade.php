@extends('layouts.app')
@section('title', $udharCustomer->name . ' — Udhar Customer')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('udhar-customers.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 transition-colors shadow-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
      </a>
      <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $udharCustomer->name }}</h1>
        @if($udharCustomer->notes)
        <p class="text-sm text-slate-500 mt-0.5">{{ $udharCustomer->notes }}</p>
        @endif
      </div>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('udhar.create', ['customer_id' => $udharCustomer->id]) }}"
         class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>New Udhar
      </a>
      <a href="{{ route('udhar-customers.edit', $udharCustomer) }}"
         class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="pencil" class="w-4 h-4"></i>Edit
      </a>
      <form method="POST" action="{{ route('udhar-customers.destroy', $udharCustomer) }}"
            data-confirm-title="Delete Customer?" data-confirm-message="This will permanently delete {{ $udharCustomer->name }}. Only customers with no outstanding balance can be deleted." data-confirm-text="Yes, Delete" data-confirm-danger="true">
        @csrf @method('DELETE')
        <button type="submit" class="inline-flex items-center gap-2 bg-white border border-red-200 hover:bg-red-50 text-red-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <i data-lucide="trash-2" class="w-4 h-4"></i>Delete
        </button>
      </form>
    </div>
  </div>

  {{-- Info + Stats --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
      <h2 class="font-semibold text-slate-900">Customer Info</h2>
      <div class="space-y-2 text-sm">
        @if($udharCustomer->phone)
        <div class="flex items-center gap-2 text-slate-600">
          <i data-lucide="phone" class="w-4 h-4 text-slate-400 shrink-0"></i>
          {{ $udharCustomer->phone }}
        </div>
        @endif
        @if($udharCustomer->whatsapp_number)
        <div class="flex items-center gap-2 text-slate-600">
          <i data-lucide="message-circle" class="w-4 h-4 text-green-500 shrink-0"></i>
          {{ $udharCustomer->whatsapp_number }}
        </div>
        @endif
        @if($udharCustomer->address)
        <div class="flex items-start gap-2 text-slate-600">
          <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
          {{ $udharCustomer->address }}
        </div>
        @endif
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-center">
      <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Given</p>
      <p class="text-2xl font-bold text-slate-800">{{ formatCurrency($udharCustomer->total_given) }}</p>
    </div>
    <div class="bg-{{ $udharCustomer->current_balance > 0 ? 'red' : 'green' }}-50 rounded-xl border border-{{ $udharCustomer->current_balance > 0 ? 'red' : 'green' }}-200 shadow-sm p-5 text-center">
      <p class="text-xs text-{{ $udharCustomer->current_balance > 0 ? 'red' : 'green' }}-600 uppercase tracking-wider mb-1 font-medium">Current Balance</p>
      <p class="text-2xl font-bold text-{{ $udharCustomer->current_balance > 0 ? 'red-600' : 'green-600' }}">{{ formatCurrency($udharCustomer->current_balance) }}</p>
      <p class="text-xs text-slate-500 mt-1">Received: {{ formatCurrency($udharCustomer->total_received) }}</p>
    </div>
  </div>

  {{-- Udhar Entries --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Udhar Entries</h2>
      <span class="text-xs text-slate-500">{{ $sales->total() }} records</span>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Sale Date</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Due Date</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($sales as $sale)
        @php $overdue = in_array($sale->status, ['unpaid','partial']) && $sale->due_date->isPast(); @endphp
        <tr class="hover:bg-slate-50 {{ $overdue ? 'bg-red-50' : '' }}">
          <td class="px-4 py-3 text-sm text-slate-700">{{ $sale->description ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->sale_date->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm {{ $overdue ? 'text-red-600 font-semibold' : 'text-slate-600' }}">{{ $sale->due_date->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($sale->amount) }}</td>
          <td class="px-4 py-3">
            @if($sale->status === 'paid')<span class="badge badge-green">Paid</span>
            @elseif($sale->status === 'partial')<span class="badge badge-yellow">Partial</span>
            @else<span class="badge badge-red">Unpaid</span>@endif
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('udhar.show', $sale) }}"
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
              <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
            </a>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">No udhar entries yet.</td></tr>
        @endforelse
      </tbody>
    </table>
    @if($sales->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $sales->links() }}</div>
    @endif
  </div>

</div>
@endsection
