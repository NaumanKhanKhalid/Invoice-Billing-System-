@extends('layouts.app')
@section('title','Udhar Book')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-2">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Udhar Book</h1>
      <p class="text-sm text-slate-500 mt-0.5">Credit sales and payment tracking</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('udhar-customers.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="users" class="w-4 h-4"></i>Customers
      </a>
      <a href="{{ route('udhar.report') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>Report
      </a>
      <a href="{{ route('udhar.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>New Udhar
      </a>
    </div>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Due</p>
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-red-600">{{ formatCurrency($stats['total_due']) }}</p>
      <p class="text-xs text-slate-400 mt-1">Total outstanding balance</p>
    </div>
    <div class="bg-white rounded-xl border border-red-300 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Overdue Records</p>
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i data-lucide="clock" class="w-4 h-4 text-red-600"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-red-700">{{ $stats['overdue_count'] }}</p>
      <p class="text-xs text-slate-400 mt-1">Past due date</p>
    </div>
    <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Due Today</p>
        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
          <i data-lucide="calendar-clock" class="w-4 h-4 text-amber-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-amber-600">{{ $stats['today_due'] }}</p>
      <p class="text-xs text-slate-400 mt-1">Due today</p>
    </div>
  </div>

  {{-- Filters --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <div class="flex flex-wrap items-center gap-3">
      <div class="flex rounded-lg border border-slate-200 overflow-hidden">
        <a href="{{ route('udhar.index', array_merge(request()->except('status','page'), ['status'=>'all'])) }}"
           class="px-4 py-2 text-sm font-medium transition-colors {{ request('status','all')==='all' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50' }}">All</a>
        <a href="{{ route('udhar.index', array_merge(request()->except('status','page'), ['status'=>'unpaid'])) }}"
           class="px-4 py-2 text-sm font-medium border-l border-slate-200 transition-colors {{ request('status')==='unpaid' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Unpaid</a>
        <a href="{{ route('udhar.index', array_merge(request()->except('status','page'), ['status'=>'overdue'])) }}"
           class="px-4 py-2 text-sm font-medium border-l border-slate-200 transition-colors {{ request('status')==='overdue' ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Overdue</a>
      </div>

      <form method="GET" class="flex gap-2 flex-1 min-w-[200px]">
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <div class="relative flex-1">
          <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
          <input type="text" name="search" value="{{ request('search') }}"
                 placeholder="Search customer or phone..."
                 class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
        </div>
        <button type="submit" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <i data-lucide="search" class="w-4 h-4"></i> Search
        </button>
        @if(request('search'))
        <a href="{{ route('udhar.index', request()->except('search','page')) }}" class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
          <i data-lucide="x" class="w-4 h-4"></i> Clear
        </a>
        @endif
      </form>

      <div class="ml-auto flex items-center gap-4 text-sm text-slate-500">
        <span class="flex items-center gap-1.5">
          <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
          <span class="font-semibold text-slate-700">{{ $records->total() }}</span> records
        </span>
      </div>
    </div>
  </div>

  {{-- Bulk WhatsApp reminder when viewing overdue --}}
  @if(request('status') === 'overdue' && $records->count() > 0)
  <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600 shrink-0" viewBox="0 0 24 24" fill="currentColor">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    <div class="flex-1">
      <p class="text-sm font-medium text-green-800">{{ $records->total() }} overdue customer(s) — send WhatsApp reminders</p>
      <p class="text-xs text-green-600 mt-0.5">Click each customer's View button, then use the WhatsApp button on their page.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      @foreach($records->filter(fn($r) => $r->phone) as $r)
      @php
        $waMsg = urlencode("Dear " . $r->customer_name . ", you have an outstanding balance of " . formatCurrency($r->amount_due) . " due by " . $r->due_date->format('d M Y') . ". Please arrange payment. — Anwar Chicken Center");
        $waPhone = preg_replace('/[^0-9]/', '', $r->phone);
        if (str_starts_with($waPhone, '0')) $waPhone = '92' . substr($waPhone, 1);
      @endphp
      <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank"
         class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-xs font-medium transition-colors">
        {{ $r->customer_name }}
      </a>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden table-responsive">
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
          $isOverdue   = in_array($record->status, ['unpaid','partial']) && $record->due_date->isPast();
          $isToday     = $record->due_date->isToday();
          $daysOverdue = $isOverdue ? $record->due_date->diffInDays(today()) : 0;

          if ($isOverdue && $daysOverdue >= 60) {
              $rowClass = 'bg-red-100';
          } elseif ($isOverdue && $daysOverdue >= 30) {
              $rowClass = 'bg-red-50';
          } elseif ($isOverdue && $daysOverdue >= 15) {
              $rowClass = 'bg-orange-50';
          } elseif ($isOverdue) {
              $rowClass = 'bg-yellow-50';
          } else {
              $rowClass = '';
          }
        @endphp
        <tr class="hover:bg-slate-50/80 transition-colors {{ $rowClass }}">
          <td class="px-4 py-3 text-sm font-medium">
            <a href="{{ route('udhar.show', $record) }}" class="text-slate-900 hover:text-green-600">{{ $record->customer_name }}</a>
            @if($record->description)<p class="text-xs text-slate-400 mt-0.5">{{ $record->description }}</p>@endif
            @if($isOverdue && $daysOverdue >= 60)
              <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-700 text-white mt-1">High Risk</span>
            @endif
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $record->phone ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($record->amount) }}</td>
          <td class="px-4 py-3 text-sm text-right text-green-600 font-medium">{{ formatCurrency($record->amount_paid) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold {{ $record->amount_due > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ formatCurrency($record->amount_due) }}</td>
          <td class="px-4 py-3 text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : ($isToday ? 'text-amber-600 font-semibold' : 'text-slate-600') }}">
            {{ $record->due_date->format('d M Y') }}
            @if($isOverdue)<p class="text-[10px] text-red-400 font-normal">{{ $daysOverdue }} days overdue</p>@endif
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
                    data-confirm-title="Delete Udhar Record?" data-confirm-message="Are you sure you want to delete this record?" data-confirm-text="Yes, Delete">
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
