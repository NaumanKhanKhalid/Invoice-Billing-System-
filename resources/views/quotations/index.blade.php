@extends('layouts.app')
@section('title', 'Quotations')
@section('content')
<div class="space-y-5">

  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Quotations</h1>
      <p class="text-sm text-slate-500 mt-0.5">Customer ko estimate / quote bhejein</p>
    </div>
    <a href="{{ route('quotations.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> New Quotation
    </a>
  </div>

  {{-- Status Summary --}}
  <div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase tracking-wider font-medium">Draft</p>
      <p class="text-2xl font-bold text-slate-700 mt-1">{{ $counts['draft'] }}</p>
    </div>
    <div class="bg-amber-50 rounded-xl border border-amber-200 shadow-sm p-4 text-center">
      <p class="text-xs text-amber-500 uppercase tracking-wider font-medium">Sent</p>
      <p class="text-2xl font-bold text-amber-700 mt-1">{{ $counts['sent'] }}</p>
    </div>
    <div class="bg-green-50 rounded-xl border border-green-200 shadow-sm p-4 text-center">
      <p class="text-xs text-green-500 uppercase tracking-wider font-medium">Accepted</p>
      <p class="text-2xl font-bold text-green-700 mt-1">{{ $counts['accepted'] }}</p>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Quote #</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Valid Until</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($quotations as $q)
          @php
          $statusClass = [
            'draft'    => 'bg-slate-100 text-slate-600',
            'sent'     => 'bg-amber-100 text-amber-700',
            'accepted' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-600',
            'expired'  => 'bg-slate-100 text-slate-400',
          ][$q->status] ?? 'bg-slate-100 text-slate-600';
          @endphp
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3 text-sm font-semibold text-green-700">{{ $q->quote_number }}</td>
            <td class="px-5 py-3">
              <p class="text-sm font-medium text-slate-800">{{ $q->customer_name ?: '—' }}</p>
              @if($q->customer_phone)<p class="text-xs text-slate-400">{{ $q->customer_phone }}</p>@endif
            </td>
            <td class="px-5 py-3 text-sm text-slate-600">{{ $q->date->format('d M Y') }}</td>
            <td class="px-5 py-3 text-sm text-slate-500">
              {{ $q->valid_until ? $q->valid_until->format('d M Y') : '—' }}
            </td>
            <td class="px-5 py-3 text-sm font-bold text-slate-900 text-right">PKR {{ number_format($q->total) }}</td>
            <td class="px-5 py-3">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                {{ ucfirst($q->status) }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <a href="{{ route('quotations.show', $q) }}" class="text-slate-400 hover:text-green-600 transition-colors">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-5 py-16 text-center">
              <i data-lucide="file-text" class="w-10 h-10 text-slate-200 mx-auto mb-3"></i>
              <p class="text-slate-400 text-sm">Koi quotation nahi mili</p>
              <a href="{{ route('quotations.create') }}" class="mt-3 inline-flex items-center gap-1 text-green-600 hover:underline text-sm font-medium">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Pehli quotation banayein
              </a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($quotations->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $quotations->links() }}</div>
    @endif
  </div>
</div>
@endsection
