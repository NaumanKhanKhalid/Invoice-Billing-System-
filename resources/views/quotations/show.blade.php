@extends('layouts.app')
@section('title', 'Quotation ' . $quotation->quote_number)
@section('content')
@php
$statusClass = [
  'draft'    => 'bg-slate-100 text-slate-600',
  'sent'     => 'bg-amber-100 text-amber-700',
  'accepted' => 'bg-green-100 text-green-700',
  'rejected' => 'bg-red-100 text-red-600',
  'expired'  => 'bg-slate-100 text-slate-400',
][$quotation->status] ?? 'bg-slate-100 text-slate-600';
@endphp
<div class="space-y-5">

  <div class="flex items-center gap-3 flex-wrap">
    <a href="{{ route('quotations.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
      <div class="flex items-center gap-2">
        <h1 class="text-xl font-bold text-slate-900">{{ $quotation->quote_number }}</h1>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
          {{ ucfirst($quotation->status) }}
        </span>
      </div>
      <p class="text-sm text-slate-500">{{ $quotation->date->format('d M Y') }}@if($quotation->valid_until) · Valid until {{ $quotation->valid_until->format('d M Y') }}@endif</p>
    </div>
    <div class="flex items-center gap-2">
      @php
        $waLines = ["*Quotation {$quotation->quote_number}*", \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? ''), ''];
        foreach ($quotation->items as $qi) {
            $waLines[] = "{$qi->product_name} — {$qi->qty} x " . number_format($qi->unit_price) . " = PKR " . number_format($qi->total);
        }
        if ($quotation->discount > 0) $waLines[] = 'Discount: PKR ' . number_format($quotation->discount);
        $waLines[] = '*Total: PKR ' . number_format($quotation->total) . '*';
        if ($quotation->valid_until) $waLines[] = 'Valid until: ' . $quotation->valid_until->format('d M Y');
        $waText  = urlencode(implode("\n", $waLines));
        $waPhone = wa_number($quotation->customer_phone ?? '');
      @endphp
      @if(feature_enabled('whatsapp_share'))
      <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank"
         class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp
      </a>
      @endif
      @if(feature_enabled('receipt_print'))
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i> Print
      </button>
      @endif
    </div>
  </div>

  {{-- Quotation document --}}
  @php
    $logo  = \App\Models\Setting::getValue('logo_path');
    $cname = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? 'Shop');
    $addr  = \App\Models\Setting::getValue('company_address');
    $cphone = \App\Models\Setting::getValue('company_phone');
    $ntn   = \App\Models\Setting::getValue('ntn_number');
  @endphp
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0">

    {{-- Letterhead --}}
    <div class="px-6 pt-6 pb-5 flex items-start justify-between gap-4 border-b border-slate-100">
      <div class="flex items-start gap-3 min-w-0">
        @if($logo)
        <img src="{{ tenant_asset($logo) }}" alt="Logo" class="w-12 h-12 rounded-xl object-contain bg-white border border-slate-100 p-1 shrink-0">
        @else
        <div class="w-12 h-12 rounded-xl bg-green-600 text-white flex items-center justify-center font-bold text-lg shrink-0">{{ strtoupper(substr($cname, 0, 1)) }}</div>
        @endif
        <div class="min-w-0">
          <p class="font-bold text-slate-900 text-lg leading-tight">{{ $cname }}</p>
          @if($addr)<p class="text-xs text-slate-500 mt-0.5 leading-snug">{{ $addr }}</p>@endif
          @if($cphone)<p class="text-xs text-slate-500">{{ $cphone }}</p>@endif
          @if($ntn)<p class="text-xs text-slate-400">NTN: {{ $ntn }}</p>@endif
        </div>
      </div>
      <div class="text-right shrink-0">
        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-green-600">Quotation</p>
        <p class="text-xl font-extrabold text-slate-900 mt-0.5 tabular-nums">{{ $quotation->quote_number }}</p>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold mt-1.5 {{ $statusClass }}">
          <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ ucfirst($quotation->status) }}
        </span>
      </div>
    </div>

    {{-- Bill-to + dates --}}
    <div class="px-6 py-4 grid grid-cols-2 gap-4 border-b border-slate-100 bg-slate-50/60">
      <div class="min-w-0">
        <p class="text-[11px] text-slate-400 uppercase tracking-wider font-bold mb-1">Bill To</p>
        @if($quotation->customer_name)
        <p class="font-semibold text-slate-800 text-sm truncate">{{ $quotation->customer_name }}</p>
        @else
        <p class="text-slate-400 text-sm italic">Walk-in customer</p>
        @endif
        @if($quotation->customer_phone)<p class="text-xs text-slate-500">{{ $quotation->customer_phone }}</p>@endif
        @if($quotation->customer_email)<p class="text-xs text-slate-500 truncate">{{ $quotation->customer_email }}</p>@endif
      </div>
      <div class="text-right text-sm space-y-0.5">
        <div class="flex justify-end gap-2"><span class="text-slate-400">Date</span><span class="font-medium text-slate-700 tabular-nums">{{ $quotation->date->format('d M Y') }}</span></div>
        @if($quotation->valid_until)
        <div class="flex justify-end gap-2"><span class="text-slate-400">Valid until</span><span class="font-medium text-slate-700 tabular-nums">{{ $quotation->valid_until->format('d M Y') }}</span></div>
        @endif
      </div>
    </div>

    {{-- Items --}}
    <div class="overflow-x-auto">
    <table class="w-full min-w-[480px]">
      <thead class="border-b border-slate-200">
        <tr>
          <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-8">#</th>
          <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Item / Description</th>
          <th class="px-5 py-2.5 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Qty</th>
          <th class="px-5 py-2.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Unit Price</th>
          <th class="px-5 py-2.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Amount</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        @foreach($quotation->items as $i => $item)
        <tr class="{{ $i % 2 ? 'bg-slate-50/40' : '' }}">
          <td class="px-5 py-3 text-sm text-slate-400 tabular-nums">{{ $i + 1 }}</td>
          <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $item->product_name }}</td>
          <td class="px-5 py-3 text-sm text-center text-slate-600 tabular-nums">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
          <td class="px-5 py-3 text-sm text-right text-slate-600 tabular-nums">PKR {{ number_format($item->unit_price) }}</td>
          <td class="px-5 py-3 text-sm text-right font-semibold text-slate-900 tabular-nums">PKR {{ number_format($item->total) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>

    {{-- Totals --}}
    <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
      <div class="w-full max-w-[240px] space-y-1.5">
        <div class="flex justify-between text-sm text-slate-500">
          <span>Subtotal</span>
          <span class="tabular-nums">PKR {{ number_format($quotation->subtotal) }}</span>
        </div>
        @if($quotation->discount > 0)
        <div class="flex justify-between text-sm text-red-500">
          <span>Discount</span>
          <span class="tabular-nums">- PKR {{ number_format($quotation->discount) }}</span>
        </div>
        @endif
        <div class="flex justify-between items-baseline pt-2 border-t border-slate-200">
          <span class="font-bold text-slate-800">Total</span>
          <span class="text-lg font-extrabold text-green-700 tabular-nums">PKR {{ number_format($quotation->total) }}</span>
        </div>
      </div>
    </div>

    @if(($taxPercent = \App\Models\Setting::getValue('sales_tax_percent')) && $taxPercent > 0)
    <p class="px-6 pb-2 text-xs text-slate-400 text-right">Prices inclusive of {{ $taxPercent + 0 }}% sales tax</p>
    @endif

    @if($quotation->notes)
    <div class="px-6 py-3 border-t border-slate-100">
      <p class="text-[11px] text-slate-400 uppercase tracking-wider font-bold mb-1">Notes</p>
      <p class="text-sm text-slate-600 whitespace-pre-line">{{ $quotation->notes }}</p>
    </div>
    @endif

    {{-- Footer --}}
    <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/60 text-center">
      <p class="text-[11px] text-slate-400">
        Ye ek quotation/estimate hai, invoice nahi.
        @if($quotation->valid_until) Rates {{ $quotation->valid_until->format('d M Y') }} tak valid hain. @endif
      </p>
    </div>
  </div>

  {{-- Status Update --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 print:hidden">
    <h3 class="font-semibold text-slate-800 mb-3">Update Status</h3>
    <form method="POST" action="{{ route('quotations.status', $quotation) }}" class="flex gap-2 flex-wrap">
      @csrf @method('PATCH')
      @foreach(['draft' => 'Draft', 'sent' => 'Sent to Customer', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'expired' => 'Expired'] as $val => $label)
      <button type="submit" name="status" value="{{ $val }}"
              class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors
                {{ $quotation->status === $val
                   ? 'bg-green-600 text-white border-green-600'
                   : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
        {{ $label }}
      </button>
      @endforeach
    </form>
  </div>

  {{-- Delete --}}
  <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" class="print:hidden"
        data-confirm-title="Delete Quotation?" data-confirm-message="This will permanently delete {{ $quotation->quote_number }}." data-confirm-danger="true">
    @csrf @method('DELETE')
    <button type="submit" class="w-full border border-red-200 text-red-600 hover:bg-red-50 py-2 rounded-lg text-sm font-medium">
      Delete Quotation
    </button>
  </form>
</div>
@endsection
