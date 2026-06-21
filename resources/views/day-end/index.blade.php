@extends('layouts.app')
@section('title','Daily Records')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Daily Records</h1>
      <p class="text-sm text-slate-500 mt-0.5">Din Band Karo — Day End Entries</p>
    </div>
    <a href="{{ route('day-end.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="moon" class="w-4 h-4"></i>Din Band Karo
    </a>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Chicken Type</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Supply Revenue</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Counter Cash</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total Revenue</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Net Profit</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
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
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Closed</span>
            @else
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Open</span>
            @endif
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('day-end.show',$record) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors"><i data-lucide="eye" class="w-3.5 h-3.5"></i>View</a>
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
