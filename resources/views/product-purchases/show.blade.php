@extends('layouts.app')
@section('title','Purchase #'.$productPurchase->id)
@section('content')
<div class="space-y-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('product-purchases.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
      <h1 class="text-xl font-bold text-slate-900">Purchase #{{ $productPurchase->id }}</h1>
      <p class="text-sm text-slate-500">{{ $productPurchase->date->format('d M Y') }} · {{ $productPurchase->supplier?->name ?? 'No Supplier' }}</p>
    </div>
    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
      {{ $productPurchase->payment_status === 'paid' ? 'bg-green-100 text-green-700' : ($productPurchase->payment_status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
      {{ ucfirst($productPurchase->payment_status) }}
    </span>
    <a href="{{ route('purchase-returns.create', $productPurchase) }}"
       class="inline-flex items-center gap-1.5 bg-orange-50 hover:bg-orange-100 text-orange-600 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
      <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Return to Supplier
    </a>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
      <h2 class="font-semibold text-slate-900">Items Purchased</h2>
    </div>
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Product</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Qty</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Unit Price</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($productPurchase->items as $item)
        <tr>
          <td class="px-4 py-3 text-sm font-medium text-slate-900">
            <a href="{{ route('products.show', $item->product) }}" class="hover:text-green-600">{{ $item->product->name }}</a>
            @if($batch = $batches->get($item->id))
            <p class="text-xs text-slate-400 mt-0.5">
              @if($batch->batch_no)Batch: {{ $batch->batch_no }} · @endif
              <span class="{{ $batch->isExpired() ? 'text-red-500 font-medium' : '' }}">Expiry: {{ $batch->expiry_date->format('M Y') }}</span>
            </p>
            @endif
          </td>
          <td class="px-4 py-3 text-sm text-center text-slate-600">{{ $item->qty }} {{ $item->product->unit }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ number_format($item->unit_price) }}</td>
          <td class="px-4 py-3 text-sm text-right font-semibold text-slate-900">{{ number_format($item->total) }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot class="bg-slate-50 border-t-2 border-slate-200">
        <tr>
          <td colspan="3" class="px-4 py-3 text-sm font-bold text-slate-900">Total</td>
          <td class="px-4 py-3 text-sm text-right font-bold text-slate-900">PKR {{ number_format($productPurchase->total_amount) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  {{-- Payment --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-3">Payment Summary</h2>
    <div class="grid grid-cols-3 gap-4 text-center mb-4">
      <div class="bg-slate-50 rounded-lg p-3">
        <p class="text-xs text-slate-400 mb-1">Total</p>
        <p class="font-bold text-slate-900">{{ number_format($productPurchase->total_amount) }}</p>
      </div>
      <div class="bg-green-50 rounded-lg p-3">
        <p class="text-xs text-slate-400 mb-1">Paid</p>
        <p class="font-bold text-green-600">{{ number_format($productPurchase->amount_paid) }}</p>
      </div>
      <div class="bg-{{ $productPurchase->amount_due > 0 ? 'red' : 'slate' }}-50 rounded-lg p-3">
        <p class="text-xs text-slate-400 mb-1">Due</p>
        <p class="font-bold text-{{ $productPurchase->amount_due > 0 ? 'red-600' : 'slate-400' }}">{{ number_format($productPurchase->amount_due) }}</p>
      </div>
    </div>
    @if($productPurchase->amount_due > 0)
    <form method="POST" action="{{ route('product-purchases.payment', $productPurchase) }}" class="flex gap-3">
      @csrf
      <input type="number" name="amount" min="0.01" max="{{ $productPurchase->amount_due }}" step="0.01"
             placeholder="Payment amount..." required
             class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Record Payment</button>
    </form>
    @endif
  </div>

  <form method="POST" action="{{ route('product-purchases.destroy', $productPurchase) }}"
        data-confirm-title="Delete Purchase?" data-confirm-message="Stock will be reversed automatically." data-confirm-danger="true">
    @csrf @method('DELETE')
    <button type="submit" class="w-full border border-red-200 text-red-600 hover:bg-red-50 py-2 rounded-lg text-sm font-medium">
      Delete Purchase (reverses stock)
    </button>
  </form>
</div>
@endsection
