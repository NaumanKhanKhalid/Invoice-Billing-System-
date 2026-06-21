@extends('layouts.app')
@section('title','Udhar Customers')
@section('content')
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Udhar Customers</h1>
      <p class="text-sm text-slate-500 mt-0.5">Registered credit customers</p>
    </div>
    <a href="{{ route('udhar-customers.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>New Customer
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Balance Due</p>
      <p class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency($totalBalance) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Given</p>
      <p class="text-2xl font-bold text-slate-700 mt-1">{{ formatCurrency($totalGiven) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-4">
      <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Received</p>
      <p class="text-2xl font-bold text-green-600 mt-1">{{ formatCurrency($totalReceived) }}</p>
    </div>
  </div>

  {{-- Search --}}
  <form method="GET" class="flex gap-2">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search name or phone..."
           class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Search</button>
    @if(request('search'))<a href="{{ route('udhar-customers.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm">Clear</a>@endif
  </form>

  {{-- Table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Phone</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total Given</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total Received</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Balance</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($customers as $customer)
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <a href="{{ route('udhar-customers.show', $customer) }}" class="text-sm font-medium text-slate-900 hover:text-green-600">{{ $customer->name }}</a>
            @if($customer->notes)<p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $customer->notes }}</p>@endif
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">{{ $customer->phone ?? '-' }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-700">{{ formatCurrency($customer->total_given) }}</td>
          <td class="px-4 py-3 text-sm text-right text-green-600">{{ formatCurrency($customer->total_received) }}</td>
          <td class="px-4 py-3 text-sm text-right font-bold {{ $customer->current_balance > 0 ? 'text-red-600' : 'text-slate-400' }}">
            {{ formatCurrency($customer->current_balance) }}
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="{{ route('udhar-customers.show', $customer) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              <a href="{{ route('udhar-customers.edit', $customer) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
              </a>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-4 py-12 text-center">
            <i data-lucide="users" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
            <p class="text-slate-500 font-medium">No customers yet</p>
            <a href="{{ route('udhar-customers.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add first customer</a>
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
