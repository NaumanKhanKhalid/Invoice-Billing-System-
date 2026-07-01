@extends('layouts.app')
@section('title','Fee Receipt')
@section('content')
<div class="max-w-md mx-auto">

  <div class="flex items-center justify-between mb-4 print:hidden">
    <a href="{{ route('coaching.fees.index') }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <button onclick="window.print()" class="flex items-center gap-2 bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
      <i data-lucide="printer" class="w-4 h-4"></i>Print
    </button>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0">
    {{-- Header --}}
    <div class="bg-slate-800 text-white px-6 py-5 text-center">
      <h1 class="text-xl font-bold">Fee Receipt</h1>
      <p class="text-slate-300 text-sm mt-1">{{ config('app.name') }}</p>
    </div>

    <div class="px-6 py-5 space-y-5">
      {{-- Receipt Meta --}}
      <div class="flex justify-between text-sm">
        <div>
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Receipt No</p>
          <p class="font-bold text-slate-900 text-lg">{{ $fee->receipt_number }}</p>
        </div>
        <div class="text-right">
          <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Date</p>
          <p class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($fee->payment_date)->format('d M Y') }}</p>
        </div>
      </div>

      <hr class="border-slate-100">

      {{-- Student Info --}}
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Student</span>
          <span class="font-semibold text-slate-900">{{ $fee->student->name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Batch</span>
          <span class="text-slate-700">{{ $fee->student->batch->name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Course</span>
          <span class="text-slate-700">{{ $fee->student->batch->course->name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Month</span>
          <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($fee->month)->format('F Y') }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Payment Method</span>
          <span class="text-slate-700">{{ ucfirst($fee->payment_method ?? 'cash') }}</span>
        </div>
      </div>

      <hr class="border-slate-100">

      {{-- Amounts --}}
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Fee Due</span>
          <span class="text-slate-700">PKR {{ number_format($fee->amount_due) }}</span>
        </div>
        @if($fee->discount_amount > 0)
        <div class="flex justify-between text-green-600">
          <span>Discount</span>
          <span>− PKR {{ number_format($fee->discount_amount) }}</span>
        </div>
        @endif
        <div class="flex justify-between font-bold text-base border-t border-slate-200 pt-2 mt-2">
          <span class="text-slate-900">Amount Paid</span>
          <span class="text-green-700">PKR {{ number_format($fee->amount_paid) }}</span>
        </div>
        @if($fee->balance_due > 0)
        <div class="flex justify-between text-red-600">
          <span>Balance Remaining</span>
          <span>PKR {{ number_format($fee->balance_due) }}</span>
        </div>
        @endif
      </div>

      {{-- Status Badge --}}
      <div class="text-center">
        @if($fee->status === 'paid')
        <span class="inline-block bg-green-100 text-green-800 font-bold text-sm px-4 py-1.5 rounded-full">✓ FULLY PAID</span>
        @elseif($fee->status === 'partial')
        <span class="inline-block bg-yellow-100 text-yellow-800 font-bold text-sm px-4 py-1.5 rounded-full">PARTIAL PAYMENT</span>
        @endif
      </div>

      @if($fee->notes)
      <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600">
        <span class="font-medium">Note: </span>{{ $fee->notes }}
      </div>
      @endif
    </div>

    <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 text-center">
      <p class="text-xs text-slate-400">Thank you for your payment. Keep this receipt for your records.</p>
    </div>
  </div>

</div>

<style>
@media print {
  nav, .print\:hidden { display: none !important; }
  body { background: white; }
}
</style>
@endsection
