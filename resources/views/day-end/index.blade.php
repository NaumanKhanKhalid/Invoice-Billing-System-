@extends('layouts.app')
@section('title','Daily Records')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Daily Records</h1>
      <p class="text-sm text-slate-500 mt-0.5">Close the day — Day End Entries</p>
    </div>
    <a href="{{ route('day-end.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="moon" class="w-4 h-4"></i>Close Day
    </a>
  </div>

  <form method="GET" class="flex flex-wrap items-center gap-2">
    <input type="date" name="from_date" value="{{ request('from_date') }}"
           class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <span class="text-slate-400 text-sm">to</span>
    <input type="date" name="to_date" value="{{ request('to_date') }}"
           class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    @if(request()->hasAny(['from_date','to_date']))<a href="{{ route('day-end.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>@endif
  </form>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden table-responsive">
    <table class="w-full">
      <thead class="bg-slate-50 border-b border-slate-200"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Supply Revenue</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Counter Cash</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Revenue</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Net Profit</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($records as $record)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm text-right text-blue-600">{{ formatCurrency($record->total_supply_revenue) }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-700">{{ formatCurrency($record->counter_cash) }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-green-700">{{ formatCurrency($record->total_revenue) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold {{ $record->net_profit>=0?'text-green-600':'text-red-600' }}">
            {{ $record->net_profit>=0?'+':'' }}{{ formatCurrency($record->net_profit) }}
          </td>
          <td class="px-4 py-3">
            @if($record->is_closed)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
              <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Closed
            </span>
            @else
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
              <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>Open
            </span>
            @endif
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('day-end.show',$record) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors"><i data-lucide="eye" class="w-3.5 h-3.5"></i>View</a>
              @if(!$record->is_closed)
              <form method="POST" action="{{ route('day-end.destroy',$record) }}"
                    data-confirm-title="Delete Day Record?" data-confirm-message="Delete record for {{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}? This cannot be undone." data-confirm-text="Yes, Delete" data-confirm-danger="true">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete</button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="px-4 py-12 text-center">
          <i data-lucide="moon" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">No daily records yet</p>
          <a href="{{ route('day-end.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add today's entry</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($records->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $records->links() }}</div>@endif
  </div>
</div>
@endsection
