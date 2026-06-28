@extends('layouts.app')
@section('title','Daily Closing')
@section('content')
<div class="space-y-5">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Daily Closing</h1>
      <p class="text-sm text-slate-500 mt-0.5">Day end summaries — cash & profit records</p>
    </div>
    <a href="{{ route('day-summary.create') }}"
       class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="moon" class="w-4 h-4"></i>Close Today
    </a>
  </div>

  {{-- Filter --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap items-center gap-3">
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="date" name="from_date" value="{{ request('from_date') }}"
               class="pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <span class="text-slate-400 text-sm">to</span>
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="date" name="to_date" value="{{ request('to_date') }}"
               class="pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <button type="submit" class="flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="search" class="w-4 h-4"></i>Filter
      </button>
      @if(request()->hasAny(['from_date','to_date']))
      <a href="{{ route('day-summary.index') }}" class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg text-sm">
        <i data-lucide="x" class="w-4 h-4"></i>Clear
      </a>
      @endif
      <div class="ml-auto flex items-center gap-5 text-sm text-slate-500">
        <span>Revenue: <strong class="text-green-600">PKR {{ number_format($totalRevenue) }}</strong></span>
        <span>Profit: <strong class="{{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $totalProfit >= 0 ? '+' : '' }}PKR {{ number_format($totalProfit) }}</strong></span>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">POS Sales</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Revenue</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Expenses</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Cash</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Difference</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Net Profit</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($records as $r)
        @php $diff = $r->cash_difference; @endphp
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm font-semibold text-slate-900">
            {{ $r->date->format('d M Y') }}
            <p class="text-xs text-slate-400 font-normal">{{ $r->date->format('l') }}</p>
          </td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $r->pos_sales_count }} txn</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-green-700">PKR {{ number_format($r->pos_revenue) }}</td>
          <td class="px-4 py-3 text-sm text-right text-orange-600">PKR {{ number_format($r->total_expenses) }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-700">PKR {{ number_format($r->cash_received) }}</td>
          <td class="px-4 py-3 text-sm text-right font-semibold {{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $diff >= 0 ? '+' : '' }}PKR {{ number_format(abs($diff)) }}
          </td>
          <td class="px-4 py-3 text-sm text-right font-bold {{ $r->net_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $r->net_profit >= 0 ? '+' : '' }}PKR {{ number_format($r->net_profit) }}
          </td>
          <td class="px-4 py-3">
            @if($r->is_closed)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
              <i data-lucide="lock" class="w-3 h-3"></i>Closed
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Open
            </span>
            @endif
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <a href="{{ route('day-summary.show', $r) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              @if(!$r->is_closed)
              <form method="POST" action="{{ route('day-summary.destroy', $r) }}"
                    data-confirm-title="Delete Summary?" data-confirm-message="Delete day summary for {{ $r->date->format('d M Y') }}?" data-confirm-text="Delete" data-confirm-danger="true">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors">
                  <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="9" class="px-4 py-16 text-center">
          <i data-lucide="moon" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">No daily summaries yet</p>
          <a href="{{ route('day-summary.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Close today's day</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($records->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $records->links() }}</div>
    @endif
  </div>
</div>
@endsection
