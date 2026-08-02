{{-- Reusable fee receipt — A5 document style (coaching), prints on normal paper --}}
@php
  $acName = \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name'));
  $acPhone = \App\Models\Setting::getValue('company_phone');
  $acAddr  = \App\Models\Setting::getValue('company_address');
  $acNtn   = \App\Models\Setting::getValue('ntn_number');
  $logoPath = \App\Models\Setting::getValue('logo_path');
@endphp
<div id="receipt" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0 mx-auto" style="max-width:34rem">
  {{-- Letterhead --}}
  <div class="px-7 pt-6 pb-5 border-b-2 border-slate-900">
    <div class="flex items-start justify-between gap-4">
      <div class="flex items-center gap-3 min-w-0">
        @if($logoPath)
        <img src="{{ tenant_asset($logoPath) }}" alt="Logo" style="max-height:3.25rem;width:auto;border-radius:0.5rem;">
        @else
        <div class="w-12 h-12 rounded-xl bg-green-600 text-white flex items-center justify-center text-xl font-extrabold shrink-0">{{ mb_substr($acName,0,1) }}</div>
        @endif
        <div class="min-w-0">
          <h1 class="text-lg font-extrabold text-slate-900 leading-tight truncate">{{ $acName }}</h1>
          @if($acAddr)<p class="text-xs text-slate-500 leading-tight">{{ $acAddr }}</p>@endif
          @if($acPhone)<p class="text-xs text-slate-500 leading-tight">{{ $acPhone }}@if($acNtn) · NTN: {{ $acNtn }}@endif</p>@endif
        </div>
      </div>
      <div class="text-right shrink-0">
        <p class="text-lg font-extrabold tracking-tight text-green-700 uppercase leading-none">Fee Receipt</p>
        <p class="text-xs text-slate-400 mt-1">No. <span class="font-bold text-slate-700 font-mono">{{ $fee->receipt_number }}</span></p>
        <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($fee->payment_date)->format('d M Y') }}</p>
      </div>
    </div>
  </div>

  <div class="px-6 py-5 space-y-4">
    {{-- Received from --}}
    <div class="grid grid-cols-2 gap-4 bg-slate-50 rounded-xl px-4 py-3">
      <div>
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Received From</p>
        <p class="font-bold text-slate-900 leading-tight">{{ $fee->student->name }}</p>
        @if($fee->student->phone)<p class="text-xs text-slate-500">{{ $fee->student->phone }}</p>@endif
      </div>
      <div class="text-right">
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Course &amp; Batch</p>
        <p class="font-semibold text-slate-800 text-sm leading-tight">{{ $fee->student->batch->course->name }}</p>
        <p class="text-xs text-slate-500">{{ $fee->student->batch->name }}</p>
      </div>
    </div>

    {{-- Fee detail (grouped box) --}}
    <div class="rounded-xl border border-slate-100 overflow-hidden">
      <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
          <tr>
            <td class="px-4 py-2 text-slate-500">Fee for the month</td>
            <td class="px-4 py-2 text-right font-semibold text-slate-800">{{ \Carbon\Carbon::parse($fee->month)->format('F Y') }}</td>
          </tr>
          <tr>
            <td class="px-4 py-2 text-slate-500">Fee Due</td>
            <td class="px-4 py-2 text-right text-slate-700 tabular-nums">PKR {{ number_format($fee->amount_due) }}</td>
          </tr>
          @if($fee->discount_amount > 0)
          <tr class="text-green-600">
            <td class="px-4 py-2">Discount</td>
            <td class="px-4 py-2 text-right tabular-nums">− PKR {{ number_format($fee->discount_amount) }}</td>
          </tr>
          @endif
          <tr>
            <td class="px-4 py-2 text-slate-500">Payment Method</td>
            <td class="px-4 py-2 text-right text-slate-700 capitalize">{{ $fee->payment_method ?? 'cash' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Amount paid highlight --}}
    <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-5 py-3">
      <div>
        <p class="text-sm font-semibold text-green-800">Amount Paid</p>
        @if($fee->status === 'paid')
          <span class="inline-flex items-center gap-1 mt-1 bg-green-600 text-white font-bold text-[11px] px-2 py-0.5 rounded-full uppercase tracking-wide">Fully Paid</span>
        @elseif($fee->status === 'partial')
          <span class="inline-flex items-center gap-1 mt-1 bg-amber-500 text-white font-bold text-[11px] px-2 py-0.5 rounded-full uppercase tracking-wide">Partial</span>
        @endif
      </div>
      <span class="text-3xl font-extrabold text-green-700 tabular-nums">PKR {{ number_format($fee->amount_paid) }}</span>
    </div>

    @if($fee->balance_due > 0)
    <div class="flex items-center justify-between px-1">
      <span class="text-sm font-semibold text-red-600">Balance Remaining</span>
      <span class="text-lg font-bold text-red-600 tabular-nums">PKR {{ number_format($fee->balance_due) }}</span>
    </div>
    @endif

    @if($fee->notes)
    <div class="bg-slate-50 rounded-lg p-3 text-xs text-slate-600"><span class="font-semibold">Note: </span>{{ $fee->notes }}</div>
    @endif

    {{-- Signature line --}}
    <div class="flex items-end justify-between pt-5">
      <p class="text-[11px] text-slate-400 max-w-[55%]">Shukriya! Ye receipt apne paas mehfooz rakhein.</p>
      <div class="text-center">
        <div class="w-32 border-t border-slate-300"></div>
        <p class="text-[11px] text-slate-500 mt-1">Received by / Signature</p>
      </div>
    </div>
  </div>
</div>

<style>
@media print {
  @page { size: A5 portrait; margin: 10mm; }
  aside, nav, header, footer, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: #fff !important; padding: 0 !important; margin: 0 !important; }
  body * { visibility: hidden; }
  #receipt, #receipt * { visibility: visible; }
  #receipt {
    position: absolute; top: 0; left: 0; right: 0; margin: 0 auto !important;
    width: 100% !important; max-width: 100% !important;
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
  }
}
</style>
