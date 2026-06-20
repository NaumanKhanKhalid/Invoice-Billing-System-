@extends('layouts.app')
@section('title','Edit Supplier')
@section('content')
<div class="max-w-2xl mx-auto space-y-5">
  <div class="flex items-center gap-3">
    <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <h1 class="text-2xl font-bold text-slate-900">Edit Supplier</h1>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('suppliers.update',$supplier) }}" class="space-y-5">
      @csrf @method('PUT')
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="{{ old('name',$supplier->name) }}" required
            class="w-full px-3 py-2 text-sm border @error('name') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone <span class="text-red-500">*</span></label>
          <input type="text" name="phone" value="{{ old('phone',$supplier->phone) }}" required
            class="w-full px-3 py-2 text-sm border @error('phone') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Credit Days</label>
        <input type="number" name="credit_days" value="{{ old('credit_days',$supplier->credit_days) }}" min="1"
          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        <p class="text-xs text-slate-400 mt-1">Default: 15 days</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
        <textarea name="address" rows="2"
          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('address',$supplier->address) }}</textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2"
          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('notes',$supplier->notes) }}</textarea>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active',$supplier->is_active)?'checked':'' }}
          class="rounded border-slate-300 text-green-600 focus:ring-green-400">
        <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
          Update Supplier
        </button>
        <a href="{{ route('suppliers.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
