@extends('layouts.app')
@section('title', $sale->invoice_number)
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('sales.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $sale->invoice_number }}</h1>
        <p class="text-sm text-slate-500">{{ $sale->customer?->name ?? 'Walk-in' }} · {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</p>
      </div>
    </div>
    <div class="flex gap-2">
      @if($whatsappLink)
      <a href="{{ $whatsappLink }}" target="_blank" class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="message-circle" class="w-4 h-4"></i>WhatsApp
      </a>
      @endif
      @if($sale->payment_status!=='paid')
      <a href="{{ route('sales.edit',$sale) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="pencil" class="w-4 h-4"></i>Edit
      </a>
      @endif
    </div>
  </div>

  @php $isOverdue = $sale->payment_status!=='paid' && $sale->due_date && \Carbon\Carbon::parse($sale->due_date)->isPast(); @endphp
  @if($isOverdue)
  <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
    <span>Payment overdue since {{ \Carbon\Carbon::parse($sale->due_date)->format('d M Y') }}.</span>
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Sale Details</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-500">Customer</p><p class="font-medium text-slate-900 mt-0.5">{{ $sale->customer?->name ?? 'Walk-in' }}</p></div>
          <div><p class="text-slate-500">Order Type</p><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-0.5 {{ $sale->order_type==='supply'?'bg-blue-100 text-blue-700':'bg-slate-100 text-slate-600' }}">{{ ucfirst($sale->order_type) }}</span></div>
          <div><p class="text-slate-500">Date</p><p class="font-medium text-slate-900 mt-0.5">{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</p></div>
          <div><p class="text-slate-500">Due Date</p><p class="font-medium {{ $isOverdue?'text-red-600':'text-slate-900' }} mt-0.5">{{ $sale->due_date ? \Carbon\Carbon::parse($sale->due_date)->format('d M Y') : 'Same day' }}</p></div>
          <div><p class="text-slate-500">Chicken Type</p><p class="font-medium text-slate-900 mt-0.5">{{ $sale->chickenType?->name ?? '-' }}</p></div>
          <div><p class="text-slate-500">Dressed Weight</p><p class="font-medium text-slate-900 mt-0.5">{{ number_format($sale->dressed_weight_kg,3) }} kg</p></div>
          <div><p class="text-slate-500">Rate per kg</p><p class="font-medium text-slate-900 mt-0.5">PKR {{ number_format($sale->rate_per_kg,2) }}</p></div>
          <div><p class="text-slate-500">Total Amount</p><p class="font-bold text-green-700 mt-0.5 text-base">PKR {{ number_format($sale->total_amount,0) }}</p></div>
        </div>
        @if($sale->delivery_address)
        <div class="mt-4 p-3 bg-slate-50 rounded-lg">
          <p class="text-xs text-slate-500 mb-1">Delivery Address</p>
          <p class="text-sm text-slate-700">{{ $sale->delivery_address }}</p>
          @if($sale->delivery_notes)<p class="text-xs text-slate-500 mt-2">{{ $sale->delivery_notes }}</p>@endif
        </div>
        @endif
        @if($sale->notes)<div class="mt-3 p-3 bg-slate-50 rounded-lg"><p class="text-xs text-slate-500 mb-1">Notes</p><p class="text-sm text-slate-700">{{ $sale->notes }}</p></div>@endif
      </div>

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
            @forelse($sale->salePayments as $payment)
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
              <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($payment->method) }}</span></td>
              <td class="px-4 py-3 text-sm text-right font-medium text-green-600">PKR {{ number_format($payment->amount,0) }}</td>
              <td class="px-4 py-3 text-sm text-slate-500">{{ $payment->note ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400 text-sm">No payments yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="space-y-5">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
        <h2 class="font-semibold text-slate-900">Payment Summary</h2>
        <div class="flex justify-between text-sm text-slate-500"><span>{{ number_format($sale->dressed_weight_kg,1) }} kg × PKR {{ number_format($sale->rate_per_kg,2) }}/kg</span></div>
        <div class="flex justify-between text-sm"><span class="text-slate-500">Total</span><span class="font-bold text-slate-900">PKR {{ number_format($sale->total_amount,0) }}</span></div>
        <div class="flex justify-between text-sm"><span class="text-slate-500">Paid</span><span class="font-medium text-green-600">PKR {{ number_format($sale->amount_paid,0) }}</span></div>
        <div class="border-t border-slate-100 pt-3 flex justify-between">
          <span class="font-semibold text-slate-700">Due</span>
          <span class="font-bold text-xl {{ $sale->amount_due>0?'text-red-600':'text-green-600' }}">PKR {{ number_format($sale->amount_due,0) }}</span>
        </div>
        <div>
          @if($sale->payment_status==='paid')<span class="inline-flex w-full justify-center items-center px-3 py-2 rounded text-sm font-medium bg-green-100 text-green-700">Fully Paid</span>
          @elseif($sale->payment_status==='partial')<span class="inline-flex w-full justify-center items-center px-3 py-2 rounded text-sm font-medium bg-yellow-100 text-yellow-700">Partially Paid</span>
          @else<span class="inline-flex w-full justify-center items-center px-3 py-2 rounded text-sm font-medium bg-red-100 text-red-700">Unpaid</span>@endif
        </div>
      </div>

      @if($sale->payment_status!=='paid')
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Record Payment</h2>
        <form method="POST" action="{{ route('sales.payment',$sale) }}" class="space-y-4">
          @csrf
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount <span class="text-red-500">*</span></label>
            <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="amount" value="{{ old('amount',$sale->amount_due) }}" step="0.01" min="0.01" max="{{ $sale->amount_due }}" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none"></div>
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
