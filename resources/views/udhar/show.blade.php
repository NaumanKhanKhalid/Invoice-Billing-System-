@extends('layouts.app')
@section('title', $creditSale->customer_name . ' — Udhar')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('udhar.index') }}" class="text-slate-400 hover:text-slate-600">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <div>
        @if($creditSale->udharCustomer)
        <a href="{{ route('udhar-customers.show', $creditSale->udharCustomer) }}"
           class="text-2xl font-bold text-slate-900 hover:text-green-600">{{ $creditSale->customer_name }}</a>
        @else
        <h1 class="text-2xl font-bold text-slate-900">{{ $creditSale->customer_name }}</h1>
        @endif
        <p class="text-sm text-slate-500">Udhar since {{ $creditSale->sale_date->format('d M Y') }}</p>
      </div>
    </div>
  </div>

  {{-- Overdue banner --}}
  @php $isOverdue = in_array($creditSale->status, ['unpaid','partial']) && $creditSale->due_date->isPast(); @endphp
  @if($isOverdue)
  <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
    <span>Payment is overdue since {{ $creditSale->due_date->format('d M Y') }}.</span>
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Details + Payment History --}}
    <div class="lg:col-span-2 space-y-5">

      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-900 mb-4">Udhar Details</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-slate-500">Customer</p>
            <p class="font-medium text-slate-900 mt-0.5">{{ $creditSale->customer_name }}</p>
            @if($creditSale->udharCustomer)
            <a href="{{ route('udhar-customers.show', $creditSale->udharCustomer) }}" class="text-xs text-green-600 hover:underline">View account →</a>
            @endif
          </div>
          <div>
            <p class="text-slate-500">Phone</p>
            <p class="font-medium text-slate-900 mt-0.5">{{ $creditSale->phone ?? '-' }}</p>
          </div>
          <div>
            <p class="text-slate-500">Description</p>
            <p class="font-medium text-slate-900 mt-0.5">{{ $creditSale->description ?? '-' }}</p>
          </div>
          <div>
            <p class="text-slate-500">Sale Date</p>
            <p class="font-medium text-slate-900 mt-0.5">{{ $creditSale->sale_date->format('d M Y') }}</p>
          </div>
          <div>
            <p class="text-slate-500">Due Date</p>
            <p class="font-medium mt-0.5 {{ $isOverdue ? 'text-red-600' : 'text-slate-900' }}">{{ $creditSale->due_date->format('d M Y') }}</p>
          </div>
        </div>
        @if($creditSale->notes)
        <div class="mt-4 p-3 bg-slate-50 rounded-lg">
          <p class="text-xs text-slate-500 mb-1">Notes</p>
          <p class="text-sm text-slate-700">{{ $creditSale->notes }}</p>
        </div>
        @endif
      </div>

      {{-- Payment History --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
          <h2 class="font-semibold text-slate-900">Payment History</h2>
        </div>
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Method</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Note</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Proof</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($creditSale->payments as $payment)
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3 text-sm text-slate-600">{{ $payment->payment_date->format('d M Y') }}</td>
              <td class="px-4 py-3 text-sm"><span class="badge badge-blue">{{ ucfirst($payment->method) }}</span></td>
              <td class="px-4 py-3 text-sm text-right font-medium text-green-600">{{ formatCurrency($payment->amount) }}</td>
              <td class="px-4 py-3 text-sm text-slate-500">{{ $payment->note ?? '-' }}</td>
              <td class="px-4 py-3 text-center">
                @if($payment->proof_photo)
                <a href="{{ Storage::url($payment->proof_photo) }}" target="_blank" title="View proof">
                  <img src="{{ Storage::url($payment->proof_photo) }}" alt="Proof"
                       class="w-10 h-10 object-cover rounded-lg border border-slate-200 inline-block hover:opacity-80 transition-opacity cursor-zoom-in">
                </a>
                @else
                <span class="text-slate-300 text-xs">—</span>
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

    {{-- Right: Summary + Record Payment --}}
    <div class="space-y-5">

      {{-- Summary card --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
        <h2 class="font-semibold text-slate-900">Payment Summary</h2>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Total Amount</span>
          <span class="font-bold text-slate-900">{{ formatCurrency($creditSale->amount) }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Amount Paid</span>
          <span class="font-medium text-green-600">{{ formatCurrency($creditSale->amount_paid) }}</span>
        </div>
        <div class="border-t border-slate-100 pt-3 flex justify-between">
          <span class="font-semibold text-slate-700">Amount Due</span>
          <span class="font-bold text-xl {{ $creditSale->amount_due > 0 ? 'text-red-600' : 'text-green-600' }}">{{ formatCurrency($creditSale->amount_due) }}</span>
        </div>
        <div class="pt-1">
          @if($creditSale->status === 'paid')
            <span class="badge badge-green w-full justify-center py-2">Fully Paid</span>
          @elseif($creditSale->status === 'partial')
            <span class="badge badge-yellow w-full justify-center py-2">Partially Paid</span>
          @else
            <span class="badge badge-red w-full justify-center py-2">Unpaid</span>
          @endif
        </div>

        {{-- WhatsApp button --}}
        @if($creditSale->phone && $creditSale->status !== 'paid')
        @php
          $waMsg = urlencode("Assalam o Alaikum " . $creditSale->customer_name . ", aap ka " . formatCurrency($creditSale->amount_due) . " udhar " . $creditSale->due_date->format('d M Y') . " tak dena hai. - Anwar Chicken Center");
          $waPhone = preg_replace('/[^0-9]/', '', $creditSale->phone);
          if (str_starts_with($waPhone, '0')) $waPhone = '92' . substr($waPhone, 1);
        @endphp
        <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank"
           class="mt-2 w-full inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg text-sm font-medium transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
          Send WhatsApp Reminder
        </a>
        @endif
      </div>

      {{-- Record Payment form --}}
      @if($creditSale->status !== 'paid')
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5" x-data="{ method: 'cash' }">
        <h2 class="font-semibold text-slate-900 mb-4">Record Payment</h2>
        <form method="POST" action="{{ route('udhar.payment', $creditSale) }}" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
              <input type="number" name="amount" value="{{ old('amount', $creditSale->amount_due) }}"
                     step="0.01" min="0.01" max="{{ $creditSale->amount_due }}" required
                     class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
            </div>
            @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
            <input type="date" name="payment_date" value="{{ old('payment_date', today()->toDateString()) }}" required
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
            @error('payment_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Method <span class="text-red-500">*</span></label>
            <select name="method" x-model="method" required
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
              <option value="cash">Cash</option>
              <option value="bank">Bank Transfer</option>
              <option value="jazzcash">JazzCash</option>
              <option value="easypaisa">EasyPaisa</option>
            </select>
            @error('method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>

          {{-- Proof photo — only for digital payments --}}
          <div x-show="method !== 'cash'" x-cloak>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Proof <span class="text-slate-400 text-xs font-normal">(screenshot optional)</span></label>
            <input type="file" name="proof_photo" accept="image/*"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            @error('proof_photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Note</label>
            <input type="text" name="note" value="{{ old('note') }}"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
            @error('note')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
            Record Payment
          </button>
        </form>
      </div>
      @endif

    </div>
  </div>

</div>
@endsection
