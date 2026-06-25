@extends('layouts.app')
@section('title','Payment History — ' . $tenant->shop_name)
@section('content')
<div class="space-y-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('admin.tenants.show', $tenant) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Payment History</h1>
      <p class="text-sm text-slate-500">{{ $tenant->shop_name }} — {{ $tenant->owner_name }}</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <div>
        <p class="font-semibold text-slate-900">{{ $payments->count() }} Payment{{ $payments->count() !== 1 ? 's' : '' }}</p>
        <p class="text-sm text-slate-500">Total: PKR {{ number_format($payments->sum('amount')) }}</p>
      </div>
    </div>
    @if($payments->count())
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Paid On</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Period</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Plan</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Method</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reference</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($payments as $p)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $p->paid_at->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $p->period_from->format('d M Y') }} → {{ $p->period_to->format('d M Y') }}</td>
          <td class="px-4 py-3">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold uppercase
              {{ $p->plan === 'business' ? 'bg-purple-100 text-purple-700' : ($p->plan === 'pro' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
              {{ $p->plan }}
            </span>
            <span class="text-xs text-slate-400 ml-1">×{{ $p->months }}mo</span>
          </td>
          <td class="px-4 py-3 text-sm text-right font-bold text-slate-900">PKR {{ number_format($p->amount) }}</td>
          <td class="px-4 py-3 text-sm text-slate-600 capitalize">{{ $p->method }}</td>
          <td class="px-4 py-3 text-sm text-slate-400">{{ $p->reference ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot class="bg-slate-50 border-t-2 border-slate-200">
        <tr>
          <td colspan="3" class="px-4 py-3 text-sm font-bold text-slate-900">Total</td>
          <td class="px-4 py-3 text-sm text-right font-bold text-green-600">PKR {{ number_format($payments->sum('amount')) }}</td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
    @else
    <div class="px-4 py-12 text-center">
      <i data-lucide="credit-card" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-400">No payments recorded yet</p>
    </div>
    @endif
  </div>
</div>
@endsection
