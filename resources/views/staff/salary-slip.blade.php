@extends('layouts.app')
@section('title','Salary Slip')
@section('content')
<div class="max-w-md mx-auto">

  <div class="flex items-center justify-between mb-4 print:hidden">
    <a href="{{ route('staff.show', $staff) }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    @if(feature_enabled('receipt_print'))
    <button onclick="window.print()" class="flex items-center gap-2 bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
      <i data-lucide="printer" class="w-4 h-4"></i>Print
    </button>
    @endif
  </div>

  <div id="slip" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border-0">
    {{-- Header --}}
    <div class="bg-slate-800 text-white px-6 py-5 text-center">
      <h1 class="text-xl font-bold">Salary Slip</h1>
      <p class="text-slate-300 text-sm mt-1">{{ \App\Models\Setting::getValue('company_name') ?? config('app.name') }}</p>
    </div>

    <div class="px-6 py-5 space-y-5">
      {{-- Staff Info --}}
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Staff Name</span>
          <span class="font-semibold text-slate-900">{{ $staff->name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Role</span>
          <span class="text-slate-700">{{ ucfirst($staff->role) }}</span>
        </div>
        @if($staff->phone)
        <div class="flex justify-between">
          <span class="text-slate-500">Phone</span>
          <span class="text-slate-700">{{ $staff->phone }}</span>
        </div>
        @endif
        <div class="flex justify-between">
          <span class="text-slate-500">Salary Month</span>
          <span class="font-medium text-slate-900">{{ \Carbon\Carbon::createFromDate($payment->year, $payment->month, 1)->format('F Y') }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">Payment Date</span>
          <span class="text-slate-700">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span>
        </div>
      </div>

      <hr class="border-slate-100">

      {{-- Amount --}}
      <div class="flex justify-between font-bold text-base">
        <span class="text-slate-900">Amount Paid</span>
        <span class="text-green-700">PKR {{ number_format($payment->amount) }}</span>
      </div>

      @if($payment->note)
      <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600">
        <span class="font-medium">Note: </span>{{ $payment->note }}
      </div>
      @endif

      {{-- Signatures --}}
      <div class="grid grid-cols-2 gap-8 pt-10 text-sm">
        <div class="text-center">
          <div class="border-t border-slate-300 pt-2 text-slate-500">Employer</div>
        </div>
        <div class="text-center">
          <div class="border-t border-slate-300 pt-2 text-slate-500">Employee</div>
        </div>
      </div>
    </div>

    <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 text-center">
      <p class="text-xs text-slate-400">This is a record of salary payment. Keep this slip for your records.</p>
    </div>
  </div>

</div>

<style>
@media print {
  @page { size: A5 portrait; margin: 10mm; }
  aside, nav, header, footer, .print\:hidden, .no-print { display: none !important; }
  body, #main-content { background: white !important; padding: 0 !important; margin: 0 !important; }
  body * { visibility: hidden; }
  #slip, #slip * { visibility: visible; }
  #slip {
    position: absolute; top: 0; left: 0;
    width: 100% !important;
    margin: 0 !important;
    color: #000 !important; background: #fff !important;
    border: 1px solid #000 !important; border-radius: 0 !important; box-shadow: none !important;
    overflow: visible !important;
  }
  #slip * {
    color: #000 !important; background: transparent !important;
    box-shadow: none !important; border-radius: 0 !important;
  }
  #slip [class*="border"] { border-color: #000 !important; }
}
</style>
@endsection
