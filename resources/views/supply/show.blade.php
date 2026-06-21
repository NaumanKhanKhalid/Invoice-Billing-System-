@extends('layouts.app')
@section('title', $supply->invoice_number)
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('supply.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $supply->invoice_number }}</h1>
        <p class="text-sm text-slate-500">{{ $supply->customer?->name ?? '—' }} · {{ \Carbon\Carbon::parse($supply->date)->format('d M Y') }}</p>
      </div>
    </div>
    <div class="flex gap-2">
      @if($whatsappLink)
      <a href="{{ $whatsappLink }}" target="_blank" class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="message-circle" class="w-4 h-4"></i>WhatsApp
      </a>
      @endif
      @if($supply->payment_status!=='paid')
      <a href="{{ route('supply.edit',$supply) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="pencil" class="w-4 h-4"></i>Edit
      </a>
      @endif
    </div>
  </div>

  @php $isOverdue = $supply->payment_status!=='paid' && $supply->due_date && \Carbon\Carbon::parse($supply->due_date)->isPast(); @endphp
  @if($isOverdue)
  <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
    <span>Payment overdue since {{ \Carbon\Carbon::parse($supply->due_date)->format('d M Y') }}.</span>
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Order Details</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-500">Customer</p><p class="font-medium text-slate-900 mt-0.5">{{ $supply->customer?->name ?? '—' }}</p></div>
          <div><p class="text-slate-500">Date</p><p class="font-medium text-slate-900 mt-0.5">{{ \Carbon\Carbon::parse($supply->date)->format('d M Y') }}</p></div>
          @if($supply->delivery_date && $supply->delivery_date->toDateString() !== $supply->date->toDateString())
          <div><p class="text-slate-500">Delivery Date</p><p class="font-medium text-slate-900 mt-0.5">{{ $supply->delivery_date->format('d M Y') }}</p></div>
          @endif
          <div><p class="text-slate-500">Due Date</p><p class="font-medium {{ $isOverdue?'text-red-600':'text-slate-900' }} mt-0.5">{{ $supply->due_date ? \Carbon\Carbon::parse($supply->due_date)->format('d M Y') : 'Same day' }}</p></div>
          <div><p class="text-slate-500">Dressed Weight</p><p class="font-medium text-slate-900 mt-0.5">{{ formatKg($supply->dressed_weight_kg) }} kg</p></div>
          <div><p class="text-slate-500">Rate per kg</p><p class="font-medium text-slate-900 mt-0.5">PKR {{ formatKg($supply->rate_per_kg) }}</p></div>
          <div><p class="text-slate-500">Total Amount</p><p class="font-bold text-green-700 mt-0.5 text-base">{{ formatCurrency($supply->total_amount) }}</p></div>
          <div>
            <p class="text-slate-500">Delivery Status</p>
            @if($supply->is_delivered)
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700 mt-0.5">
                Delivered ✓
              </span>
              @if($supply->delivered_at)
              <p class="text-xs text-slate-400 mt-0.5">{{ $supply->delivered_at->format('d M Y, h:i A') }}</p>
              @endif
            @else
              <div class="flex items-center gap-2 mt-0.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700">Pending</span>
                <form method="POST" action="{{ route('supply.deliver', $supply) }}" class="inline">
                  @csrf @method('PATCH')
                  <button type="submit" onclick="return confirm('Deliver mark karo?')"
                          class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-100 hover:bg-green-200 text-green-700 text-xs font-semibold transition-colors">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Mark Delivered
                  </button>
                </form>
              </div>
            @endif
          </div>
        </div>
        @if($supply->delivery_address)
        <div class="mt-4 p-3 bg-slate-50 rounded-lg">
          <p class="text-xs text-slate-500 mb-1">Delivery Address</p>
          <p class="text-sm text-slate-700">{{ $supply->delivery_address }}</p>
          @if($supply->delivery_notes)<p class="text-xs text-slate-500 mt-2">{{ $supply->delivery_notes }}</p>@endif
        </div>
        @endif
        @if($supply->notes)<div class="mt-3 p-3 bg-slate-50 rounded-lg"><p class="text-xs text-slate-500 mb-1">Notes</p><p class="text-sm text-slate-700">{{ $supply->notes }}</p></div>@endif
      </div>

      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-900">Payment History</h2></div>
        <table class="w-full">
          <thead class="bg-slate-50"><tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Method</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Note</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Proof</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($supply->payments as $payment)
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
              <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($payment->method) }}</span></td>
              <td class="px-4 py-3 text-sm text-right font-medium text-green-600">{{ formatCurrency($payment->amount) }}</td>
              <td class="px-4 py-3 text-sm text-slate-500">{{ $payment->note ?? '—' }}</td>
              <td class="px-4 py-3 text-sm">
                @if($payment->proof_path)
                  <a href="{{ Storage::url($payment->proof_path) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs font-medium"><i data-lucide="paperclip" class="w-3 h-3"></i>View</a>
                @else
                  <span class="text-slate-300">—</span>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400 text-sm">No payments yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="space-y-5">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
        <h2 class="font-semibold text-slate-900">Payment Summary</h2>
        <div class="flex justify-between text-sm text-slate-500"><span>{{ formatKg($supply->dressed_weight_kg) }} kg × PKR {{ formatKg($supply->rate_per_kg) }}/kg</span></div>
        <div class="flex justify-between text-sm"><span class="text-slate-500">Total</span><span class="font-bold text-slate-900">{{ formatCurrency($supply->total_amount) }}</span></div>
        <div class="flex justify-between text-sm"><span class="text-slate-500">Paid</span><span class="font-medium text-green-600">{{ formatCurrency($supply->amount_paid) }}</span></div>
        <div class="border-t border-slate-100 pt-3 flex justify-between">
          <span class="font-semibold text-slate-700">Due</span>
          <span class="font-bold text-xl {{ $supply->amount_due>0?'text-red-600':'text-green-600' }}">{{ formatCurrency($supply->amount_due) }}</span>
        </div>
        <div>
          @if($supply->payment_status==='paid')<span class="inline-flex w-full justify-center items-center px-3 py-2 rounded text-sm font-medium bg-green-100 text-green-700">Fully Paid</span>
          @elseif($supply->payment_status==='partial')<span class="inline-flex w-full justify-center items-center px-3 py-2 rounded text-sm font-medium bg-yellow-100 text-yellow-700">Partially Paid</span>
          @else<span class="inline-flex w-full justify-center items-center px-3 py-2 rounded text-sm font-medium bg-red-100 text-red-700">Unpaid</span>@endif
        </div>
      </div>

      @if($supply->payment_status!=='paid')
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h2 class="font-semibold text-slate-900 mb-4">Record Payment</h2>
        <form method="POST" action="{{ route('supply.payment',$supply) }}" class="space-y-4" enctype="multipart/form-data">
          @csrf
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount <span class="text-red-500">*</span></label>
            <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="amount" value="{{ old('amount',$supply->amount_due) }}" step="0.01" min="0.01" max="{{ $supply->amount_due }}" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none"></div>
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
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Proof <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="file" name="proof" accept="image/*,.pdf" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <p class="text-xs text-slate-400 mt-1">Screenshot ya receipt upload karo (JPG, PNG, PDF · max 2MB)</p>
          </div>
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">Record Payment</button>
        </form>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
