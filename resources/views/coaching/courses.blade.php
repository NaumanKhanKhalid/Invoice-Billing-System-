@extends('layouts.app')
@section('title','Courses & Batches')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Courses & Batches</h1>
      <p class="text-sm text-slate-500 mt-0.5">Manage your courses and class batches</p>
    </div>
    <button onclick="document.getElementById('addCourseModal').classList.remove('hidden')"
            class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
      <i data-lucide="plus" class="w-4 h-4"></i>Add Course
    </button>
  </div>


  {{-- Courses --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-200 px-5 py-3">
      <h2 class="font-semibold text-slate-800 flex items-center gap-2">
        <i data-lucide="book-open" class="w-4 h-4 text-slate-400"></i>Courses
      </h2>
    </div>
    <div class="divide-y divide-slate-100">
      @forelse($courses as $course)
      <div class="px-5 py-4 flex items-center justify-between" x-data="{editing:false}">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-3">
            <p class="font-medium text-slate-900">{{ $course->name }}</p>
            @if(!$course->is_active)
            <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">Inactive</span>
            @endif
          </div>
          @if($course->description)
          <p class="text-xs text-slate-400 mt-0.5">{{ $course->description }}</p>
          @endif
          <p class="text-xs text-slate-500 mt-1">PKR {{ number_format($course->monthly_fee) }}/month · {{ $course->batches_count }} batch(es)</p>
        </div>
        <div class="flex items-center gap-2 ml-4">
          <button onclick="openEditCourse({{ $course->id }}, '{{ addslashes($course->name) }}', {{ $course->monthly_fee }}, '{{ addslashes($course->description ?? '') }}')"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
          </button>
          <form action="{{ route('coaching.courses.destroy', $course) }}" method="POST"
                onsubmit="return confirm('Delete course?')">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors">
              <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete
            </button>
          </form>
        </div>
      </div>
      @empty
      <div class="px-5 py-10 text-center text-slate-400 text-sm">
        <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
        No courses yet. Add your first course above.
      </div>
      @endforelse
    </div>
  </div>

  {{-- Batches --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-200 px-5 py-3 flex items-center justify-between">
      <h2 class="font-semibold text-slate-800 flex items-center gap-2">
        <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>Batches
      </h2>
      <button onclick="document.getElementById('addBatchModal').classList.remove('hidden')"
              class="text-xs text-blue-600 hover:underline flex items-center gap-1">
        <i data-lucide="plus" class="w-3 h-3"></i>Add Batch
      </button>
    </div>
    <div class="divide-y divide-slate-100">
      @forelse($batches as $batch)
      <div class="px-5 py-4 flex items-center justify-between">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-3">
            <p class="font-medium text-slate-900">{{ $batch->name }}</p>
            <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">{{ $batch->course->name }}</span>
            @if(!$batch->is_active)
            <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">Inactive</span>
            @endif
          </div>
          <div class="flex items-center gap-4 mt-1">
            @if($batch->timing)<p class="text-xs text-slate-400"><i data-lucide="clock" class="w-3 h-3 inline -mt-0.5"></i> {{ $batch->timing }}</p>@endif
            @if($batch->days)<p class="text-xs text-slate-400">{{ $batch->days }}</p>@endif
            @if($batch->teacher_name)<p class="text-xs text-slate-400"><i data-lucide="user" class="w-3 h-3 inline -mt-0.5"></i> {{ $batch->teacher_name }}</p>@endif
            <p class="text-xs text-slate-500 font-medium">{{ $batch->students_count }}/{{ $batch->capacity }} students</p>
          </div>
        </div>
        <div class="flex items-center gap-2 ml-4">
          <button onclick="openEditBatch({{ $batch->id }}, {{ $batch->course_id }}, '{{ addslashes($batch->name) }}', '{{ addslashes($batch->timing ?? '') }}', '{{ addslashes($batch->days ?? '') }}', '{{ addslashes($batch->teacher_name ?? '') }}', {{ $batch->capacity }})"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
          </button>
          <form action="{{ route('coaching.batches.destroy', $batch) }}" method="POST"
                onsubmit="return confirm('Delete batch?')">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors">
              <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete
            </button>
          </form>
        </div>
      </div>
      @empty
      <div class="px-5 py-10 text-center text-slate-400 text-sm">No batches yet.</div>
      @endforelse
    </div>
  </div>

</div>

{{-- Add Course Modal --}}
<div id="addCourseModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
      <h3 class="font-semibold text-slate-900">Add Course</h3>
      <button onclick="document.getElementById('addCourseModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form action="{{ route('coaching.courses.store') }}" method="POST" class="p-6 space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Course Name</label>
        <input name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Matriculation (Science)">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Fee (PKR)</label>
        <input name="monthly_fee" type="number" min="0" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="2500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
        <textarea name="description" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief course description"></textarea>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('addCourseModal').classList.add('hidden')"
                class="flex-1 border border-slate-300 text-slate-700 rounded-lg py-2 text-sm font-medium hover:bg-slate-50">Cancel</button>
        <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700">Add Course</button>
      </div>
    </form>
  </div>
</div>

{{-- Edit Course Modal --}}
<div id="editCourseModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
      <h3 class="font-semibold text-slate-900">Edit Course</h3>
      <button onclick="document.getElementById('editCourseModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="editCourseForm" method="POST" class="p-6 space-y-4">
      @csrf @method('PATCH')
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Course Name</label>
        <input id="editCourseName" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Fee (PKR)</label>
        <input id="editCourseFee" name="monthly_fee" type="number" min="0" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea id="editCourseDesc" name="description" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('editCourseModal').classList.add('hidden')"
                class="flex-1 border border-slate-300 text-slate-700 rounded-lg py-2 text-sm font-medium hover:bg-slate-50">Cancel</button>
        <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- Add Batch Modal --}}
<div id="addBatchModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
      <h3 class="font-semibold text-slate-900">Add Batch</h3>
      <button onclick="document.getElementById('addBatchModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form action="{{ route('coaching.batches.store') }}" method="POST" class="p-6 space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Course</label>
        <select name="course_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Select course...</option>
          @foreach($courses as $course)
          <option value="{{ $course->id }}">{{ $course->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Batch Name</label>
        <input name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Morning Batch A">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Timing</label>
          <input name="timing" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="8:00 AM – 10:00 AM">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Days</label>
          <input name="days" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Mon, Wed, Fri">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Teacher Name</label>
          <input name="teacher_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Capacity</label>
          <input name="capacity" type="number" min="1" max="200" value="20" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('addBatchModal').classList.add('hidden')"
                class="flex-1 border border-slate-300 text-slate-700 rounded-lg py-2 text-sm font-medium hover:bg-slate-50">Cancel</button>
        <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700">Add Batch</button>
      </div>
    </form>
  </div>
</div>

{{-- Edit Batch Modal --}}
<div id="editBatchModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
      <h3 class="font-semibold text-slate-900">Edit Batch</h3>
      <button onclick="document.getElementById('editBatchModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="editBatchForm" method="POST" class="p-6 space-y-4">
      @csrf @method('PATCH')
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Course</label>
        <select id="editBatchCourse" name="course_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          @foreach($courses as $course)
          <option value="{{ $course->id }}">{{ $course->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Batch Name</label>
        <input id="editBatchName" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Timing</label>
          <input id="editBatchTiming" name="timing" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Days</label>
          <input id="editBatchDays" name="days" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Teacher Name</label>
          <input id="editBatchTeacher" name="teacher_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Capacity</label>
          <input id="editBatchCapacity" name="capacity" type="number" min="1" max="200" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('editBatchModal').classList.add('hidden')"
                class="flex-1 border border-slate-300 text-slate-700 rounded-lg py-2 text-sm font-medium hover:bg-slate-50">Cancel</button>
        <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditCourse(id, name, fee, desc) {
  document.getElementById('editCourseForm').action = `/coaching/courses/${id}`;
  document.getElementById('editCourseName').value  = name;
  document.getElementById('editCourseFee').value   = fee;
  document.getElementById('editCourseDesc').value  = desc;
  document.getElementById('editCourseModal').classList.remove('hidden');
}
function openEditBatch(id, courseId, name, timing, days, teacher, capacity) {
  document.getElementById('editBatchForm').action     = `/coaching/batches/${id}`;
  document.getElementById('editBatchCourse').value    = courseId;
  document.getElementById('editBatchName').value      = name;
  document.getElementById('editBatchTiming').value    = timing;
  document.getElementById('editBatchDays').value      = days;
  document.getElementById('editBatchTeacher').value   = teacher;
  document.getElementById('editBatchCapacity').value  = capacity;
  document.getElementById('editBatchModal').classList.remove('hidden');
}
</script>
@endsection
