@extends('layouts.app')
@section('title','Credit Customers')
@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Credit Customers</h1>
      <p class="text-sm text-slate-500 mt-0.5">Manage udhar / credit accounts</p>
    </div>
    <a href="{{ route('udhar-customers.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
      <i data-lucide="user-plus" class="w-4 h-4"></i>New Customer
    </a>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Balance Due</p>
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-red-600">{{ formatCurrency($totalBalance) }}</p>
      <p class="text-xs text-slate-400 mt-1">Total outstanding</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Given</p>
        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
          <i data-lucide="arrow-up-right" class="w-4 h-4 text-blue-500"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-slate-800">{{ formatCurrency($totalGiven) }}</p>
      <p class="text-xs text-slate-400 mt-1">Credit issued</p>
    </div>
    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Received</p>
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
          <i data-lucide="arrow-down-left" class="w-4 h-4 text-green-600"></i>
        </div>
      </div>
      <p class="text-2xl font-bold text-green-600">{{ formatCurrency($totalReceived) }}</p>
      <p class="text-xs text-slate-400 mt-1">Payments collected</p>
    </div>
  </div>

  {{-- Search --}}
  <form method="GET" class="flex gap-2">
    <div class="relative flex-1">
      <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
      <input type="text" name="search" value="{{ request('search') }}"
             placeholder="Search by name or phone..."
             class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
    </div>
    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Search</button>
    @if(request('search'))
    <a href="{{ route('udhar-customers.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Clear</a>
    @endif
  </form>

  {{-- Customer Cards / Table --}}
  @if($customers->isEmpty())
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-6 py-16 text-center">
    <i data-lucide="users" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
    <p class="text-slate-500 font-medium">No customers yet</p>
    <a href="{{ route('udhar-customers.create') }}" class="text-green-600 text-sm mt-1 inline-block hover:underline">Add first customer →</a>
  </div>
  @else
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden table-responsive">
    <table class="w-full">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Phone</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Given</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Received</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance</th>
          <th class="px-5 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($customers as $customer)
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-3.5">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0 text-green-700 font-bold text-sm">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
              </div>
              <div>
                <a href="{{ route('udhar-customers.show', $customer) }}" class="text-sm font-semibold text-slate-900 hover:text-green-600 transition-colors">{{ $customer->name }}</a>
                @if($customer->notes)
                <p class="text-xs text-slate-400 truncate max-w-[180px]">{{ $customer->notes }}</p>
                @endif
              </div>
            </div>
          </td>
          <td class="px-5 py-3.5">
            @if($customer->phone)
            <a href="tel:{{ $customer->phone }}" class="text-sm text-slate-600 hover:text-green-600 transition-colors flex items-center gap-1">
              <i data-lucide="phone" class="w-3 h-3 text-slate-400"></i>{{ $customer->phone }}
            </a>
            @else
            <span class="text-sm text-slate-400">—</span>
            @endif
          </td>
          <td class="px-5 py-3.5 text-sm text-right text-slate-700 font-medium">{{ formatCurrency($customer->total_given) }}</td>
          <td class="px-5 py-3.5 text-sm text-right text-green-600 font-medium">{{ formatCurrency($customer->total_received) }}</td>
          <td class="px-5 py-3.5 text-right">
            @if($customer->current_balance > 0)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">
              {{ formatCurrency($customer->current_balance) }}
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">
              Cleared
            </span>
            @endif
          </td>
          <td class="px-5 py-3.5">
            <div class="flex items-center justify-end gap-1.5">
              <a href="{{ route('udhar-customers.show', $customer) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-green-50 hover:bg-green-100 text-green-700 text-xs font-medium transition-colors">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>View
              </a>
              <a href="{{ route('udhar-customers.edit', $customer) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium transition-colors">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
              </a>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($customers->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">{{ $customers->links() }}</div>
    @endif
  </div>
  @endif

</div>
@endsection
