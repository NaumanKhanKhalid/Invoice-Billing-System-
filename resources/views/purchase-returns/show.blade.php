@extends('layouts.app')
@section('title', 'Purchase Return')
@section('content')
<div class="max-w-xl mx-auto space-y-5">

  <div class="flex items-center gap-3">
    <a href="{{ route('purchase-returns.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Purchase Return</h1>
      <p class="text-sm text-slate-500">{{ $purchaseReturn->return_number }}</p>
    </div>
    <div class="ml-auto">
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i> Print
      </button>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="bg-orange-600 px-6 py-5 text-white">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium opacity-80">Purchase Return</p>
          <p class="text-2xl font-bold mt-1">{{ $purchaseReturn->return_number }}</p>
        </div>
        <div class="text-right">
          <p class="text-sm opacity-80">Date</p>
          <p class="text-lg font-semibold">{{ \Carbon\Carbon::parse($purchaseReturn->date)->format('d M Y') }}</p>
        </div>
      </div>
    </div>

    {{-- Meta --}}
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 grid grid-cols-2 gap-3 text-sm">
      <div>
        <p class="text-slate-400 text-xs">Supplier</p>
        <p class="font-medium text-slate-800">{{ $purchaseReturn->supplier?->name ?? '—' }}</p>
      </div>
      <div>
        <p class="text-slate-400 text-xs">Original Purchase</p>
        @if($purchaseReturn->purchase)
        <a href="{{ route('product-purchases.show', $purchaseReturn->purchase) }}" class="font-medium text-green-600 hover:underline">
          {{ $purchaseReturn->purchase->invoice_number ?: '#'.$purchaseReturn->purchase->id }}
        </a>
        @else
        <p class="font-medium text-slate-400">—</p>
        @endif
      </div>
      <div>
        <p class="text-slate-400 text-xs">Adjustment Method</p>
        @php
        $labels = ['deduct_balance' => 'Supplier Balance Kam', 'cash_refund' => 'Cash Wapas', 'exchange' => 'Exchange'];
        @endphp
        <p class="font-medium text-slate-800">{{ $labels[$purchaseReturn->adjustment_method] ?? $purchaseReturn->adjustment_method }}</p>
      </div>
      <div>
        <p class="text-slate-400 text-xs">Reason</p>
        <p class="font-medium text-slate-800">{{ $purchaseReturn->reason ?: '—' }}</p>
      </div>
    </div>

    {{-- Items --}}
    <div class="divide-y divide-slate-100">
      @foreach($purchaseReturn->items as $item)
      <div class="flex items-center justify-between px-6 py-3">
        <div>
          <p class="text-sm font-medium text-slate-900">{{ $item->product_name }}</p>
          <p class="text-xs text-slate-400">{{ $item->qty }} × PKR {{ number_format($item->unit_price) }}</p>
        </div>
        <p class="text-sm font-semibold text-slate-900">PKR {{ number_format($item->total) }}</p>
      </div>
      @endforeach
    </div>

    {{-- Total --}}
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
      <div class="flex items-center justify-between font-bold text-base">
        <span class="text-orange-600">Total Return Amount</span>
        <span class="text-orange-600">PKR {{ number_format($purchaseReturn->total) }}</span>
      </div>
      @if($purchaseReturn->notes)
      <p class="text-xs text-slate-400 mt-2">Notes: {{ $purchaseReturn->notes }}</p>
      @endif
    </div>
  </div>
</div>
@endsection
