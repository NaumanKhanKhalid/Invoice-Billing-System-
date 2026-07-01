@extends('layouts.app')
@section('title','Edit Tenant')
@section('content')
<div class="space-y-6 max-w-2xl">
  <div class="flex items-center gap-3">
    <a href="{{ route('admin.tenants.show', $tenant) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <h1 class="text-xl font-bold text-slate-900">Edit — {{ $tenant->shop_name }}</h1>
  </div>

  <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="space-y-5">
    @csrf @method('PUT')

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Shop Name</label>
          <input type="text" name="shop_name" value="{{ old('shop_name', $tenant->shop_name) }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Shop Type</label>
          <select name="shop_type" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            @foreach(['chicken'=>'🍗 Chicken','bike'=>'🏍️ Bike Parts','hardware'=>'🔧 Hardware','mobile'=>'📱 Mobile','general'=>'🏪 General','medical'=>'💊 Medical Store','coaching'=>'🎓 Coaching Center'] as $val => $label)
            <option value="{{ $val }}" {{ ($tenant->shop_type == $val)?'selected':'' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Owner Name</label>
          <input type="text" name="owner_name" value="{{ old('owner_name', $tenant->owner_name) }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Owner Phone</label>
          <input type="text" name="owner_phone" value="{{ old('owner_phone', $tenant->owner_phone) }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Plan</label>
          <select name="plan" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            @foreach(['basic','pro','business'] as $p)
            <option value="{{ $p }}" {{ $tenant->plan==$p?'selected':'' }}>{{ ucfirst($p) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Plan Expires At</label>
          <input type="date" name="plan_expires_at" value="{{ old('plan_expires_at', $tenant->plan_expires_at?->format('Y-m-d')) }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>

      <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" {{ $tenant->is_active?'checked':'' }} class="rounded">
          <span class="text-sm font-medium text-slate-700">Active (tenant can login)</span>
        </label>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('notes', $tenant->notes) }}</textarea>
      </div>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium">Save Changes</button>
      <a href="{{ route('admin.tenants.show', $tenant) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2.5 rounded-lg text-sm font-medium">Cancel</a>
    </div>
  </form>
</div>
@endsection
