@extends('layouts.app')
@section('title','Pricing Plans')
@section('content')
<div class="space-y-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Subscription Plans</h1>
    <p class="text-sm text-slate-500 mt-0.5">Current plan structure for clients</p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    @foreach(config('plans') as $key => $plan)
    <div class="bg-white rounded-2xl border {{ $key === 'pro' ? 'border-green-400 shadow-lg ring-2 ring-green-200' : 'border-slate-200 shadow-sm' }} p-6 relative">
      @if($key === 'pro')
      <div class="absolute -top-3 left-1/2 -translate-x-1/2">
        <span class="bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">MOST POPULAR</span>
      </div>
      @endif
      <p class="text-xs font-bold uppercase tracking-widest {{ $key === 'pro' ? 'text-green-600' : 'text-slate-400' }} mb-2">{{ $plan['name'] }}</p>
      <p class="text-3xl font-black text-slate-900 mb-1">PKR {{ number_format($plan['price']) }}<span class="text-base font-normal text-slate-400">/mo</span></p>
      <p class="text-sm text-slate-500 mb-5">{{ $plan['max_users'] === PHP_INT_MAX ? 'Unlimited' : $plan['max_users'] }} user{{ $plan['max_users'] > 1 ? 's' : '' }}</p>

      <ul class="space-y-2 text-sm">
        <li class="flex items-center gap-2 text-slate-600"><i data-lucide="check" class="w-4 h-4 text-green-500 flex-shrink-0"></i> Dashboard & Reports</li>
        <li class="flex items-center gap-2 text-slate-600"><i data-lucide="check" class="w-4 h-4 text-green-500 flex-shrink-0"></i> Purchases & Supply Orders</li>
        <li class="flex items-center gap-2 text-slate-600"><i data-lucide="check" class="w-4 h-4 text-green-500 flex-shrink-0"></i> Udhar Book & Expenses</li>
        <li class="flex items-center gap-2 {{ $plan['staff_module'] ? 'text-slate-600' : 'text-slate-300' }}">
          <i data-lucide="{{ $plan['staff_module'] ? 'check' : 'x' }}" class="w-4 h-4 {{ $plan['staff_module'] ? 'text-green-500' : 'text-slate-300' }} flex-shrink-0"></i>
          Staff & Salary
        </li>
        <li class="flex items-center gap-2 {{ $plan['google_backup'] ? 'text-slate-600' : 'text-slate-300' }}">
          <i data-lucide="{{ $plan['google_backup'] ? 'check' : 'x' }}" class="w-4 h-4 {{ $plan['google_backup'] ? 'text-green-500' : 'text-slate-300' }} flex-shrink-0"></i>
          Google Drive Backup
        </li>
        <li class="flex items-center gap-2 {{ $key === 'business' ? 'text-slate-600' : 'text-slate-300' }}">
          <i data-lucide="{{ $key === 'business' ? 'check' : 'x' }}" class="w-4 h-4 {{ $key === 'business' ? 'text-green-500' : 'text-slate-300' }} flex-shrink-0"></i>
          Priority Support
        </li>
      </ul>

      <div class="mt-5 pt-4 border-t border-slate-100 text-xs text-slate-400">
        <p><strong>Tenants on this plan:</strong>
          {{ \App\Models\Tenant::where('plan', $key)->count() }}
        </p>
        <p class="mt-0.5"><strong>Monthly Revenue:</strong>
          PKR {{ number_format(\App\Models\Tenant::where('plan', $key)->where('is_active', true)->count() * $plan['price']) }}
        </p>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Revenue Summary --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-3">Revenue Overview</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
      @php
        $totalMonthly = collect(config('plans'))->reduce(function ($carry, $p, $k) {
          return $carry + \App\Models\Tenant::where('plan', $k)->where('is_active', true)->count() * $p['price'];
        }, 0);
      @endphp
      <div>
        <p class="text-xs text-slate-400 mb-1">Active Tenants</p>
        <p class="text-2xl font-bold text-slate-900">{{ \App\Models\Tenant::where('is_active', true)->count() }}</p>
      </div>
      <div>
        <p class="text-xs text-slate-400 mb-1">Monthly Revenue</p>
        <p class="text-2xl font-bold text-green-600">PKR {{ number_format($totalMonthly) }}</p>
      </div>
      <div>
        <p class="text-xs text-slate-400 mb-1">Yearly (est.)</p>
        <p class="text-2xl font-bold text-slate-900">PKR {{ number_format($totalMonthly * 12) }}</p>
      </div>
      <div>
        <p class="text-xs text-slate-400 mb-1">Expiring (7 days)</p>
        <p class="text-2xl font-bold text-amber-600">
          {{ \App\Models\Tenant::where('is_active', true)->whereBetween('plan_expires_at', [now(), now()->addDays(7)])->count() }}
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
