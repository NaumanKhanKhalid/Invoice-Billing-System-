@extends('layouts.app')
@section('title','Fee Receipt')
@section('content')
<div class="max-w-md mx-auto">

  <div class="flex items-center justify-between mb-4 print:hidden">
    <a href="{{ route('coaching.fees.index') }}"
       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>Back
    </a>
    <div class="flex items-center gap-2">
      @if($fee->student->phone && feature_enabled('whatsapp_share'))
      @php
        $waText = urlencode("*Fee Receipt — {$fee->receipt_number}*\n"
          . ($fee->student->name) . "\n"
          . \Carbon\Carbon::parse($fee->month)->format('F Y') . " ki fees\n"
          . "Paid: PKR " . number_format($fee->amount_paid)
          . ($fee->balance_due > 0 ? "\nBalance: PKR " . number_format($fee->balance_due) : "")
          . "\nShukriya! 🙏");
        $waPhone = preg_replace('/[^0-9]/', '', $fee->student->phone);
      @endphp
      <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank"
         class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="message-circle" class="w-4 h-4"></i>WhatsApp
      </a>
      @endif
      @if(feature_enabled('receipt_print'))
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="printer" class="w-4 h-4"></i>Print
      </button>
      @endif
    </div>
  </div>

  <div id="receipt" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0">
    {{-- Header (inline styles so gradient can't be purged from the build) --}}
    <div style="background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;padding:1.5rem;text-align:center;">
      @if($logoPath = \App\Models\Setting::getValue('logo_path'))
      <img src="{{ tenant_asset($logoPath) }}" alt="Logo" style="max-height:3.5rem;margin:0 auto 0.5rem;background:rgba(255,255,255,0.9);border-radius:0.5rem;padding:0.25rem;">
      @endif
      <h1 style="font-size:1.125rem;font-weight:700;letter-spacing:0.02em;">{{ \App\Models\Setting::getValue('company_name', tenant()->shop_name ?? config('app.name')) }}</h1>
      @php $phone = \App\Models\Setting::getValue('company_phone'); $ntn = \App\Models\Setting::getValue('ntn_number'); @endphp
      @if($phone)<p style="color:#dcfce7;font-size:0.75rem;margin-top:0.125rem;">{{ $phone }}</p>@endif
      @if($ntn)<p style="color:#dcfce7;font-size:0.75rem;">NTN: {{ $ntn }}</p>@endif
      <div style="display:inline-block;margin-top:0.75rem;background:rgba(255,255,255,0.18);border-radius:9999px;padding:0.25rem 1rem;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">Fee Receipt</div>
    </div>

    <div class="px-6 py-5 space-y-5">
      {{-- Receipt Meta --}}
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Receipt No</p>
          <p class="font-bold text-slate-900 text-lg">{{ $fee->receipt_number }}</p>
        </div>
        <div class="text-right">
          <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Date</p>
          <p class="font-medium text-slate-700 text-sm">{{ \Carbon\Carbon::parse($fee->payment_date)->format('d M Y') }}</p>
        </div>
      </div>

      {{-- Student Info --}}
      <div class="bg-slate-50 rounded-xl p-4 space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Student</span>
          <span class="font-semibold text-slate-900">{{ $fee->student->name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Course · Batch</span>
          <span class="text-slate-700 text-right">{{ $fee->student->batch->course->name }}<br><span class="text-xs text-slate-400">{{ $fee->student->batch->name }}</span></span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Fee Month</span>
          <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($fee->month)->format('F Y') }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Payment Method</span>
          <span class="text-slate-700">{{ ucfirst($fee->payment_method ?? 'cash') }}</span>
        </div>
      </div>

      {{-- Amounts --}}
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Fee Due</span>
          <span class="text-slate-700">PKR {{ number_format($fee->amount_due) }}</span>
        </div>
        @if($fee->discount_amount > 0)
        <div class="flex justify-between text-green-600">
          <span>Discount (this month)</span>
          <span>− PKR {{ number_format($fee->discount_amount) }}</span>
        </div>
        @endif
        @if($fee->balance_due > 0)
        <div class="flex justify-between text-red-600 font-medium">
          <span>Balance Remaining</span>
          <span>PKR {{ number_format($fee->balance_due) }}</span>
        </div>
        @endif
      </div>

      {{-- Amount Paid — highlighted --}}
      <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center justify-between">
        <span class="text-sm font-semibold text-green-800">Amount Paid</span>
        <span class="text-2xl font-bold text-green-700">PKR {{ number_format($fee->amount_paid) }}</span>
      </div>

      {{-- Status Badge --}}
      <div class="text-center">
        @if($fee->status === 'paid')
        <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 font-bold text-sm px-4 py-1.5 rounded-full">
          <i data-lucide="check-circle" class="w-4 h-4"></i>FULLY PAID
        </span>
        @elseif($fee->status === 'partial')
        <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 font-bold text-sm px-4 py-1.5 rounded-full">
          <i data-lucide="clock" class="w-4 h-4"></i>PARTIAL PAYMENT
        </span>
        @endif
      </div>

      @if(($taxPercent = \App\Models\Setting::getValue('sales_tax_percent')) && $taxPercent > 0)
      <p class="text-[11px] text-slate-400 text-center">Prices inclusive of {{ $taxPercent + 0 }}% sales tax</p>
      @endif

      @if($fee->notes)
      <div class="bg-slate-50 rounded-lg p-3 text-xs text-slate-600">
        <span class="font-medium">Note: </span>{{ $fee->notes }}
      </div>
      @endif
    </div>

    <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 text-center">
      <p class="text-[11px] text-slate-400">Shukriya! Ye receipt apne paas mehfooz rakhein.</p>
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
  #receipt > div:first-child { background: #fff !important; color: #000 !important; border-bottom: 1px dashed #000 !important; }
  #receipt > div:first-child * { color: #000 !important; }
  #receipt h1 { font-size: 13px !important; font-weight: bold !important; }
  #receipt [class*="px-6"], #receipt [class*="px-4"] { padding-left: 2mm !important; padding-right: 2mm !important; }
  #receipt [class*="py-"] { padding-top: 1mm !important; padding-bottom: 1mm !important; }
  #receipt [class*="border"] { border-color: #000 !important; }
  #receipt .rounded-full { border: 1px solid #000 !important; }
  #receipt img { max-height: 15mm !important; width: auto !important; margin: 0 auto 1mm !important; }
  #receipt .text-2xl { font-size: 14px !important; font-weight: bold !important; }
}
</style>
@endsection
