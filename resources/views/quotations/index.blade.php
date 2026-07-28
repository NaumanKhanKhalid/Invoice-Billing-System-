@extends('layouts.app')
@section('title', 'Quotations')
@section('content')
<div class="space-y-5">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Quotations</h1>
      <p class="text-sm text-slate-500 mt-0.5">Customer ko estimate / quote bhejein</p>
    </div>
    <a href="{{ route('quotations.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> New Quotation
    </a>
  </div>

  {{-- Stat cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total</p>
        <span class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i></span>
      </div>
      <p class="text-2xl font-bold text-slate-800 mt-2">{{ $counts['all'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-4">
      <div class="flex items-center justify-between">
        <p class="text-xs text-amber-500 uppercase tracking-wider font-semibold">Sent</p>
        <span class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center"><i data-lucide="send" class="w-4 h-4 text-amber-600"></i></span>
      </div>
      <p class="text-2xl font-bold text-amber-700 mt-2">{{ $counts['sent'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-4">
      <div class="flex items-center justify-between">
        <p class="text-xs text-green-500 uppercase tracking-wider font-semibold">Accepted</p>
        <span class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center"><i data-lucide="check-circle-2" class="w-4 h-4 text-green-600"></i></span>
      </div>
      <p class="text-2xl font-bold text-green-700 mt-2">{{ $counts['accepted'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Accepted Value</p>
        <span class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center"><i data-lucide="banknote" class="w-4 h-4 text-green-600"></i></span>
      </div>
      <p class="text-xl font-bold text-slate-900 mt-2 tabular-nums">PKR {{ number_format($acceptedValue) }}</p>
    </div>
  </div>

  {{-- Filters --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <div class="flex flex-wrap items-center gap-3">
      @php $active = request('status', 'all'); @endphp
      <div class="flex rounded-lg border border-slate-200 overflow-hidden">
        @foreach(['all'=>'All','draft'=>'Draft','sent'=>'Sent','accepted'=>'Accepted'] as $key => $label)
        <a href="{{ route('quotations.index', array_merge(request()->except('status','page'), ['status'=>$key])) }}"
           class="px-4 py-2 text-sm font-medium transition-colors border-l first:border-l-0 border-slate-200 {{ $active === $key ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
        @endforeach
      </div>

      <form method="GET" class="flex gap-2 flex-1 min-w-[200px]">
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <div class="relative flex-1">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Quote #, customer ya phone..."
                 class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
        </div>
        <button type="submit" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <i data-lucide="search" class="w-4 h-4"></i> Search
        </button>
        @if(request('search'))
        <a href="{{ route('quotations.index', request()->except('search','page')) }}" class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
          <i data-lucide="x" class="w-4 h-4"></i> Clear
        </a>
        @endif
      </form>
    </div>
  </div>

  {{-- Table --}}
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
          $isExpired = $q->valid_until && $q->valid_until->isPast() && !in_array($q->status, ['accepted','rejected']);
          @endphp
          <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location='{{ route('quotations.show', $q) }}'">
            <td class="px-5 py-3 text-sm font-semibold text-green-700 font-mono">{{ $q->quote_number }}</td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-bold uppercase shrink-0">{{ mb_substr($q->customer_name ?: '?', 0, 1) }}</span>
                <div>
                  <p class="text-sm font-medium text-slate-800">{{ $q->customer_name ?: '—' }}</p>
                  @if($q->customer_phone)<p class="text-xs text-slate-400">{{ $q->customer_phone }}</p>@endif
                </div>
              </div>
            </td>
            <td class="px-5 py-3 text-sm text-slate-600 whitespace-nowrap">{{ $q->date->format('d M Y') }}</td>
            <td class="px-5 py-3 text-sm whitespace-nowrap {{ $isExpired ? 'text-red-500 font-semibold' : 'text-slate-500' }}">
              {{ $q->valid_until ? $q->valid_until->format('d M Y') : '—' }}
              @if($isExpired)<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600">Expired</span>@endif
            </td>
            <td class="px-5 py-3 text-sm font-bold text-slate-900 text-right whitespace-nowrap tabular-nums">PKR {{ number_format($q->total) }}</td>
            <td class="px-5 py-3">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ ucfirst($q->status) }}</span>
            </td>
            <td class="px-5 py-3 text-right">
              <span class="inline-flex text-slate-300"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
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
