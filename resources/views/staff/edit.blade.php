@extends('layouts.app')
@section('title','Edit Staff')
@section('content')
<div class="space-y-5">
  <div class="flex items-center gap-3">
    <a href="{{ route('staff.show',$staff) }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div><h1 class="text-2xl font-bold text-slate-900">Edit Staff</h1><p class="text-sm text-slate-500">{{ $staff->name }}</p></div>
  </div>

  <form method="POST" action="{{ route('staff.update',$staff) }}" class="space-y-5">
    @csrf @method('PUT')
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="{{ old('name',$staff->name) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
          <input type="text" name="phone" value="{{ old('phone',$staff->phone) }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Role <span class="text-red-500">*</span></label>
          <select name="role" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            @foreach(['manager','butcher','delivery','cleaner','cashier','other'] as $r)
            <option value="{{ $r }}" {{ old('role',$staff->role)===$r?'selected':'' }}>{{ ucfirst($r) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Salary (PKR) <span class="text-red-500">*</span></label>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
          <input type="number" name="salary" value="{{ old('salary',$staff->salary) }}" step="1" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"></div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Joining Date <span class="text-red-500">*</span></label>
          <input type="date" name="joining_date" value="{{ old('joining_date',$staff->joining_date->format('Y-m-d')) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Update</button>
      <a href="{{ route('staff.show',$staff) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
    </div>
  </form>
</div>
@endsection
