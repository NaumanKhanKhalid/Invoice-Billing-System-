@extends('layouts.app')
@section('title','Receipt #'.$posSale->sale_number)
@section('content')
<div class="max-w-sm mx-auto">
  {{-- Print button --}}
  <div class="flex gap-3 mb-6 print:hidden">
    <button onclick="window.print()" class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
      <i data-lucide="printer" class="w-4 h-4"></i>Print Receipt
    </button>
    <a href="{{ route('pos.create') }}" class="flex-1 inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
      <i data-lucide="plus" class="w-4 h-4"></i>New Sale
    </a>
  </div>

  {{-- Receipt --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 font-mono text-sm">
    @php $shopName = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? 'Shop'); @endphp
    <div class="text-center mb-4">
      <h1 class="text-lg font-bold">{{ $shopName }}</h1>
      @if($phone = \App\Models\Setting::getValue('company_phone'))
      <p class="text-xs text-slate-500">{{ $phone }}</p>
      @endif
      @if($address = \App\Models\Setting::getValue('company_address'))
      <p class="text-xs text-slate-500">{{ $address }}</p>
      @endif
      <div class="border-t border-dashed border-slate-300 my-3"></div>
      <p class="text-xs text-slate-500">{{ $posSale->sale_number }}</p>
      <p class="text-xs text-slate-500">{{ $posSale->date->format('d M Y') }} · {{ $posSale->created_at->format('H:i') }}</p>
      @if($posSale->customer_name)
      <p class="text-xs text-slate-500 mt-1">Customer: {{ $posSale->customer_name }}</p>
      @endif
    </div>

    <div class="border-t border-dashed border-slate-300 my-3"></div>

    <table class="w-full text-xs">
      <thead>
        <tr class="text-slate-500">
          <th class="text-left pb-1">Item</th>
          <th class="text-center pb-1">Qty</th>
          <th class="text-right pb-1">Price</th>
          <th class="text-right pb-1">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($posSale->items as $item)
        <tr>
          <td class="py-0.5 pr-2">{{ $item->product_name }}</td>
          <td class="text-center py-0.5">{{ $item->qty }}</td>
          <td class="text-right py-0.5">{{ number_format($item->unit_price) }}</td>
          <td class="text-right py-0.5 font-semibold">{{ number_format($item->total) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="border-t border-dashed border-slate-300 my-3 space-y-1 text-xs">
      @if($posSale->discount > 0)
      <div class="flex justify-between">
        <span>Subtotal</span><span>{{ number_format($posSale->subtotal) }}</span>
      </div>
      <div class="flex justify-between text-red-600">
        <span>Discount</span><span>- {{ number_format($posSale->discount) }}</span>
      </div>
      @endif
      <div class="flex justify-between font-bold text-base">
        <span>TOTAL</span><span>PKR {{ number_format($posSale->total) }}</span>
      </div>
      <div class="flex justify-between text-slate-500">
        <span>Paid ({{ ucfirst($posSale->payment_method) }})</span>
        <span>{{ number_format($posSale->amount_paid) }}</span>
      </div>
      @if($posSale->change_due > 0)
      <div class="flex justify-between font-semibold text-green-600">
        <span>Change</span><span>{{ number_format($posSale->change_due) }}</span>
      </div>
      @endif
    </div>

    <div class="border-t border-dashed border-slate-300 mt-3 pt-3 text-center text-xs text-slate-400">
      <p>Shukriya! Dobara tashreef layen.</p>
      <p class="mt-1">Powered by ShopSaas</p>
    </div>
  </div>
</div>
@endsection
