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
      <h1 class="text-2xl font-bold text-slate-900">Customers</h1>
      <p class="text-sm text-slate-500 mt-0.5">Manage your customers</p>
    </div>
    <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>Add Customer
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Customers</p>
      <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Active</p>
      <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Billed</p>
      <p class="text-2xl font-bold text-slate-900 mt-1">{{ formatCurrency($stats['billed']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Outstanding</p>
      <p class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency($stats['outstanding']) }}</p>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" class="flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone..."
      class="flex-1 min-w-48 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
    <select name="type" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <option value="">All Types</option>
      @foreach(['retail','hotel','restaurant','company','reseller'] as $t)
        <option value="{{ $t }}" {{ request('type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
      @endforeach
    </select>
    <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <option value="">All Status</option>
      <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
      <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
    </select>
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    @if(request()->hasAny(['search','type','status']))
      <a href="{{ route('customers.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>
    @endif
  </form>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Credit Days</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
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
                <span class="badge badge-red ml-1">Blacklisted</span>
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
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('customers.show',$customer) }}" class="text-slate-400 hover:text-green-600" title="View">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </a>
              <a href="{{ route('customers.edit',$customer) }}" class="text-slate-400 hover:text-blue-600" title="Edit">
                <i data-lucide="pencil" class="w-4 h-4"></i>
              </a>
              <form method="POST" action="{{ route('customers.toggle-status',$customer) }}" class="inline">
                @csrf
                <button type="submit" class="text-slate-400 hover:text-yellow-600" title="{{ $customer->is_active?'Deactivate':'Activate' }}">
                  <i data-lucide="{{ $customer->is_active?'toggle-right':'toggle-left' }}" class="w-4 h-4"></i>
                </button>
              </form>
              <form method="POST" action="{{ route('customers.destroy',$customer) }}" class="inline" onsubmit="return confirm('Delete this customer?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-slate-400 hover:text-red-600" title="Delete">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-4 py-12 text-center">
            <i data-lucide="users" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
            <p class="text-slate-500 font-medium">No customers found</p>
            <a href="{{ route('customers.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add your first customer</a>
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
