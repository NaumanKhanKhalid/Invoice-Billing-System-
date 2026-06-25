@extends('layouts.app')
@section('title',$tenant->shop_name)
@section('content')
<div class="space-y-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('admin.tenants.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
      <h1 class="text-xl font-bold text-slate-900">{{ $tenant->shop_name }}</h1>
      <p class="text-sm text-slate-500">{{ $tenant->id }} · {{ ucfirst($tenant->shop_type) }} Shop</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('admin.tenants.edit', $tenant) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="pencil" class="w-4 h-4"></i>Edit
      </a>
      <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}"
            data-confirm-title="Delete Tenant?" data-confirm-message="This will permanently delete {{ $tenant->shop_name }} and ALL their data." data-confirm-text="Yes, Delete Forever" data-confirm-danger="true">
        @csrf @method('DELETE')
        <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
          <i data-lucide="trash-2" class="w-4 h-4"></i>Delete
        </button>
      </form>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    {{-- Plan Status --}}
    @php
      $expired = $tenant->plan_expires_at && $tenant->plan_expires_at < now();
      $daysLeft = $tenant->plan_expires_at ? now()->diffInDays($tenant->plan_expires_at, false) : null;
    @endphp
    <div class="bg-white rounded-xl border border-{{ $expired ? 'red' : 'green' }}-200 shadow-sm p-5">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Plan</p>
      <p class="text-xl font-bold text-slate-900 capitalize">{{ $tenant->plan }}</p>
      <p class="text-sm {{ $expired ? 'text-red-600' : 'text-slate-500' }} mt-1">
        @if($tenant->plan_expires_at)
          {{ $expired ? 'Expired' : 'Expires' }} {{ $tenant->plan_expires_at->format('d M Y') }}
          @if(!$expired)<span class="text-xs">({{ $daysLeft }} days left)</span>@endif
        @else Never @endif
      </p>
    </div>

    {{-- Owner --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Owner</p>
      <p class="text-base font-bold text-slate-900">{{ $tenant->owner_name }}</p>
      <p class="text-sm text-slate-500">{{ $tenant->owner_email }}</p>
      @if($tenant->owner_phone)<p class="text-sm text-slate-500">{{ $tenant->owner_phone }}</p>@endif
    </div>

    {{-- Domain --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Domain</p>
      @foreach($tenant->domains as $domain)
      <a href="http://{{ $domain->domain }}" target="_blank" class="text-green-600 hover:underline text-sm font-medium">{{ $domain->domain }}</a><br>
      @endforeach
    </div>
  </div>

  {{-- Renew Plan --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-slate-900">Record Payment & Renew Plan</h2>
      <a href="{{ route('admin.tenants.payments', $tenant) }}" class="text-sm text-green-600 hover:underline flex items-center gap-1">
        <i data-lucide="history" class="w-4 h-4"></i> Payment History
      </a>
    </div>
    @if($expired)<div class="mb-3 text-sm text-red-600 font-medium bg-red-50 border border-red-200 rounded-lg px-3 py-2">⚠️ Plan expired — recording a payment will reactivate access</div>@endif
    <form method="POST" action="{{ route('admin.tenants.renew', $tenant) }}">
      @csrf
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Amount (PKR) *</label>
          <input type="number" name="amount" required placeholder="{{ config('plans.'.$tenant->plan.'.price', 0) }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Months *</label>
          <select name="months" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            <option value="1">1 month</option>
            <option value="3">3 months</option>
            <option value="6">6 months</option>
            <option value="12" selected>12 months</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Payment Method *</label>
          <select name="method" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            <option value="cash">Cash</option>
            <option value="bank">Bank Transfer</option>
            <option value="jazzcash">JazzCash</option>
            <option value="easypaisa">EasyPaisa</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Paid On *</label>
          <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Reference #</label>
          <input type="text" name="reference" placeholder="Transaction ID..."
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div class="flex items-end">
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Record & Renew
          </button>
        </div>
      </div>
    </form>
  </div>

  @if($tenant->notes)
  <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
    <p class="text-sm font-semibold text-amber-800 mb-1">Notes</p>
    <p class="text-sm text-amber-700">{{ $tenant->notes }}</p>
  </div>
  @endif
</div>
@endsection
