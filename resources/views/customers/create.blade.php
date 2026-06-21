@extends('layouts.app')
@section('title','Add Customer')
@section('content')
<div class="max-w-2xl mx-auto space-y-5"
  x-data="{
    type: '{{ old('type','hotel') }}',
    get creditDays() {
      return { hotel: 30, catering: 30, restaurant: 30, company: 30, reseller: 7 }[this.type] ?? 30;
    }
  }">
  <div class="flex items-center gap-3">
    <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-slate-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <h1 class="text-2xl font-bold text-slate-900">Add Customer</h1>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('customers.store') }}" class="space-y-5">
      @csrf

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required
            class="w-full px-3 py-2 text-sm border @error('name') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone <span class="text-red-500">*</span></label>
          <input type="text" name="phone" value="{{ old('phone') }}" required
            class="w-full px-3 py-2 text-sm border @error('phone') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp Number</label>
        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Type <span class="text-red-500">*</span></label>
          <select name="type" x-model="type" required
            class="w-full px-3 py-2 text-sm border @error('type') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="">Select type...</option>
            @foreach(['hotel','catering','restaurant','company','reseller'] as $t)
              <option value="{{ $t }}" {{ old('type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
            @endforeach
          </select>
          @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Credit Days</label>
          <input type="number" name="credit_days" :value="creditDays" min="0"
            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          <p class="text-xs text-slate-400 mt-1">Auto-set based on type</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Credit Limit (PKR)</label>
        <input type="number" name="credit_limit" value="{{ old('credit_limit',0) }}" min="0" step="100"
          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        <p class="text-xs text-slate-400 mt-1">0 = no limit</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
        <textarea name="address" rows="2"
          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('address') }}</textarea>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1" checked
          class="rounded border-slate-300 text-green-600 focus:ring-green-400">
        <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
          Save Customer
        </button>
        <a href="{{ route('customers.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
