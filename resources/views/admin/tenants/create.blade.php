@extends('layouts.app')
@section('title','Add Tenant')
@section('content')
<div class="space-y-6 max-w-2xl">
  <div class="flex items-center gap-3">
    <a href="{{ route('admin.tenants.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">New Tenant</h1>
      <p class="text-sm text-slate-500">Create a new client shop</p>
    </div>
  </div>

  <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-5" x-data="{
    shopName: '{{ old('shop_name') }}',
    subdomain: '{{ old('subdomain') }}',
    userEditedSub: {{ old('subdomain') ? 'true' : 'false' }},
    autoSlug() {
      if (!this.userEditedSub) {
        this.subdomain = this.shopName
          .toLowerCase()
          .replace(/[^a-z0-9\s-]/g, '')
          .trim()
          .replace(/\s+/g, '-')
          .substring(0, 50);
      }
    }
  }">
    @csrf
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
      <h2 class="font-semibold text-slate-900 text-sm uppercase tracking-wider text-slate-500">Shop Details</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Shop Name *</label>
          <input type="text" name="shop_name" x-model="shopName" @input="autoSlug()" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          @error('shop_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Shop Type *</label>
          <select name="shop_type" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            <option value="">Select type...</option>
            <option value="chicken" {{ old('shop_type')=='chicken'?'selected':'' }}>🍗 Chicken Shop</option>
            <option value="bike"    {{ old('shop_type')=='bike'   ?'selected':'' }}>🏍️ Bike Spare Parts</option>
            <option value="hardware"{{ old('shop_type')=='hardware'?'selected':'' }}>🔧 Hardware Shop</option>
            <option value="mobile"  {{ old('shop_type')=='mobile' ?'selected':'' }}>📱 Mobile Shop</option>
            <option value="general" {{ old('shop_type')=='general'?'selected':'' }}>🏪 General</option>
            <option value="medical" {{ old('shop_type')=='medical'?'selected':'' }}>💊 Medical Store</option>
            <option value="coaching" {{ old('shop_type')=='coaching'?'selected':'' }}>🎓 Coaching Center</option>
          </select>
          @error('shop_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Subdomain *</label>
        <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-green-300">
          <input type="text" name="subdomain" x-model="subdomain" @input="userEditedSub=true" required placeholder="ahmed-bikes"
                 class="flex-1 px-3 py-2 text-sm outline-none">
          <span class="px-3 py-2 bg-slate-50 text-slate-400 text-sm border-l border-slate-200">.{{ config('app.central_domain','yourapp.com') }}</span>
        </div>
        @error('subdomain')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
      <h2 class="font-semibold text-slate-900 text-sm uppercase tracking-wider text-slate-500">Owner Details</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Owner Name *</label>
          <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          @error('owner_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Owner Phone</label>
          <input type="text" name="owner_phone" value="{{ old('owner_phone') }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1">Owner Email *</label>
          <input type="email" name="owner_email" value="{{ old('owner_email') }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          <p class="text-xs text-slate-400 mt-1">This will be the login email. Temp password: <strong>password123</strong></p>
          @error('owner_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
      <h2 class="font-semibold text-slate-900 text-sm uppercase tracking-wider text-slate-500">Subscription</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Plan *</label>
          <select name="plan" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            <option value="basic"    {{ old('plan')=='basic'   ?'selected':'' }}>Basic — PKR 1,500/mo</option>
            <option value="pro"      {{ old('plan')=='pro'     ?'selected':'' }}>Pro — PKR 3,000/mo</option>
            <option value="business" {{ old('plan')=='business'?'selected':'' }}>Business — PKR 5,000/mo</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Duration (months) *</label>
          <select name="plan_months" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            <option value="1">1 month</option>
            <option value="3">3 months</option>
            <option value="6">6 months</option>
            <option value="12" selected>12 months</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('notes') }}</textarea>
      </div>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
        Create Tenant & Database
      </button>
      <a href="{{ route('admin.tenants.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2.5 rounded-lg text-sm font-medium">Cancel</a>
    </div>
  </form>
</div>
@endsection
