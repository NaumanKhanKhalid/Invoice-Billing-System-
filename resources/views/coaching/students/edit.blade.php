@extends('layouts.app')
@section('title','Edit Student')
@section('content')
<div class="space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('coaching.students.show', $student) }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Edit Student</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ $student->name }}</p>
    </div>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form action="{{ route('coaching.students.update', $student) }}" method="POST" class="bg-white rounded-xl border border-slate-200 shadow-sm divide-y divide-slate-100">
    @csrf @method('PATCH')

    <div class="px-6 py-5 space-y-4">
      <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Enrollment Info</h2>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Batch <span class="text-red-500">*</span></label>
        <select name="batch_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          @foreach($batches as $batch)
          <option value="{{ $batch->id }}" @selected(old('batch_id',$student->batch_id)==$batch->id)>
            {{ $batch->course->name }} — {{ $batch->name }}
            @if($batch->timing) ({{ $batch->timing }})@endif
          </option>
          @endforeach
        </select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Enrollment Date <span class="text-red-500">*</span></label>
          <input name="enrollment_date" type="date" value="{{ old('enrollment_date', $student->enrollment_date) }}" required
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
          <select name="status" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="active" @selected(old('status',$student->status)==='active')>Active</option>
            <option value="completed" @selected(old('status',$student->status)==='completed')>Completed</option>
            <option value="dropped" @selected(old('status',$student->status)==='dropped')>Dropped</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Custom Fee (PKR)</label>
          <input name="custom_fee" type="number" min="0" value="{{ old('custom_fee', $student->custom_fee) }}"
                 placeholder="Leave blank to use course fee"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Permanent Discount (%)</label>
          <input name="discount_percent" type="number" min="0" max="100" value="{{ old('discount_percent', $student->discount_percent) }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-slate-400 mt-1">Har mahine apne aap lagega. Ek dafa ki chhoot fee collect karte waqt dena.</p>
        </div>
      </div>
    </div>

    <div class="px-6 py-5 space-y-4">
      <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Student Details</h2>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Full Name <span class="text-red-500">*</span></label>
        <input name="name" value="{{ old('name', $student->name) }}" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
          <input name="phone" value="{{ old('phone', $student->phone) }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
          <input name="address" value="{{ old('address', $student->address) }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
    </div>

    <div class="px-6 py-5 space-y-4">
      <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">Guardian Info</h2>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Name</label>
          <input name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Phone</label>
          <input name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $student->notes) }}</textarea>
      </div>
    </div>

    <div class="px-6 py-4 flex gap-3">
      <a href="{{ route('coaching.students.show', $student) }}"
         class="flex-1 text-center border border-slate-300 text-slate-700 rounded-lg py-2 text-sm font-medium hover:bg-slate-50">Cancel</a>
      <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700">Save Changes</button>
    </div>
  </form>
</div>
@endsection
