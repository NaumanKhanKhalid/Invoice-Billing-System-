@extends('layouts.app')
@section('title','Google Drive Backups')
@section('content')
<div class="space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('settings.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Google Drive Backups</h1>
      <p class="text-sm text-slate-500 mt-0.5">Kisi bhi backup ko restore ya delete kar sakte ho</p>
    </div>
  </div>

  @if(session('success'))
  <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>{{ session('success') }}
  </div>
  @endif
  @if(session('error'))
  <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>{{ session('error') }}
  </div>
  @endif

  {{-- Stats + Action bar --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
        <i data-lucide="cloud" class="w-5 h-5 text-green-600"></i>
      </div>
      <div>
        <p class="font-semibold text-slate-900">{{ count($backups) }} Backup{{ count($backups) !== 1 ? 's' : '' }}</p>
        <p class="text-xs text-slate-400">Google Drive · Anwar Chicken Backups</p>
      </div>
    </div>
    <form method="POST" action="{{ route('backup.google') }}">
      @csrf
      <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="upload-cloud" class="w-4 h-4"></i>New Backup Lo
      </button>
    </form>
  </div>

  @if(count($backups) === 0)
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-6 py-16 text-center">
    <i data-lucide="cloud-off" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
    <p class="text-slate-500 font-medium">Abhi koi backup nahi hai</p>
    <p class="text-slate-400 text-sm mt-1">Upar "New Backup Lo" button se pehla backup banao</p>
  </div>
  @else
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($backups as $i => $backup)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-4 hover:shadow-md transition-shadow">
      {{-- Icon + Name --}}
      <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
          <i data-lucide="database" class="w-5 h-5 text-blue-500"></i>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-semibold text-slate-800">Backup #{{ count($backups) - $i }}</p>
          <p class="text-xs text-slate-400 mt-0.5">{{ $backup['size'] }} &middot; .{{ pathinfo($backup['name'], PATHINFO_EXTENSION) }}</p>
        </div>
        @if($i === 0)
        <span class="ml-auto shrink-0 text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Latest</span>
        @endif
      </div>

      {{-- Date --}}
      <div class="flex items-center gap-2 text-xs text-slate-500">
        <i data-lucide="calendar" class="w-3.5 h-3.5 shrink-0"></i>
        {{ $backup['created'] }}
      </div>

      {{-- Actions --}}
      <div class="flex gap-2 pt-1 border-t border-slate-100">
        <form method="POST" action="{{ route('backup.restore', $backup['id']) }}" class="flex-1"
              data-confirm-title="Restore Karna Chahte Ho?"
              data-confirm-message="Is backup se restore karne par current data replace ho jaye ga."
              data-confirm-text="Haan, Restore Karo"
              data-confirm-danger="true">
          @csrf
          <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition-colors">
            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>Restore
          </button>
        </form>
        <form method="POST" action="{{ route('backup.delete', $backup['id']) }}" class="flex-1"
              data-confirm-title="Backup Delete Karo?"
              data-confirm-message="Ye backup Google Drive se hamesha ke liye delete ho jaye ga."
              data-confirm-text="Haan, Delete Karo"
              data-confirm-danger="true">
          @csrf @method('DELETE')
          <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold transition-colors">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete
          </button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
    <strong>Note:</strong> Restore karne se pehle confirm kar lo — backup se current data replace ho jayega.
  </div>

</div>
@endsection

