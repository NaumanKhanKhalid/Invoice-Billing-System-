@extends('layouts.app')
@section('title','Enroll Student')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('coaching.students.index') }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Enroll Student</h1>
      <p class="text-sm text-slate-500 mt-0.5">Add a new student to a batch</p>
    </div>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form action="{{ route('coaching.students.store') }}" method="POST" class="bg-white rounded-xl border border-slate-200 shadow-sm divide-y divide-slate-100">
    @csrf

    <div class="px-6 py-5 space-y-4">
      <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Enrollment Info</h2>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Batch <span class="text-red-500">*</span></label>
        <select name="batch_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Select batch...</option>
          @foreach($batches as $batch)
          <option value="{{ $batch->id }}" @selected(old('batch_id')==$batch->id)>
            {{ $batch->course->name }} — {{ $batch->name }}
            @if($batch->timing) ({{ $batch->timing }})@endif
          </option>
          @endforeach
        </select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Enrollment Date <span class="text-red-500">*</span></label>
          <input name="enrollment_date" type="date" value="{{ old('enrollment_date', today()->toDateString()) }}" required
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Custom Fee (PKR)</label>
          <input name="custom_fee" type="number" min="0" value="{{ old('custom_fee') }}"
                 placeholder="Leave blank to use course fee"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Discount (%)</label>
        <input name="discount_percent" type="number" min="0" max="100" value="{{ old('discount_percent', 0) }}"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>

    <div class="px-6 py-5 space-y-4">
      <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Student Details</h2>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Full Name <span class="text-red-500">*</span></label>
        <input name="name" value="{{ old('name') }}" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Student full name">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
          <input name="phone" value="{{ old('phone') }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="03XX-XXXXXXX">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
          <input name="address" value="{{ old('address') }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional">
        </div>
      </div>
    </div>

    <div class="px-6 py-5 space-y-4">
      <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Guardian Info</h2>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Name</label>
          <input name="guardian_name" value="{{ old('guardian_name') }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Father/Mother name">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Phone</label>
          <input name="guardian_phone" value="{{ old('guardian_phone') }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="03XX-XXXXXXX">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Any additional notes">{{ old('notes') }}</textarea>
      </div>
    </div>

    <div class="px-6 py-4 flex gap-3">
      <a href="{{ route('coaching.students.index') }}"
         class="flex-1 text-center border border-slate-300 text-slate-700 rounded-lg py-2 text-sm font-medium hover:bg-slate-50">Cancel</a>
      <button type="submit" class="flex-1 bg-green-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-green-700">Enroll Student</button>
    </div>
  </form>
</div>
@endsection
