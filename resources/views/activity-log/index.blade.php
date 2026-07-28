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

  {{-- Timeline --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
    @forelse($logs as $log)
    @php
      $cfg = [
        'created' => ['dot'=>'bg-green-500', 'chip'=>'bg-green-100 text-green-700', 'ring'=>'ring-green-100', 'icon'=>'plus'],
        'updated' => ['dot'=>'bg-blue-500',  'chip'=>'bg-blue-100 text-blue-700',  'ring'=>'ring-blue-100',  'icon'=>'pencil'],
        'deleted' => ['dot'=>'bg-red-500',   'chip'=>'bg-red-100 text-red-700',    'ring'=>'ring-red-100',   'icon'=>'trash-2'],
      ][$log->action] ?? ['dot'=>'bg-slate-400','chip'=>'bg-slate-100 text-slate-600','ring'=>'ring-slate-100','icon'=>'activity'];
      $fmt = fn ($v) => is_scalar($v) ? (string) $v : (is_null($v) ? '—' : json_encode($v));
    @endphp
    <div class="relative flex gap-4 px-5 py-4 {{ !$loop->last ? 'border-b border-slate-100' : '' }} hover:bg-slate-50/60 transition-colors">
      {{-- Timeline rail --}}
      <div class="relative flex flex-col items-center shrink-0">
        <span class="w-9 h-9 rounded-full {{ $cfg['dot'] }} ring-4 {{ $cfg['ring'] }} flex items-center justify-center text-white shadow-sm">
          <i data-lucide="{{ $cfg['icon'] }}" class="w-4 h-4"></i>
        </span>
        @if(!$loop->last)<span class="w-px flex-1 bg-slate-200 mt-1"></span>@endif
      </div>

      {{-- Content --}}
      <div class="flex-1 min-w-0 pb-0.5">
        <div class="flex items-start justify-between gap-3 flex-wrap">
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $cfg['chip'] }}">{{ ucfirst($log->action) }}</span>
              <p class="text-sm font-semibold text-slate-800">{{ $log->description }}</p>
            </div>
            <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
              <span class="inline-flex items-center gap-1"><i data-lucide="user" class="w-3.5 h-3.5"></i>{{ $log->user_name ?? 'System' }}</span>
            </div>
          </div>
          <div class="text-right shrink-0">
            <p class="text-xs font-semibold text-slate-600 whitespace-nowrap">{{ $log->created_at->format('d M Y') }}</p>
            <p class="text-[11px] text-slate-400 whitespace-nowrap">{{ $log->created_at->format('h:i A') }}</p>
          </div>
        </div>

        {{-- Changes: old vs new --}}
        @if($log->changes && count($log->changes))
        <div class="mt-2.5 rounded-lg border border-slate-100 bg-slate-50/70 divide-y divide-slate-100 overflow-hidden">
          @foreach($log->changes as $field => $change)
          @php
            $old = is_array($change) && array_key_exists('old', $change) ? $fmt($change['old']) : null;
            $new = is_array($change) && array_key_exists('new', $change) ? $fmt($change['new']) : $fmt($change);
          @endphp
          <div class="flex items-center gap-2 px-3 py-1.5 text-xs">
            <span class="font-semibold text-slate-500 capitalize w-32 shrink-0 truncate">{{ str_replace('_',' ',$field) }}</span>
            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
              @if(!is_null($old) && $old !== '—' && $old !== '')
                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-red-50 text-red-500 line-through font-mono">{{ $old }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-300 shrink-0"></i>
              @endif
              <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-green-50 text-green-700 font-mono font-semibold">{{ $new }}</span>
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>
    @empty
    <div class="px-4 py-16 text-center">
      <i data-lucide="history" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-500 font-medium">No activity recorded yet</p>
      <p class="text-slate-400 text-sm mt-1">Changes to records will appear here</p>
    </div>
    @endforelse
    @if($logs->hasPages())<div class="px-5 py-4 border-t border-slate-100">{{ $logs->links() }}</div>@endif
  </div>
</div>
@endsection
