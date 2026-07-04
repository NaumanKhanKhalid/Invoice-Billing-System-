@extends('layouts.app')
@section('title','Error Logs')
@section('content')
<div class="space-y-5">

  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Error Logs</h1>
      <p class="text-sm text-slate-500 mt-0.5">Latest 100 entries — errors highlighted</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
      <select name="file" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @foreach($files as $f)
        <option value="{{ $f }}" @selected($f === $current)>{{ $f }}</option>
        @endforeach
      </select>
      <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-900">Load</button>
    </form>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @forelse($entries as $e)
    <details class="border-b border-slate-100 group">
      <summary class="px-5 py-3 cursor-pointer flex items-start gap-3 hover:bg-slate-50 list-none">
        @php
          $color = in_array($e['level'], ['ERROR','CRITICAL','ALERT','EMERGENCY']) ? 'red'
                 : ($e['level'] === 'WARNING' ? 'yellow' : 'slate');
        @endphp
        <span class="text-xs bg-{{ $color }}-100 text-{{ $color }}-700 px-2 py-0.5 rounded-full font-bold flex-shrink-0 mt-0.5">{{ $e['level'] }}</span>
        <span class="flex-1 min-w-0">
          <span class="block text-sm text-slate-800 truncate">{{ \Illuminate\Support\Str::limit(strtok($e['message'], "\n"), 160) }}</span>
          <span class="block text-xs text-slate-400 mt-0.5">{{ $e['time'] }}</span>
        </span>
        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-300 flex-shrink-0 mt-1 group-open:rotate-180 transition-transform"></i>
      </summary>
      <pre class="px-5 py-3 bg-slate-900 text-slate-200 text-xs overflow-x-auto whitespace-pre-wrap max-h-96 overflow-y-auto">{{ $e['message'] }}</pre>
    </details>
    @empty
    <div class="px-5 py-12 text-center text-slate-400">
      <i data-lucide="check-circle" class="w-8 h-8 text-green-400 mx-auto mb-2"></i>
      No log entries — sab theek chal raha hai!
    </div>
    @endforelse
  </div>
</div>
@endsection
