@extends('layouts.app')
@section('title','Add Supplier')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center gap-3">
    <a href="{{ route('suppliers.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">{{ __('forms.add_supplier') }}</h1>
      <p class="text-sm text-slate-500">{{ __('forms.register_supplier') }}</p>
    </div>
  </div>

  @if($errors->any())
  <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
    <ul class="text-sm text-red-700 space-y-0.5">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('suppliers.store') }}">
    @csrf
    <div class="grid grid-cols-3 gap-6">

      {{-- Left column --}}
      <div class="col-span-2 space-y-5">

        {{-- Basic Info card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-slate-50">
            <span class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center">
              <i data-lucide="truck" class="w-3.5 h-3.5 text-slate-600"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">{{ __('forms.supplier_info') }}</p>
              <p class="text-xs text-slate-500">{{ __('forms.name_phone_address') }}</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                  class="w-full px-3 py-2 text-sm border @error('name') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Phone <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                  class="w-full px-3 py-2 text-sm border @error('phone') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('pages.address') }}</label>
              <textarea name="address" rows="2"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('address') }}</textarea>
            </div>
          </div>
        </div>

        {{-- Credit & Settings card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-blue-50">
            <span class="w-7 h-7 rounded-lg bg-blue-200 flex items-center justify-center">
              <i data-lucide="settings" class="w-3.5 h-3.5 text-blue-700"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">{{ __('forms.credit_settings') }}</p>
              <p class="text-xs text-slate-500">{{ __('forms.payment_terms_status') }}</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('forms.credit_days') }}</label>
              <input type="number" name="credit_days" value="{{ old('credit_days',15) }}" min="1"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              <p class="text-xs text-slate-400 mt-1">{{ __('forms.default_15') }}</p>
            </div>
            <div class="flex items-center gap-2 pt-1">
              <input type="checkbox" name="is_active" id="is_active" value="1" checked
                class="rounded border-slate-300 text-green-600 focus:ring-green-400">
              <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('forms.active_supplier') }}</label>
            </div>
          </div>
        </div>

      </div>

      {{-- Right column --}}
      <div class="space-y-4">

        {{-- Notes card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-yellow-50">
            <span class="w-7 h-7 rounded-lg bg-yellow-200 flex items-center justify-center">
              <i data-lucide="sticky-note" class="w-3.5 h-3.5 text-yellow-700"></i>
            </span>
            <p class="text-sm font-semibold text-slate-800">{{ __('forms.notes') }}</p>
          </div>
          <div class="p-5">
            <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('forms.internal_notes') }}</label>
            <textarea name="notes" rows="4"
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('notes') }}</textarea>
          </div>
        </div>

        {{-- Actions --}}
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
          <i data-lucide="check-circle" class="w-4 h-4"></i> Save Supplier
        </button>
        <a href="{{ route('suppliers.index') }}" class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center transition-colors">
          Cancel
        </a>

      </div>
    </div>
  </form>
</div>
@endsection
