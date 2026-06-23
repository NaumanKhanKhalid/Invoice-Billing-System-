@extends('layouts.app')
@section('title','Suppliers')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Suppliers</h1>
      <p class="text-sm text-slate-500 mt-0.5">Manage your chicken suppliers</p>
    </div>
    <a href="{{ route('suppliers.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>Add Supplier
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Suppliers</p>
      <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Active</p>
      <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Purchased</p>
      <p class="text-2xl font-bold text-slate-900 mt-1">{{ formatCurrency($stats['purchased']) }}</p>
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
    <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      <option value="">All Status</option>
      <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
      <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
    </select>
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    @if(request()->hasAny(['search','status']))
      <a href="{{ route('suppliers.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>
    @endif
  </form>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Phone</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Credit Days</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Purchased</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($suppliers as $supplier)
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <a href="{{ route('suppliers.show',$supplier) }}" class="font-medium text-slate-900 hover:text-green-600">{{ $supplier->name }}</a>
            @if($supplier->address)
              <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($supplier->address,40) }}</p>
            @endif
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $supplier->phone }}</td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $supplier->credit_days }} days</td>
          <td class="px-4 py-3 text-sm text-right font-medium text-slate-900">{{ formatCurrency($supplier->purchase_orders_sum_total_amount??0) }}</td>
          <td class="px-4 py-3 text-sm text-right font-medium {{ ($supplier->balance??0)>0?'text-red-600':'text-slate-500' }}">{{ formatCurrency($supplier->balance??0) }}</td>
          <td class="px-4 py-3">
            <span class="badge {{ $supplier->is_active?'badge-green':'badge-gray' }}">{{ $supplier->is_active?'Active':'Inactive' }}</span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1.5">
              <a href="{{ route('suppliers.show',$supplier) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              <a href="{{ route('suppliers.edit',$supplier) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
              </a>
              <form method="POST" action="{{ route('suppliers.toggle-status',$supplier) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-yellow-100 text-slate-500 hover:text-yellow-700 text-xs font-medium transition-colors">
                  <i data-lucide="{{ $supplier->is_active?'toggle-right':'toggle-left' }}" class="w-3.5 h-3.5"></i>{{ $supplier->is_active?'Active':'Inactive' }}
                </button>
              </form>
              <form method="POST" action="{{ route('suppliers.destroy',$supplier) }}" class="inline" data-confirm-title="Delete Supplier?" data-confirm-message="Are you sure you want to delete this supplier?" data-confirm-text="Yes, Delete">
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
          <td colspan="7" class="px-4 py-12 text-center">
            <i data-lucide="truck" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
            <p class="text-slate-500 font-medium">No suppliers found</p>
            <a href="{{ route('suppliers.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add your first supplier</a>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($suppliers->hasPages())
      <div class="px-4 py-3 border-t border-slate-100">{{ $suppliers->links() }}</div>
    @endif
  </div>
</div>
@endsection
