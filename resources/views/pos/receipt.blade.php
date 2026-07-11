@extends('layouts.app')
@section('title','Receipt #'.$posSale->sale_number)
@section('content')
<div class="max-w-sm mx-auto">
  {{-- Print button --}}
  <div class="flex gap-3 mb-6 print:hidden">
    @if(feature_enabled('receipt_print'))
    <button onclick="window.print()" class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
      <i data-lucide="printer" class="w-4 h-4"></i>Print Receipt
    </button>
    @endif
    @php
      $waShop  = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? 'Shop');
      $waLines = ["*{$waShop}* — Receipt {$posSale->sale_number}", $posSale->date->format('d M Y'), ''];
      foreach ($posSale->items as $ri) {
          $waLines[] = "{$ri->product_name} — {$ri->qty} x " . number_format($ri->unit_price) . " = " . number_format($ri->total);
      }
      if ($posSale->discount > 0) $waLines[] = 'Discount: PKR ' . number_format($posSale->discount);
      $waLines[] = '*Total: PKR ' . number_format($posSale->total) . '*';
      $waLines[] = 'Shukriya! 🙏';
      $waText  = urlencode(implode("\n", $waLines));
      $waPhone = wa_number($posSale->customer_phone ?? '');
    @endphp
    @if(feature_enabled('whatsapp_share'))
    <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank"
       class="flex-1 inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
      <i data-lucide="message-circle" class="w-4 h-4"></i>WhatsApp
    </a>
    @endif
    <a href="{{ route('pos.create') }}" class="flex-1 inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
      <i data-lucide="plus" class="w-4 h-4"></i>New Sale
    </a>
  </div>

  {{-- Receipt --}}
  <div id="receipt" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 font-mono text-sm">
    @php $shopName = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? 'Shop'); @endphp
    <div class="text-center mb-4">
      @if($logoPath = \App\Models\Setting::getValue('logo_path'))
      <img src="{{ tenant_asset($logoPath) }}" alt="{{ $shopName }}" class="max-h-16 mx-auto mb-2">
      @endif
      <h1 class="text-lg font-bold">{{ $shopName }}</h1>
      @if($ntn = \App\Models\Setting::getValue('ntn_number'))
      <p class="text-xs text-slate-500">NTN: {{ $ntn }}</p>
      @endif
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
      @if(($taxPercent = \App\Models\Setting::getValue('sales_tax_percent')) && $taxPercent > 0)
      <p class="text-slate-400 text-center">Prices inclusive of {{ $taxPercent + 0 }}% sales tax</p>
      @endif
    </div>

    <div class="border-t border-dashed border-slate-300 mt-3 pt-3 text-center text-xs text-slate-400">
      <p>Shukriya! Dobara tashreef layen.</p>
      @if(!\App\Models\Setting::getValue('hide_branding'))
      <p class="mt-1">Powered by ShopSaas</p>
      @endif
    </div>
  </div>
</div>

<style>
@media print {
  @page { size: 80mm auto; margin: 2mm; }
  aside, nav, header, footer, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: white !important; padding: 0 !important; margin: 0 !important; }
  body * { visibility: hidden; }
  #receipt, #receipt * { visibility: visible; }
  #receipt {
    position: absolute; top: 0; left: 0;
    width: 76mm !important; max-width: 76mm !important;
    margin: 0 !important; padding: 2mm !important;
    font-family: 'Courier New', Courier, monospace !important;
    font-size: 11px !important; line-height: 1.3 !important;
    color: #000 !important; background: #fff !important;
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
  }
  #receipt * {
    color: #000 !important; background: transparent !important;
    box-shadow: none !important; border-radius: 0 !important;
    font-size: 11px !important; line-height: 1.3 !important;
  }
  #receipt h1 { font-size: 13px !important; font-weight: bold !important; }
  #receipt .border-dashed { border-color: #000 !important; }
  #receipt img { max-height: 15mm !important; width: auto !important; margin: 0 auto 1mm !important; }
}
</style>
@endsection
