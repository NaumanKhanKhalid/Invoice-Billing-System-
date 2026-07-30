@extends('layouts.app')
@section('title', 'Edit Customer')
@section('content')
<div class="space-y-6" x-data="{
    type: '{{ old('type', $customer->type) }}',
    get creditDays() {
        return { hotel: 30, catering: 30, restaurant: 30, company: 30, reseller: 7 }[this.type] ?? 30;
    }
}">

  {{-- Header --}}
  <div class="flex items-center gap-3">
    <a href="{{ route('customers.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">{{ __('forms.edit_customer') }}</h1>
      <p class="text-sm text-slate-500">{{ $customer->name }}</p>
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

  <form method="POST" action="{{ route('customers.update', $customer) }}">
    @csrf @method('PUT')
    <div class="grid grid-cols-3 gap-6">

      {{-- Left column --}}
      <div class="col-span-2 space-y-5">

        {{-- Basic Info card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-slate-50">
            <span class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center">
              <i data-lucide="user" class="w-3.5 h-3.5 text-slate-600"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">{{ __('forms.basic_info') }}</p>
              <p class="text-xs text-slate-500">{{ __('forms.basic_info_sub') }}</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('common.name') }} span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('common.phone') }} span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('forms.whatsapp_number') }}</label>
              <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $customer->whatsapp_number) }}" placeholder="92313551819"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              <p class="text-xs text-slate-400 mt-1">{{ __('forms.with_country_code') }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('pages.address') }}</label>
              <textarea name="address" rows="2"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('address', $customer->address) }}</textarea>
            </div>
          </div>
        </div>

        {{-- Settings card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-blue-50">
            <span class="w-7 h-7 rounded-lg bg-blue-200 flex items-center justify-center">
              <i data-lucide="settings" class="w-3.5 h-3.5 text-blue-700"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">{{ __('forms.account_settings') }}</p>
              <p class="text-xs text-slate-500">{{ __('forms.account_settings_sub') }}</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('forms.customer_type') }} <span class="text-red-500">*</span></label>
                <select name="type" x-model="type" required
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
                  @foreach(['hotel','catering','restaurant','company','reseller'] as $t)
                    <option value="{{ $t }}" {{ old('type', $customer->type)==$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                  @endforeach
                </select>
                @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('forms.credit_days') }}</label>
                <input type="number" name="credit_days" value="{{ old('credit_days', $customer->credit_days) }}" min="0"
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">{{ __('forms.credit_limit') }}</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">PKR</span>
                <input type="number" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" min="0" step="1000"
                  class="w-full pl-14 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              </div>
              <p class="text-xs text-slate-400 mt-1">0 = no limit</p>
            </div>
            <div class="flex items-center gap-2 pt-1">
              <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                class="rounded border-slate-300 text-green-600 focus:ring-green-400">
              <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('forms.active_account') }}</label>
            </div>
          </div>
        </div>

      </div>

      {{-- Right column --}}
      <div class="space-y-4">

        {{-- Info card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-green-50">
            <span class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center">
              <i data-lucide="info" class="w-3.5 h-3.5 text-green-700"></i>
            </span>
            <p class="text-sm font-semibold text-slate-800">{{ __('forms.customer_info') }}</p>
          </div>
          <div class="p-5 space-y-3 text-xs text-slate-500">
            <div class="flex gap-2">
              <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
              <p>Customer since {{ $customer->created_at->format('d M Y') }}</p>
            </div>
            <div class="flex gap-2">
              <i data-lucide="tag" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
              <p>Type: <span class="font-medium text-slate-700">{{ ucfirst($customer->type) }}</span></p>
            </div>
            <div class="flex gap-2">
              <i data-lucide="check" class="w-3.5 h-3.5 text-green-500 shrink-0 mt-0.5"></i>
              <p>Credit days auto-fill based on customer type</p>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
          <i data-lucide="check-circle" class="w-4 h-4"></i> Update Customer
        </button>
        <a href="{{ route('customers.index') }}" class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center transition-colors">
          Cancel
        </a>

      </div>
    </div>
  </form>
</div>
@endsection
