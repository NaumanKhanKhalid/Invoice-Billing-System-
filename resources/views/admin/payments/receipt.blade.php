@extends('layouts.app')
@section('title','Receipt — ' . $tenant->shop_name)
@section('content')
@php
  $invNo   = 'SS-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);
  $platform = config('app.name', 'ShopSaas');
  $waPhone = wa_number($tenant->owner_phone ?? '');
  $waText  = urlencode("*{$platform}* Payment Receipt {$invNo}\n"
      . "Shop: {$tenant->shop_name}\n"
      . "Plan: " . ucfirst($payment->plan) . " × {$payment->months} month(s)\n"
      . "Period: " . $payment->period_from->format('d M Y') . " to " . $payment->period_to->format('d M Y') . "\n"
      . "Amount: PKR " . number_format($payment->amount) . "\n"
      . "Paid on: " . $payment->paid_at->format('d M Y') . "\nShukriya!");
@endphp

<div class="max-w-3xl mx-auto space-y-5">
  {{-- Actions (hidden on print) --}}
  <div class="flex items-center justify-between print:hidden">
    <a href="{{ route('admin.tenants.payments', $tenant) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800">
      <i data-lucide="arrow-left" class="w-4 h-4"></i> Payment History
    </a>
    <div class="flex items-center gap-2">
      @if($waPhone)
      <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-emerald-400 hover:text-emerald-600 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition">
        <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp
      </a>
      @endif
      <button onclick="window.print()" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
        <i data-lucide="printer" class="w-4 h-4"></i> Print
      </button>
    </div>
  </div>

  {{-- Invoice document --}}
  <div id="invoice" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    {{-- Header --}}
    <div class="px-8 py-6 flex items-start justify-between gap-4" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%)">
      <div>
        <div class="flex items-center gap-2 text-white">
          <span class="w-9 h-9 rounded-lg bg-green-500 flex items-center justify-center font-extrabold">S</span>
          <span class="text-xl font-extrabold tracking-tight">{{ $platform }}</span>
        </div>
        <p class="text-slate-300 text-xs mt-2">Multi-shop billing &amp; POS platform</p>
      </div>
      <div class="text-right text-white">
        <p class="text-lg font-bold">PAYMENT RECEIPT</p>
        <p class="text-slate-300 text-sm font-mono mt-1">{{ $invNo }}</p>
        <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-full bg-green-500/20 text-green-300 text-xs font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> PAID
        </span>
      </div>
    </div>

    {{-- Bill to + meta --}}
    <div class="px-8 py-6 grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-slate-100">
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Billed To</p>
        <p class="text-base font-bold text-slate-900">{{ $tenant->shop_name }}</p>
        <p class="text-sm text-slate-600 mt-0.5">{{ $tenant->owner_name }}</p>
        @if($tenant->owner_email)<p class="text-sm text-slate-500">{{ $tenant->owner_email }}</p>@endif
        @if($tenant->owner_phone)<p class="text-sm text-slate-500">{{ $tenant->owner_phone }}</p>@endif
        <p class="text-xs text-slate-400 mt-1 font-mono">{{ $tenant->id }}</p>
      </div>
      <div class="sm:text-right space-y-1">
        <div class="flex sm:justify-end gap-2 text-sm"><span class="text-slate-400">Receipt Date:</span><span class="font-semibold text-slate-700">{{ $payment->paid_at->format('d M Y') }}</span></div>
        <div class="flex sm:justify-end gap-2 text-sm"><span class="text-slate-400">Method:</span><span class="font-semibold text-slate-700 capitalize">{{ $payment->method }}</span></div>
        @if($payment->reference)<div class="flex sm:justify-end gap-2 text-sm"><span class="text-slate-400">Reference:</span><span class="font-semibold text-slate-700">{{ $payment->reference }}</span></div>@endif
        <div class="flex sm:justify-end gap-2 text-sm"><span class="text-slate-400">Service Period:</span><span class="font-semibold text-slate-700">{{ $payment->period_from->format('d M Y') }} – {{ $payment->period_to->format('d M Y') }}</span></div>
      </div>
    </div>

    {{-- Line items --}}
    <div class="px-8 py-6">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-slate-400 text-[11px] uppercase tracking-wider border-b border-slate-100">
            <th class="text-left pb-2 font-semibold">Description</th>
            <th class="text-center pb-2 font-semibold">Months</th>
            <th class="text-right pb-2 font-semibold">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-b border-slate-50">
            <td class="py-3">
              <p class="font-semibold text-slate-800">{{ ucfirst($payment->plan) }} Plan — Subscription</p>
              <p class="text-xs text-slate-400">{{ $tenant->shop_name }} ({{ ucfirst($tenant->shop_type) }})</p>
            </td>
            <td class="py-3 text-center text-slate-600">{{ $payment->months }}</td>
            <td class="py-3 text-right font-semibold text-slate-900 tabular-nums">PKR {{ number_format($payment->amount) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-end mt-4">
        <div class="w-full sm:w-64 space-y-2">
          <div class="flex justify-between text-sm text-slate-500"><span>Subtotal</span><span class="tabular-nums">PKR {{ number_format($payment->amount) }}</span></div>
          <div class="flex justify-between items-center pt-2 border-t border-slate-200">
            <span class="font-bold text-slate-900">Total Paid</span>
            <span class="text-xl font-extrabold text-green-600 tabular-nums">PKR {{ number_format($payment->amount) }}</span>
          </div>
        </div>
      </div>
    </div>

    @if($payment->notes)
    <div class="px-8 pb-4">
      <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Notes</p>
      <p class="text-sm text-slate-600">{{ $payment->notes }}</p>
    </div>
    @endif

    <div class="px-8 py-5 border-t border-slate-100 text-center">
      <p class="text-sm text-slate-500">Shukriya! {{ $platform }} use karne ka.</p>
      <p class="text-xs text-slate-400 mt-0.5">Ye ek computer-generated receipt hai — signature ki zaroorat nahi.</p>
    </div>
  </div>
</div>

<style>
@media print {
  aside, nav, header, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: #fff !important; padding: 0 !important; margin: 0 !important; }
  #invoice { border: none !important; box-shadow: none !important; border-radius: 0 !important; max-width: 100% !important; }
  @page { margin: 12mm; }
}
</style>
@endsection
