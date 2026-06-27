@extends('layouts.app')
@section('title', 'Purchase Returns')
@section('content')
<div class="space-y-5">

  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Purchase Returns</h1>
      <p class="text-sm text-slate-500 mt-0.5">Supplier ko wapas kiya gaya maal</p>
    </div>
    <a href="{{ route('product-purchases.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Purchases
    </a>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Return #</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Purchase</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Reason</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Adjustment</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($returns as $r)
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3">
              <span class="text-sm font-semibold text-orange-600">{{ $r->return_number }}</span>
            </td>
            <td class="px-5 py-3 text-sm text-slate-700">{{ $r->supplier?->name ?? '—' }}</td>
            <td class="px-5 py-3 text-sm text-green-600 font-medium">
              {{ $r->purchase?->invoice_number ?: ($r->purchase ? '#'.$r->purchase->id : '—') }}
            </td>
            <td class="px-5 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}</td>
            <td class="px-5 py-3 text-sm text-slate-500 max-w-xs truncate">{{ $r->reason ?: '—' }}</td>
            <td class="px-5 py-3 text-sm font-bold text-orange-600 text-right">PKR {{ number_format($r->total) }}</td>
            <td class="px-5 py-3">
              @php $adjLabels = ['deduct_balance'=>'Balance Kam','cash_refund'=>'Cash Wapas','exchange'=>'Exchange']; @endphp
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                {{ $adjLabels[$r->adjustment_method] ?? $r->adjustment_method }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <a href="{{ route('purchase-returns.show', $r) }}" class="text-slate-400 hover:text-green-600 transition-colors">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="px-5 py-16 text-center">
              <i data-lucide="rotate-ccw" class="w-10 h-10 text-slate-200 mx-auto mb-3"></i>
              <p class="text-slate-400 text-sm">Koi purchase return record nahi mila</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($returns->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $returns->links() }}</div>
    @endif
  </div>
</div>
@endsection
