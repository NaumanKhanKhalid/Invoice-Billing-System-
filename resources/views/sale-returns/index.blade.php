@extends('layouts.app')
@section('title', 'Sale Returns')
@section('content')
<div class="space-y-5">

  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Sale Returns</h1>
      <p class="text-sm text-slate-500 mt-0.5">Wapas aane wale items ka record</p>
    </div>
    <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Sales
    </a>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Return #</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Original Sale</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Reason</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Refund</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Method</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($returns as $r)
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3">
              <span class="text-sm font-semibold text-red-600">{{ $r->return_number }}</span>
            </td>
            <td class="px-5 py-3 text-sm text-green-600 font-medium">
              {{ $r->sale?->sale_number ?? '—' }}
            </td>
            <td class="px-5 py-3 text-sm text-slate-600">
              {{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}
            </td>
            <td class="px-5 py-3 text-sm text-slate-500 max-w-xs truncate">
              {{ $r->reason ?: '—' }}
            </td>
            <td class="px-5 py-3 text-sm font-bold text-red-600 text-right">
              PKR {{ number_format($r->total) }}
            </td>
            <td class="px-5 py-3">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 capitalize">
                {{ $r->refund_method }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <a href="{{ route('sale-returns.show', $r) }}" class="text-slate-400 hover:text-green-600 transition-colors">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-5 py-16 text-center">
              <i data-lucide="rotate-ccw" class="w-10 h-10 text-slate-200 mx-auto mb-3"></i>
              <p class="text-slate-400 text-sm">Koi return record nahi mila</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($returns->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
      {{ $returns->links() }}
    </div>
    @endif
  </div>

</div>
@endsection
