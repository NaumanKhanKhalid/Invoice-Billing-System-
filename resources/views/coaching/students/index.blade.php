@extends('layouts.app')
@section('title','Students')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">{{ __('coaching.students') }}</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ $students->total() }} total students</p>
    </div>
    <a href="{{ route('coaching.students.create') }}"
       class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">
      <i data-lucide="user-plus" class="w-4 h-4"></i>Enroll Student
    </a>
  </div>


  {{-- Filters --}}
  <form method="GET" class="flex flex-wrap gap-3">
    <input name="search" value="{{ request('search') }}"
           placeholder="{{ __('pages.search_name_phone') }}"
           class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 w-56">
    <select name="batch_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      <option value="">{{ __('coaching.all_batches') }}</option>
      @foreach($batches as $batch)
      <option value="{{ $batch->id }}" @selected(request('batch_id')==$batch->id)>{{ $batch->course->name }} – {{ $batch->name }}</option>
      @endforeach
    </select>
    <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      <option value="">{{ __('common.all_status') }}</option>
      <option value="active" @selected(request('status')=='active')>{{ __('common.active') }}</option>
      <option value="completed" @selected(request('status')=='completed')>{{ __('coaching.completed') }}</option>
      <option value="dropped" @selected(request('status')=='dropped')>{{ __('coaching.dropped') }}</option>
    </select>
    <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">{{ __('common.filter') }}</button>
    @if(request()->hasAny(['search','batch_id','status']))
    <a href="{{ route('coaching.students.index') }}" class="text-sm text-slate-500 hover:underline self-center">{{ __('common.clear') }}</a>
    @endif
  </form>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">{{ __('common.name') }}</th>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">{{ __('coaching.batch_course') }}</th>
            <th class="text-left px-4 py-3 font-semibold text-slate-600">{{ __('common.phone') }}</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">{{ __('coaching.monthly_fee') }}</th>
            <th class="text-center px-4 py-3 font-semibold text-slate-600">{{ __('common.status') }}</th>
            <th class="text-right px-4 py-3 font-semibold text-slate-600">{{ __('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($students as $student)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
              <a href="{{ route('coaching.students.show', $student) }}" class="font-medium text-slate-900 hover:text-green-600">{{ $student->name }}</a>
              @if($student->guardian_name)
              <p class="text-xs text-slate-400">Guardian: {{ $student->guardian_name }}</p>
              @endif
            </td>
            <td class="px-4 py-3 text-slate-600">
              {{ $student->batch->name }}<br>
              <span class="text-xs text-slate-400">{{ $student->batch->course->name }}</span>
            </td>
            <td class="px-4 py-3 text-slate-600">{{ $student->phone ?? '—' }}</td>
            <td class="px-4 py-3 text-right font-medium text-slate-900">PKR {{ number_format($student->effectiveFee()) }}</td>
            <td class="px-4 py-3 text-center">
              @if($student->status === 'active')
              <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">{{ __('common.active') }}</span>
              @elseif($student->status === 'completed')
              <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ __('coaching.completed') }}</span>
              @else
              <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium">{{ __('coaching.dropped') }}</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <a href="{{ route('coaching.students.show', $student) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                  <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
                </a>
                <a href="{{ route('coaching.students.edit', $student) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                  <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
                </a>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
              <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
              No students found.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($students->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $students->links() }}</div>
    @endif
  </div>
</div>
@endsection
