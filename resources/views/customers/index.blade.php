@extends('layouts.app')
@section('title','Customers')
@section('content')
@php
$typeBadge = [
    'retail'     => 'badge-gray',
    'hotel'      => 'badge-blue',
    'restaurant' => 'badge-purple',
    'company'    => 'badge-green',
    'reseller'   => 'badge-yellow',
];
@endphp
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">{{ __('pages.customers') }}</h1>
      <p class="text-sm text-slate-500 mt-0.5">{{ __('pages.customers_sub') }}</p>
    </div>
    <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>Add Customer
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('pages.total_customers') }}</p>
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
          <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('common.active') }}</p>
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
          <i data-lucide="user-check" class="w-4 h-4 text-green-600"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('pages.total_billed') }}</p>
        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
          <i data-lucide="receipt" class="w-4 h-4 text-blue-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-slate-900">{{ formatCurrency($stats['billed']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('pages.outstanding') }}</p>
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-red-600">{{ formatCurrency($stats['outstanding']) }}</p>
    </div>
  </div>

  {{-- Filters --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
      <div class="relative flex-1 min-w-48">
        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('pages.search_name_phone') }}"
          class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <div class="relative">
        <i data-lucide="layers" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <select name="type" class="pl-9 pr-8 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white text-slate-700 appearance-none cursor-pointer">
          <option value="">{{ __('pages.all_types') }}</option>
          @foreach(['retail','hotel','restaurant','company','reseller'] as $t)
            <option value="{{ $t }}" {{ request('type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
          @endforeach
        </select>
        <i data-lucide="chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      </div>
      <div class="relative">
        <i data-lucide="circle-dot" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
        <select name="status" class="pl-9 pr-8 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white text-slate-700 appearance-none cursor-pointer">
          <option value="">{{ __('common.all_status') }}</option>
          <option value="active"   {{ request('status')=='active'  ?'selected':'' }}>{{ __('common.active') }}</option>
          <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>{{ __('common.inactive') }}</option>
        </select>
        <i data-lucide="chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
      </div>
      <button type="submit" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i data-lucide="search" class="w-4 h-4"></i> Filter
      </button>
      @if(request()->hasAny(['search','type','status']))
      <a href="{{ route('customers.index') }}" class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
        <i data-lucide="x" class="w-4 h-4"></i> Clear
      </a>
      @endif
      <div class="ml-auto flex items-center gap-4 text-sm text-slate-500">
        <span class="flex items-center gap-1.5">
          <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
          <span class="font-semibold text-slate-700">{{ $customers->total() }}</span> customers
        </span>
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('common.customer') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('pages.type') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('pages.credit_days') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('pages.outstanding') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('common.status') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($customers as $customer)
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <a href="{{ route('customers.show',$customer) }}" class="font-medium text-slate-900 hover:text-green-600">{{ $customer->name }}</a>
            <p class="text-xs text-slate-400 mt-0.5">
              {{ $customer->phone }}
              @if($customer->is_blacklisted)
                <span class="badge badge-red ml-1">{{ __('pages.blacklisted') }}</span>
              @endif
            </p>
          </td>
          <td class="px-4 py-3">
            <span class="badge {{ $typeBadge[$customer->type] ?? 'badge-gray' }}">{{ ucfirst($customer->type) }}</span>
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $customer->credit_days }} days</td>
          <td class="px-4 py-3 text-sm text-right font-medium {{ ($customer->current_balance??0)>0?'text-red-600':'text-slate-500' }}">
            {{ formatCurrency($customer->current_balance??0) }}
          </td>
          <td class="px-4 py-3">
            <span class="badge {{ $customer->is_active?'badge-green':'badge-gray' }}">{{ $customer->is_active?'Active':'Inactive' }}</span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1.5">
              <a href="{{ route('customers.show',$customer) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              <a href="{{ route('customers.edit',$customer) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
              </a>
              <form method="POST" action="{{ route('customers.toggle-status',$customer) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-yellow-100 text-slate-500 hover:text-yellow-700 text-xs font-medium transition-colors">
                  <i data-lucide="{{ $customer->is_active?'toggle-right':'toggle-left' }}" class="w-3.5 h-3.5"></i>{{ $customer->is_active?'Active':'Inactive' }}
                </button>
              </form>
              <form method="POST" action="{{ route('customers.destroy',$customer) }}" class="inline" data-confirm-title="Delete Customer?" data-confirm-message="Are you sure you want to delete this customer?" data-confirm-text="Yes, Delete">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-700 text-xs font-medium transition-colors">
                  <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-4 py-12 text-center">
            <i data-lucide="users" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
            <p class="text-slate-500 font-medium">{{ __('pages.no_customers') }}</p>
            <a href="{{ route('customers.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">{{ __('pages.add_first_customer') }}</a>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($customers->hasPages())
      <div class="px-4 py-3 border-t border-slate-100">{{ $customers->links() }}</div>
    @endif
  </div>
</div>
@endsection
