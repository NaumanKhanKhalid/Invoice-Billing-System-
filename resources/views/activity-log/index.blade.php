@extends('layouts.app')
@section('title','Activity Log')
@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">Activity Log</h1><p class="text-sm text-slate-500 mt-0.5">Audit trail of all record changes</p></div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
      <div class="relative">
        <i data-lucide="activity" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <select name="action_filter" class="pl-9 pr-8 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white text-slate-700 appearance-none cursor-pointer">
          <option value="">All Actions</option>
          @foreach(['created','updated','deleted'] as $a)<option value="{{ $a }}" {{ request('action_filter')==$a?'selected':'' }}>{{ ucfirst($a) }}</option>@endforeach
        </select>
        <i data-lucide="chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      </div>
      <div class="relative">
        <i data-lucide="database" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <select name="model_type" class="pl-9 pr-8 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white text-slate-700 appearance-none cursor-pointer">
          <option value="">All Records</option>
          @foreach($modelTypes as $type)<option value="{{ $type }}" {{ request('model_type')==$type?'selected':'' }}>{{ $type }}</option>@endforeach
        </select>
        <i data-lucide="chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      </div>
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="date" name="date" value="{{ request('date') }}"
               class="pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none text-slate-700">
      </div>
      <button type="submit" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="search" class="w-4 h-4"></i> Filter
      </button>
      @if(request()->hasAny(['action_filter','model_type','date']))
      <a href="{{ route('activity-log.index') }}" class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
        <i data-lucide="x" class="w-4 h-4"></i> Clear
      </a>
      @endif
      <div class="ml-auto flex items-center gap-1.5 text-sm text-slate-500">
        <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
        <span class="font-semibold text-slate-700">{{ $logs->total() }}</span> entries
      </div>
    </form>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden table-responsive">
    <table class="w-full">
      <thead class="bg-slate-50"><tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">When</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">User</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Action</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Changes</th>
      </tr></thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($logs as $log)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">
            {{ $log->created_at->format('d M Y') }}
            <span class="text-xs text-slate-400 block">{{ $log->created_at->format('h:i A') }}</span>
          </td>
          <td class="px-4 py-3 text-sm text-slate-700">{{ $log->user_name ?? 'System' }}</td>
          <td class="px-4 py-3">
            @php $badge = ['created' => 'bg-green-100 text-green-700', 'updated' => 'bg-blue-100 text-blue-700', 'deleted' => 'bg-red-100 text-red-700'][$log->action] ?? 'bg-slate-100 text-slate-600'; @endphp
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">{{ ucfirst($log->action) }}</span>
          </td>
          <td class="px-4 py-3 text-sm text-slate-700">{{ $log->description }}</td>
          <td class="px-4 py-3 text-xs text-slate-500">
            @if($log->changes)
              <div class="space-y-0.5">
                @foreach($log->changes as $field => $change)
                @php
                  $fmt = fn ($v) => is_scalar($v) ? (string) $v : (is_null($v) ? '-' : json_encode($v));
                  $old = is_array($change) && array_key_exists('old', $change) ? $fmt($change['old']) : null;
                  $new = is_array($change) && array_key_exists('new', $change) ? $fmt($change['new']) : $fmt($change);
                @endphp
                <div><span class="font-medium text-slate-600">{{ $field }}:</span> @if(!is_null($old)){{ $old }} <span class="text-slate-400">&rarr;</span> @endif{{ $new }}</div>
                @endforeach
              </div>
            @else
              <span class="text-slate-400">-</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="px-4 py-12 text-center">
          <i data-lucide="history" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
          <p class="text-slate-500 font-medium">No activity recorded yet</p>
          <p class="text-slate-400 text-sm mt-1">Changes to records will appear here</p>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($logs->hasPages())<div class="px-4 py-3 border-t border-slate-100">{{ $logs->links() }}</div>@endif
  </div>
</div>
@endsection
