@extends('layouts.app')
@section('title','Udhar Book')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Udhar Book</h1>
      <p class="text-sm text-slate-500 mt-0.5">Credit sales and payment tracking</p>
    </div>
    <a href="{{ route('udhar.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>New Udhar
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Due</p>
      <p class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency($stats['total_due']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-red-300 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Overdue Records</p>
      <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['overdue_count'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Due Today</p>
      <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['today_due'] }}</p>
    </div>
  </div>

  {{-- Filters --}}
  <div class="flex flex-wrap items-center gap-3">
    {{-- Status pills --}}
    <div class="flex rounded-lg border border-slate-200 overflow-hidden bg-white shadow-sm">
      <a href="{{ route('udhar.index', array_merge(request()->except('status','page'), ['status'=>'all'])) }}"
         class="px-4 py-2 text-sm font-medium transition-colors {{ request('status','all')==='all' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50' }}">All</a>
      <a href="{{ route('udhar.index', array_merge(request()->except('status','page'), ['status'=>'unpaid'])) }}"
         class="px-4 py-2 text-sm font-medium border-l border-slate-200 transition-colors {{ request('status')==='unpaid' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Unpaid</a>
      <a href="{{ route('udhar.index', array_merge(request()->except('status','page'), ['status'=>'overdue'])) }}"
         class="px-4 py-2 text-sm font-medium border-l border-slate-200 transition-colors {{ request('status')==='overdue' ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Overdue</a>
    </div>

    {{-- Search --}}
    <form method="GET" class="flex gap-2 flex-1 min-w-[200px]">
      @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      <input type="text" name="search" value="{{ request('search') }}"
             placeholder="Search customer or phone..."
             class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Search</button>
      @if(request('search'))<a href="{{ route('udhar.index', request()->except('search','page')) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>@endif
    </form>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Phone</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Amount</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Paid</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Due</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Due Date</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($records as $record)
        @php
          $isOverdue = in_array($record->status, ['unpaid','partial']) && $record->due_date->isPast();
          $isToday   = $record->due_date->isToday();
        @endphp
        <tr class="hover:bg-slate-50 transition-colors {{ $isOverdue ? 'bg-red-50' : '' }}">
          <td class="px-4 py-3 text-sm font-medium">
            <a href="{{ route('udhar.show', $record) }}" class="text-slate-900 hover:text-green-600">{{ $record->customer_name }}</a>
            @if($record->description)<p class="text-xs text-slate-400 mt-0.5">{{ $record->description }}</p>@endif
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $record->phone ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($record->amount) }}</td>
          <td class="px-4 py-3 text-sm text-right text-green-600 font-medium">{{ formatCurrency($record->amount_paid) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold {{ $record->amount_due > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ formatCurrency($record->amount_due) }}</td>
          <td class="px-4 py-3 text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : ($isToday ? 'text-amber-600 font-semibold' : 'text-slate-600') }}">
            {{ $record->due_date->format('d M Y') }}
          </td>
          <td class="px-4 py-3">
            @if($record->status === 'paid')
              <span class="badge badge-green">Paid</span>
            @elseif($record->status === 'partial')
              <span class="badge badge-yellow">Partial</span>
            @else
              <span class="badge badge-red">Unpaid</span>
            @endif
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('udhar.show', $record) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              @if($record->status === 'unpaid')
              <form method="POST" action="{{ route('udhar.destroy', $record) }}" class="inline"
                    onsubmit="return confirm('Delete this udhar record for {{ addslashes($record->customer_name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors">
                  <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete
                </button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="px-4 py-12 text-center">
            <i data-lucide="book-open" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
            <p class="text-slate-500 font-medium">No udhar records found</p>
            <a href="{{ route('udhar.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add first udhar record</a>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($records->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $records->links() }}</div>
    @endif
  </div>

</div>
@endsection
