@extends('layouts.app')
@section('title', 'Receipt — ' . $openTab->tab_number)
@section('content')

<div class="max-w-md mx-auto">
  <div class="flex items-center justify-between mb-4 no-print">
    <a href="{{ route('open-tabs.index') }}"
       class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>Back to Tabs
    </a>
    <div class="flex items-center gap-2">
      @php
        $waShop  = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? 'Shop');
        $waLines = ["*{$waShop}* — Bill {$openTab->tab_number}", ''];
        foreach ($openTab->items as $ti) {
            $waLines[] = "{$ti->product_name} — {$ti->qty} x " . number_format($ti->price) . " = " . number_format($ti->total);
        }
        if ($openTab->discount > 0) $waLines[] = 'Discount: PKR ' . number_format($openTab->discount);
        $waLines[] = '*Total: PKR ' . number_format($openTab->total) . '*';
        $waLines[] = 'Shukriya! 🙏';
        $waText  = urlencode(implode("\n", $waLines));
        $waPhone = wa_number($openTab->customer_phone ?? '');
      @endphp
      @if(feature_enabled('whatsapp_share'))
      <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank"
         class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="message-circle" class="w-4 h-4"></i>WhatsApp
      </a>
      @endif
      @if(feature_enabled('receipt_print'))
      <button onclick="window.print()"
              class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i>Print
      </button>
      @endif
    </div>
  </div>

  {{-- Receipt --}}
  <div id="receipt" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="bg-slate-900 text-white text-center px-6 py-6">
      @php $shopName = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? 'My Shop'); @endphp
      <h1 class="text-xl font-bold tracking-wide">{{ $shopName }}</h1>
      @if($ntn = \App\Models\Setting::getValue('ntn_number'))
      <p class="text-slate-400 text-xs mt-1">NTN: {{ $ntn }}</p>
      @endif
      @php $phone = \App\Models\Setting::getValue('phone'); $address = \App\Models\Setting::getValue('address'); @endphp
      @if($phone)<p class="text-slate-400 text-xs mt-1">{{ $phone }}</p>@endif
      @if($address)<p class="text-slate-400 text-xs">{{ $address }}</p>@endif
    </div>

    <div class="px-6 py-4 border-b border-dashed border-slate-200">
      <div class="grid grid-cols-2 gap-2 text-sm">
        <div>
          <p class="text-xs text-slate-400">Receipt #</p>
          <p class="font-bold text-slate-900">{{ $openTab->tab_number }}</p>
        </div>
        <div class="text-right">
          <p class="text-xs text-slate-400">Date</p>
          <p class="font-semibold text-slate-900">{{ $openTab->closed_at?->format('d M Y') }}</p>
          <p class="text-xs text-slate-500">{{ $openTab->closed_at?->format('h:i A') }}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400">Customer</p>
          <p class="font-semibold text-slate-900">{{ $openTab->customer_name }}</p>
          @if($openTab->customer_phone)<p class="text-xs text-slate-500">{{ $openTab->customer_phone }}</p>@endif
        </div>
        @if($openTab->notes)
        <div class="text-right">
          <p class="text-xs text-slate-400">Note</p>
          <p class="text-sm text-slate-600">{{ $openTab->notes }}</p>
        </div>
        @endif
      </div>
    </div>

    {{-- Items --}}
    <div class="px-6 py-4">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100">
            <th class="pb-2 text-left text-xs font-semibold text-slate-400 uppercase">Item</th>
            <th class="pb-2 text-center text-xs font-semibold text-slate-400 uppercase">Qty</th>
            <th class="pb-2 text-right text-xs font-semibold text-slate-400 uppercase">Price</th>
            <th class="pb-2 text-right text-xs font-semibold text-slate-400 uppercase">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          @foreach($openTab->items as $i => $item)
          <tr>
            <td class="py-2 font-medium text-slate-800">
              {{ $item->product_name }}
              @if($item->unit && $item->unit !== 'pcs')
              <span class="text-xs text-slate-400">({{ $item->unit }})</span>
              @endif
            </td>
            <td class="py-2 text-center text-slate-600">{{ $item->qty }}</td>
            <td class="py-2 text-right text-slate-600">{{ number_format($item->price) }}</td>
            <td class="py-2 text-right font-semibold text-slate-900">{{ number_format($item->total) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Totals --}}
    <div class="px-6 pb-4 border-t border-dashed border-slate-200 pt-3 space-y-1.5">
      <div class="flex justify-between text-sm">
        <span class="text-slate-500">Subtotal</span>
        <span class="font-medium">PKR {{ number_format($openTab->subtotal) }}</span>
      </div>
      @if($openTab->discount > 0)
      <div class="flex justify-between text-sm">
        <span class="text-slate-500">Discount</span>
        <span class="font-medium text-red-500">−PKR {{ number_format($openTab->discount) }}</span>
      </div>
      @endif
      <div class="flex justify-between text-base font-bold border-t border-slate-200 pt-2 mt-2">
        <span>Total</span>
        <span class="text-lg">PKR {{ number_format($openTab->total) }}</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-slate-500">Paid ({{ ucfirst($openTab->payment_method) }})</span>
        <span class="font-medium text-green-600">PKR {{ number_format($openTab->amount_paid) }}</span>
      </div>
      @php $change = $openTab->amount_paid - $openTab->total; @endphp
      @if($change != 0)
      <div class="flex justify-between text-sm">
        <span class="text-slate-500">{{ $change > 0 ? 'Change' : 'Balance Due' }}</span>
        <span class="font-semibold {{ $change > 0 ? 'text-slate-700' : 'text-red-600' }}">
          PKR {{ number_format(abs($change)) }}
        </span>
      </div>
      @endif
      @if(($taxPercent = \App\Models\Setting::getValue('sales_tax_percent')) && $taxPercent > 0)
      <p class="text-xs text-slate-400 text-center pt-1">Prices inclusive of {{ $taxPercent + 0 }}% sales tax</p>
      @endif
    </div>

    {{-- Footer --}}
    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 text-center">
      <p class="text-xs text-slate-400">Thank you for your business!</p>
      <p class="text-xs text-slate-400 mt-0.5">{{ now()->format('d M Y, h:i A') }}</p>
    </div>
  </div>
</div>

<style>
@media print {
  @page { size: 80mm auto; margin: 2mm; }
  aside, nav, header, footer, .no-print, .print\:hidden { display: none !important; }
  body, #main-content { background: white !important; padding: 0 !important; margin: 0 !important; }
  body * { visibility: hidden; }
  #receipt, #receipt * { visibility: visible; }
  #receipt {
    position: absolute; top: 0; left: 0;
    width: 76mm !important; max-width: 76mm !important;
    margin: 0 !important; padding: 0 !important;
    font-family: 'Courier New', Courier, monospace !important;
    font-size: 11px !important; line-height: 1.3 !important;
    color: #000 !important; background: #fff !important;
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
    overflow: visible !important;
  }
  #receipt * {
    color: #000 !important; background: transparent !important;
    box-shadow: none !important; border-radius: 0 !important;
    font-size: 11px !important; line-height: 1.3 !important;
  }
  #receipt h1 { font-size: 13px !important; font-weight: bold !important; }
  #receipt [class*="px-6"] { padding-left: 2mm !important; padding-right: 2mm !important; }
  #receipt [class*="py-"] { padding-top: 1mm !important; padding-bottom: 1mm !important; }
  #receipt [class*="border"] { border-color: #000 !important; }
}
</style>
@endsection
