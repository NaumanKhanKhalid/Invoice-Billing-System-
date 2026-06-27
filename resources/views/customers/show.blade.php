@extends('layouts.app')
@section('title',$customer->name)
@section('content')
@php
$typeBadge = [
    'retail'     => 'badge-gray',
    'hotel'      => 'badge-blue',
    'restaurant' => 'badge-purple',
    'company'    => 'badge-green',
    'reseller'   => 'badge-yellow',
];
@endphp
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-slate-600">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-2xl font-bold text-slate-900">{{ $customer->name }}</h1>
          <span class="badge {{ $typeBadge[$customer->type] ?? 'badge-gray' }}">{{ ucfirst($customer->type) }}</span>
          @if(!$customer->is_active)<span class="badge badge-gray">Inactive</span>@endif
        </div>
        <p class="text-sm text-slate-500">{{ $customer->phone }}@if($customer->address) &middot; {{ $customer->address }}@endif</p>
      </div>
    </div>
    <a href="{{ route('ledger.customer', $customer) }}" class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="book-open" class="w-4 h-4"></i>Ledger
    </a>
    <a href="{{ route('customers.edit',$customer) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="pencil" class="w-4 h-4"></i>Edit
    </a>
  </div>

  {{-- Blacklist Warning --}}
  @if($customer->is_blacklisted)
  <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
    <div class="flex-1">
      <p class="font-semibold text-red-800">Customer is Blacklisted</p>
      @if($customer->blacklist_reason)
        <p class="text-sm text-red-700 mt-0.5">Reason: {{ $customer->blacklist_reason }}</p>
      @endif
    </div>
    <form method="POST" action="{{ route('customers.toggle-blacklist',$customer) }}">
      @csrf
      <button type="submit" class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg transition-colors">
        Remove from Blacklist
      </button>
    </form>
  </div>
  @else
  <div x-data="{ open: false }" class="flex justify-end">
    <button @click="open = !open" class="text-sm text-red-600 hover:text-red-800 border border-red-200 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors">
      <i data-lucide="ban" class="w-4 h-4 inline mr-1"></i>Blacklist Customer
    </button>
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full">
        <h3 class="font-semibold text-slate-900 mb-3">Blacklist Customer</h3>
        <form method="POST" action="{{ route('customers.toggle-blacklist',$customer) }}">
          @csrf
          <textarea name="reason" rows="3" placeholder="Reason for blacklisting (optional)..."
            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-red-300 outline-none mb-4"></textarea>
          <div class="flex gap-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
              Confirm Blacklist
            </button>
            <button type="button" @click="open = false" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

  {{-- Stats --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Billed</p>
      <p class="text-2xl font-bold text-slate-900 mt-1">{{ formatCurrency($totalBilled) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Paid</p>
      <p class="text-2xl font-bold text-green-600 mt-1">{{ formatCurrency($totalPaid) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Outstanding</p>
      <p class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency($outstanding) }}</p>
    </div>
  </div>

  {{-- Sales Orders --}}
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
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Qty (Kg)</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Paid</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($orders as $order)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $order->invoice_number ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right">{{ formatKg($order->dressed_weight_kg) }} kg</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($order->total_amount) }}</td>
          <td class="px-4 py-3 text-sm text-right text-green-600">{{ formatCurrency($order->amount_paid ?? 0) }}</td>
          <td class="px-4 py-3">
            <span class="badge {{ ($order->payment_status??'unpaid')==='paid'?'badge-green':(($order->payment_status??'unpaid')==='partial'?'badge-yellow':'badge-red') }}">
              {{ ucfirst($order->payment_status ?? 'unpaid') }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">No sales orders yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
