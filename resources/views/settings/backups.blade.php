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
      <p class="text-sm text-slate-500 mt-0.5">Kisi bhi backup ko restore kar sakte ho</p>
    </div>
  </div>

  @if(session('success'))
  <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>{{ session('success') }}
  </div>
  @endif

  <div class="flex justify-between items-center">
    <p class="text-sm text-slate-500">{{ count($backups) }} backup{{ count($backups) !== 1 ? 's' : '' }} mili hain Google Drive mein</p>
    <form method="POST" action="{{ route('backup.google') }}">
      @csrf
      <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="upload-cloud" class="w-4 h-4"></i>New Backup Lo
      </button>
    </form>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if(count($backups) === 0)
    <div class="px-6 py-16 text-center">
      <i data-lucide="cloud-off" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-500 font-medium">Abhi koi backup nahi hai</p>
      <p class="text-slate-400 text-sm mt-1">Pehle "New Backup Lo" button se backup banao</p>
    </div>
    @else
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">File Name</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date & Time</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Size</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($backups as $backup)
        <tr class="hover:bg-slate-50">
          <td class="px-5 py-4 text-sm font-medium text-slate-800">
            <div class="flex items-center gap-2">
              <i data-lucide="database" class="w-4 h-4 text-green-600 shrink-0"></i>
              {{ $backup['name'] }}
            </div>
          </td>
          <td class="px-5 py-4 text-sm text-slate-600">{{ $backup['created'] }}</td>
          <td class="px-5 py-4 text-sm text-slate-500">{{ $backup['size'] }}</td>
          <td class="px-5 py-4 text-right">
            <form method="POST" action="{{ route('backup.restore', $backup['id']) }}"
                  data-confirm-title="Restore Karna Chahte Ho?"
                  data-confirm-message="Is backup se restore karne par current data replace ho jaye ga. Kya aap sure hain?"
                  data-confirm-text="Haan, Restore Karo"
                  data-confirm-danger="true">
              @csrf
              <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition-colors">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>Restore
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
    <strong>Note:</strong> Restore karne se pehle current data ka ek copy automatically save hota hai (<code>database.sqlite.before_restore</code>).
  </div>

</div>
@endsection
