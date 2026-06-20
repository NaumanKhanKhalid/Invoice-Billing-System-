@extends('layouts.app')
@section('title',$supplier->name)
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-slate-600">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $supplier->name }}</h1>
        <p class="text-sm text-slate-500">{{ $supplier->phone }}@if($supplier->address) · {{ $supplier->address }}@endif</p>
      </div>
    </div>
    <a href="{{ route('suppliers.edit',$supplier) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="pencil" class="w-4 h-4"></i>Edit
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Purchased</p>
      <p class="text-2xl font-bold text-slate-900 mt-1">PKR {{ number_format($totalPurchased,0) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Total Paid</p>
      <p class="text-2xl font-bold text-green-600 mt-1">PKR {{ number_format($totalPaid,0) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Outstanding</p>
      <p class="text-2xl font-bold text-red-600 mt-1">PKR {{ number_format($outstanding,0) }}</p>
    </div>
  </div>

  {{-- Purchase History --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Purchase History</h2>
      <span class="text-sm text-slate-500">Credit: {{ $supplier->credit_days }} days</span>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Invoice #</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Live Kg</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Dressed Kg</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($orders as $order)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $order->invoice_number }}</td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $order->chickenType->name ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right">{{ number_format($order->live_weight_kg,1) }}</td>
          <td class="px-4 py-3 text-sm text-right">{{ number_format($order->dressed_weight_kg,1) }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">PKR {{ number_format($order->total_amount,0) }}</td>
          <td class="px-4 py-3">
            <span class="badge {{ $order->payment_status==='paid'?'badge-green':($order->payment_status==='partial'?'badge-yellow':'badge-red') }}">
              {{ ucfirst($order->payment_status) }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">No purchase orders yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($orders->hasPages())
      <div class="px-4 py-3 border-t border-slate-100">{{ $orders->links() }}</div>
    @endif
  </div>
</div>
@endsection
