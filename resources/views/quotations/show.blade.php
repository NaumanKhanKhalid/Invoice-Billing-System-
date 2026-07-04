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
<div class="max-w-3xl mx-auto space-y-5">

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
        $waPhone = preg_replace('/[^0-9]/', '', $quotation->customer_phone ?? '');
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

  @if(session('success'))
  <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>{{ session('success') }}
  </div>
  @endif

  {{-- Quotation Card --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0">

    {{-- Header banner --}}
    <div class="bg-green-600 px-6 py-5 text-white">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm font-medium opacity-80">QUOTATION / ESTIMATE</p>
          <p class="text-2xl font-bold mt-0.5">{{ $quotation->quote_number }}</p>
          @if($ntn = \App\Models\Setting::getValue('ntn_number'))
          <p class="text-xs opacity-70 mt-0.5">NTN: {{ $ntn }}</p>
          @endif
        </div>
        <div class="text-right">
          <p class="text-sm opacity-80">Date</p>
          <p class="text-lg font-semibold">{{ $quotation->date->format('d M Y') }}</p>
          @if($quotation->valid_until)
          <p class="text-xs opacity-70 mt-0.5">Valid until {{ $quotation->valid_until->format('d M Y') }}</p>
          @endif
        </div>
      </div>
    </div>

    {{-- Customer info --}}
    @if($quotation->customer_name || $quotation->customer_phone)
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100">
      <p class="text-xs text-slate-400 uppercase tracking-wider font-medium mb-1">Customer</p>
      @if($quotation->customer_name)<p class="font-semibold text-slate-800">{{ $quotation->customer_name }}</p>@endif
      @if($quotation->customer_phone)<p class="text-sm text-slate-500">{{ $quotation->customer_phone }}</p>@endif
      @if($quotation->customer_email)<p class="text-sm text-slate-500">{{ $quotation->customer_email }}</p>@endif
    </div>
    @endif

    {{-- Items --}}
    <table class="w-full">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Item / Description</th>
          <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Qty</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Unit Price</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($quotation->items as $i => $item)
        <tr>
          <td class="px-5 py-3 text-sm text-slate-400">{{ $i + 1 }}</td>
          <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $item->product_name }}</td>
          <td class="px-5 py-3 text-sm text-center text-slate-600">{{ $item->qty }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
          <td class="px-5 py-3 text-sm text-right text-slate-600">PKR {{ number_format($item->unit_price) }}</td>
          <td class="px-5 py-3 text-sm text-right font-semibold text-slate-900">PKR {{ number_format($item->total) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Totals --}}
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 space-y-1.5">
      <div class="flex justify-between text-sm text-slate-600">
        <span>Subtotal</span>
        <span>PKR {{ number_format($quotation->subtotal) }}</span>
      </div>
      @if($quotation->discount > 0)
      <div class="flex justify-between text-sm text-red-500">
        <span>Discount</span>
        <span>- PKR {{ number_format($quotation->discount) }}</span>
      </div>
      @endif
      <div class="flex justify-between text-base font-bold text-slate-900 pt-1.5 border-t border-slate-300">
        <span>TOTAL</span>
        <span class="text-green-700">PKR {{ number_format($quotation->total) }}</span>
      </div>
      @if(($taxPercent = \App\Models\Setting::getValue('sales_tax_percent')) && $taxPercent > 0)
      <p class="text-xs text-slate-400 text-center pt-1">Prices inclusive of {{ $taxPercent + 0 }}% sales tax</p>
      @endif
    </div>

    @if($quotation->notes)
    <div class="px-6 py-3 border-t border-slate-100">
      <p class="text-xs text-slate-400 uppercase tracking-wider font-medium mb-1">Notes</p>
      <p class="text-sm text-slate-600">{{ $quotation->notes }}</p>
    </div>
    @endif
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
