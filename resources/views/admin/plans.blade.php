@extends('layouts.app')
@section('title','Subscription Plans')
@section('content')
<div class="space-y-6" x-data="{ addOpen: false, editId: null }">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Subscription Plans</h1>
      <p class="text-sm text-slate-500 mt-0.5">Manage pricing, limits &amp; features — changes go live instantly</p>
    </div>
    <button type="button" @click="addOpen = !addOpen"
            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i> New Plan
    </button>
  </div>

  {{-- Add plan form --}}
  <div x-show="addOpen" x-cloak class="bg-white rounded-xl border border-green-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-4">Add a Plan</h2>
    <form method="POST" action="{{ route('admin.plans.store') }}" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Key (a-z_)</label>
        <input name="key" required placeholder="enterprise" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-green-300">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Name</label>
        <input name="name" required placeholder="Enterprise" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-green-300">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Price / mo (PKR)</label>
        <input name="price" type="number" min="0" required value="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-green-300">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Max Users (-1 = ∞)</label>
        <input name="max_users" type="number" min="-1" required value="1" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-green-300">
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="staff_module" value="1" class="rounded border-slate-300 text-green-600"> Staff &amp; Salary</label>
      <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="google_backup" value="1" class="rounded border-slate-300 text-green-600"> Google Backup</label>
      <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="priority_support" value="1" class="rounded border-slate-300 text-green-600"> Priority Support</label>
      <div class="flex items-end">
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold">Save Plan</button>
      </div>
    </form>
  </div>

  {{-- Plan cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    <div class="bg-white rounded-2xl border {{ $plan->is_popular ? 'border-green-400 shadow-lg ring-2 ring-green-200' : 'border-slate-200 shadow-sm' }} p-6 relative">
      @if($plan->is_popular)
      <div class="absolute -top-3 left-1/2 -translate-x-1/2"><span class="bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">MOST POPULAR</span></div>
      @endif
      @unless($plan->is_active)
      <div class="absolute top-3 right-3"><span class="bg-slate-200 text-slate-500 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Inactive</span></div>
      @endunless

      {{-- Display --}}
      <div x-show="editId !== {{ $plan->id }}">
        <p class="text-xs font-bold uppercase tracking-widest {{ $plan->is_popular ? 'text-green-600' : 'text-slate-400' }} mb-2">{{ $plan->name }}</p>
        <p class="text-3xl font-black text-slate-900 mb-1">PKR {{ number_format($plan->price) }}<span class="text-base font-normal text-slate-400">/mo</span></p>
        <p class="text-sm text-slate-500 mb-5">{{ $plan->max_users < 0 ? 'Unlimited' : $plan->max_users }} user{{ $plan->max_users == 1 ? '' : 's' }}</p>
        <ul class="space-y-2 text-sm">
          <li class="flex items-center gap-2 text-slate-600"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i> Dashboard &amp; Reports</li>
          <li class="flex items-center gap-2 text-slate-600"><i data-lucide="check" class="w-4 h-4 text-green-500 shrink-0"></i> Purchases, Udhar &amp; Expenses</li>
          <li class="flex items-center gap-2 {{ $plan->staff_module ? 'text-slate-600' : 'text-slate-300' }}"><i data-lucide="{{ $plan->staff_module ? 'check' : 'x' }}" class="w-4 h-4 {{ $plan->staff_module ? 'text-green-500' : 'text-slate-300' }} shrink-0"></i> Staff &amp; Salary</li>
          <li class="flex items-center gap-2 {{ $plan->google_backup ? 'text-slate-600' : 'text-slate-300' }}"><i data-lucide="{{ $plan->google_backup ? 'check' : 'x' }}" class="w-4 h-4 {{ $plan->google_backup ? 'text-green-500' : 'text-slate-300' }} shrink-0"></i> Google Drive Backup</li>
          <li class="flex items-center gap-2 {{ $plan->priority_support ? 'text-slate-600' : 'text-slate-300' }}"><i data-lucide="{{ $plan->priority_support ? 'check' : 'x' }}" class="w-4 h-4 {{ $plan->priority_support ? 'text-green-500' : 'text-slate-300' }} shrink-0"></i> Priority Support</li>
        </ul>
        <div class="mt-5 pt-4 border-t border-slate-100 text-xs text-slate-400 space-y-0.5">
          <p><strong class="text-slate-500">Tenants:</strong> {{ $stats[$plan->key]['tenants'] }} ({{ $stats[$plan->key]['active'] }} active)</p>
          <p><strong class="text-slate-500">Monthly Revenue:</strong> PKR {{ number_format($stats[$plan->key]['monthly']) }}</p>
        </div>
        <div class="mt-4 flex gap-2">
          <button type="button" @click="editId = {{ $plan->id }}" class="flex-1 inline-flex items-center justify-center gap-1.5 bg-slate-100 hover:bg-green-100 text-slate-600 hover:text-green-700 px-3 py-2 rounded-lg text-xs font-bold transition"><i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit</button>
          @if($stats[$plan->key]['tenants'] === 0)
          <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" data-confirm-title="Delete plan?" data-confirm-message="{{ $plan->name }} delete karein?" data-confirm-text="Haan">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex items-center justify-center bg-slate-100 hover:bg-red-100 text-slate-400 hover:text-red-600 w-9 h-9 rounded-lg text-xs transition"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
          </form>
          @endif
        </div>
      </div>

      {{-- Edit form --}}
      <form x-show="editId === {{ $plan->id }}" x-cloak method="POST" action="{{ route('admin.plans.update', $plan) }}" class="space-y-3">
        @csrf @method('PUT')
        <p class="text-xs font-bold uppercase tracking-widest text-green-600">Edit {{ $plan->key }}</p>
        <div>
          <label class="block text-[11px] font-semibold text-slate-500 mb-1">Name</label>
          <input name="name" value="{{ $plan->name }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-green-300">
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Price/mo</label>
            <input name="price" type="number" min="0" value="{{ $plan->price }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-green-300">
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Max Users (-1=∞)</label>
            <input name="max_users" type="number" min="-1" value="{{ $plan->max_users }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-green-300">
          </div>
        </div>
        <div class="space-y-1.5 pt-1">
          <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="staff_module" value="1" @checked($plan->staff_module) class="rounded border-slate-300 text-green-600"> Staff &amp; Salary</label>
          <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="google_backup" value="1" @checked($plan->google_backup) class="rounded border-slate-300 text-green-600"> Google Backup</label>
          <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="priority_support" value="1" @checked($plan->priority_support) class="rounded border-slate-300 text-green-600"> Priority Support</label>
          <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_popular" value="1" @checked($plan->is_popular) class="rounded border-slate-300 text-green-600"> Most Popular</label>
          <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_active" value="1" @checked($plan->is_active) class="rounded border-slate-300 text-green-600"> Active (shown to clients)</label>
        </div>
        <div class="flex gap-2 pt-1">
          <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold">Save</button>
          <button type="button" @click="editId = null" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-800">Cancel</button>
        </div>
      </form>
    </div>
    @endforeach
  </div>

  {{-- Revenue Overview --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-3">Revenue Overview</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-center">
      <div class="bg-slate-50 rounded-xl p-4">
        <p class="text-xs text-slate-400 uppercase font-semibold">Projected MRR</p>
        <p class="text-2xl font-bold text-slate-900 mt-1">PKR {{ number_format($totalMonthly) }}</p>
      </div>
      <div class="bg-green-50 rounded-xl p-4">
        <p class="text-xs text-green-600 uppercase font-semibold">Collected (This Month)</p>
        <p class="text-2xl font-bold text-green-700 mt-1">PKR {{ number_format($collectedMonth) }}</p>
      </div>
      <div class="bg-blue-50 rounded-xl p-4">
        <p class="text-xs text-blue-500 uppercase font-semibold">Projected Yearly</p>
        <p class="text-2xl font-bold text-blue-700 mt-1">PKR {{ number_format($totalMonthly * 12) }}</p>
      </div>
    </div>
  </div>
</div>
@endsection
