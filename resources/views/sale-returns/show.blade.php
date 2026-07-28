@extends('layouts.app')
@section('title', 'Return Receipt')
@section('content')
<div class="space-y-5">

  <div class="flex items-center gap-3">
    <a href="{{ route('sale-returns.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Return Receipt</h1>
      <p class="text-sm text-slate-500">{{ $saleReturn->return_number }}</p>
    </div>
    <div class="ml-auto">
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i> Print
      </button>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="bg-red-600 px-6 py-5 text-white">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium opacity-80">Return Receipt</p>
          <p class="text-2xl font-bold mt-1">{{ $saleReturn->return_number }}</p>
        </div>
        <div class="text-right">
          <p class="text-sm opacity-80">Date</p>
          <p class="text-lg font-semibold">{{ \Carbon\Carbon::parse($saleReturn->date)->format('d M Y') }}</p>
        </div>
      </div>
    </div>

    {{-- Original sale ref --}}
    @if($saleReturn->sale)
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-sm">
      <span class="text-slate-500">Original Sale:</span>
      <a href="{{ route('pos.show', $saleReturn->sale) }}" class="font-semibold text-green-600 hover:underline">{{ $saleReturn->sale->sale_number }}</a>
    </div>
    @endif

    {{-- Items --}}
    <div class="divide-y divide-slate-100">
      @foreach($saleReturn->items as $item)
      <div class="flex items-center justify-between px-6 py-3">
        <div>
          <p class="text-sm font-medium text-slate-900">{{ $item->product_name }}</p>
          <p class="text-xs text-slate-400">{{ $item->qty }} × PKR {{ number_format($item->unit_price) }}</p>
        </div>
        <p class="text-sm font-semibold text-slate-900">PKR {{ number_format($item->total) }}</p>
      </div>
      @endforeach
    </div>

    {{-- Totals --}}
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 space-y-2">
      <div class="flex items-center justify-between text-sm">
        <span class="text-slate-500">Subtotal</span>
        <span class="font-medium">PKR {{ number_format($saleReturn->subtotal) }}</span>
      </div>
      <div class="flex items-center justify-between font-bold text-base border-t border-slate-200 pt-2 mt-2">
        <span class="text-red-600">Refund Amount</span>
        <span class="text-red-600">PKR {{ number_format($saleReturn->total) }}</span>
      </div>
      <div class="flex items-center justify-between text-sm">
        <span class="text-slate-500">Refund Method</span>
        <span class="capitalize font-medium">{{ $saleReturn->refund_method }}</span>
      </div>
    </div>

    @if($saleReturn->reason || $saleReturn->notes)
    <div class="px-6 py-4 border-t border-slate-100 space-y-1">
      @if($saleReturn->reason)
      <p class="text-xs text-slate-500">Reason: <span class="text-slate-700">{{ $saleReturn->reason }}</span></p>
      @endif
      @if($saleReturn->notes)
      <p class="text-xs text-slate-500">Notes: <span class="text-slate-700">{{ $saleReturn->notes }}</span></p>
      @endif
    </div>
    @endif
  </div>
</div>
@endsection
