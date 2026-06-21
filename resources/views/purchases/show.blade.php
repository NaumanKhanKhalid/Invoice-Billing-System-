@extends('layouts.app')
@section('title', $purchase->invoice_number)
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('purchases.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $purchase->invoice_number }}</h1>
        <p class="text-sm text-slate-500">{{ $purchase->supplier->name }} · {{ \Carbon\Carbon::parse($purchase->date)->format('d M Y') }}</p>
      </div>
    </div>
    <div class="flex gap-2">
      @if($purchase->payment_status !== 'paid')<a href="{{ route('purchases.edit',$purchase) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a>@endif
    </div>
  </div>

  {{-- Status banner --}}
  @php $isOverdue = $purchase->payment_status !== 'paid' && $purchase->due_date && \Carbon\Carbon::parse($purchase->due_date)->isPast(); @endphp
  @if($isOverdue)
  <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm"><i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i><span>This payment is overdue since {{ \Carbon\Carbon::parse($purchase->due_date)->format('d M Y') }}.</span></div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Details --}}
    <div class="lg:col-span-2 space-y-5">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Purchase Details</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-500">Supplier</p><p class="font-medium text-slate-900 mt-0.5">{{ $purchase->supplier->name }}</p></div>
          <div><p class="text-slate-500">Date</p><p class="font-medium text-slate-900 mt-0.5">{{ \Carbon\Carbon::parse($purchase->date)->format('d M Y') }}</p></div>
          <div><p class="text-slate-500">Due Date</p><p class="font-medium {{ $isOverdue?'text-red-600':'text-slate-900' }} mt-0.5">{{ $purchase->due_date ? \Carbon\Carbon::parse($purchase->due_date)->format('d M Y') : '-' }}</p></div>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-4">
          <div class="bg-slate-50 rounded-lg p-3 text-center"><p class="text-xs text-slate-500">Live Weight</p><p class="text-lg font-bold text-slate-900 mt-1">{{ number_format($purchase->live_weight_kg,3) }} kg</p></div>
          <div class="bg-green-50 rounded-lg p-3 text-center border border-green-100"><p class="text-xs text-green-600">Rate / kg (Live)</p><p class="text-lg font-bold text-green-700 mt-1">PKR {{ number_format($purchase->rate_per_kg_live,2) }}</p></div>
        </div>
        @if($purchase->dead_on_arrival_kg > 0)
        <p class="mt-3 text-sm text-red-600"><i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>Dead on arrival: {{ $purchase->dead_on_arrival_kg }} kg</p>
        @endif
        @if($purchase->notes)<div class="mt-4 p-3 bg-slate-50 rounded-lg"><p class="text-xs text-slate-500 mb-1">Notes</p><p class="text-sm text-slate-700">{{ $purchase->notes }}</p></div>@endif
      </div>

      {{-- Payment History --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-900">Payment History</h2></div>
        <table class="w-full">
          <thead class="bg-slate-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Method</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Note</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($purchase->purchasePayments as $payment)
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
              <td class="px-4 py-3 text-sm"><span class="badge badge-blue">{{ ucfirst($payment->method) }}</span></td>
              <td class="px-4 py-3 text-sm text-right font-medium text-green-600">PKR {{ number_format($payment->amount,0) }}</td>
              <td class="px-4 py-3 text-sm text-slate-500">{{ $payment->note??'-' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400 text-sm">No payments yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Right: Summary + Record Payment --}}
    <div class="space-y-5">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
        <h2 class="font-semibold text-slate-900">Payment Summary</h2>
        <div class="flex justify-between text-sm"><span class="text-slate-500">Rate/kg (live)</span><span class="font-medium">PKR {{ number_format($purchase->rate_per_kg_live,2) }}</span></div>
        <div class="flex justify-between text-sm"><span class="text-slate-500">Total Amount</span><span class="font-bold text-slate-900">PKR {{ number_format($purchase->total_amount,0) }}</span></div>
        <div class="flex justify-between text-sm"><span class="text-slate-500">Amount Paid</span><span class="font-medium text-green-600">PKR {{ number_format($purchase->amount_paid,0) }}</span></div>
        <div class="border-t border-slate-100 pt-3 flex justify-between"><span class="font-semibold text-slate-700">Amount Due</span><span class="font-bold text-xl {{ $purchase->amount_due>0?'text-red-600':'text-green-600' }}">PKR {{ number_format($purchase->amount_due,0) }}</span></div>
        <div class="pt-1">
          @if($purchase->payment_status==='paid')<span class="badge badge-green w-full justify-center py-2">Fully Paid</span>
          @elseif($purchase->payment_status==='partial')<span class="badge badge-yellow w-full justify-center py-2">Partially Paid</span>
          @else<span class="badge badge-red w-full justify-center py-2">Unpaid</span>@endif
        </div>
      </div>

      @if($purchase->payment_status !== 'paid')
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Record Payment</h2>
        <form method="POST" action="{{ route('purchases.payment',$purchase) }}" class="space-y-4">
          @csrf
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount <span class="text-red-500">*</span></label>
            <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span><input type="number" name="amount" value="{{ old('amount',$purchase->amount_due) }}" step="0.01" min="0.01" max="{{ $purchase->amount_due }}" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none"></div>
            @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
            <input type="date" name="payment_date" value="{{ old('payment_date',today()->toDateString()) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Method <span class="text-red-500">*</span></label>
            <select name="method" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
              <option value="cash">Cash</option>
              <option value="bank">Bank Transfer</option>
              <option value="jazzcash">JazzCash</option>
              <option value="easypaisa">EasyPaisa</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Note</label>
            <input type="text" name="note" value="{{ old('note') }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          </div>
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">Record Payment</button>
        </form>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
