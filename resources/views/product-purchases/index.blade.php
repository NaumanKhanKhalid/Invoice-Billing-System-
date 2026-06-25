@extends('layouts.app')
@section('title','Product Purchases')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900">Product Purchases</h1>
      <p class="text-sm text-slate-500">{{ $purchases->total() }} records</p>
    </div>
    <a href="{{ route('product-purchases.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="plus" class="w-4 h-4"></i>New Purchase
    </a>
  </div>

  {{-- Filter --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
      <div class="relative">
        <i data-lucide="truck" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <select name="supplier_id" class="appearance-none pl-8 pr-7 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="">All Suppliers</option>
          @foreach($suppliers as $s)
          <option value="{{ $s->id }}" @selected(request('supplier_id')==$s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
        <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
      </div>
      <div class="relative">
        <i data-lucide="circle-dot" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <select name="status" class="appearance-none pl-8 pr-7 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="">All Status</option>
          <option value="unpaid" @selected(request('status')==='unpaid')>Unpaid</option>
          <option value="partial" @selected(request('status')==='partial')>Partial</option>
          <option value="paid" @selected(request('status')==='paid')>Paid</option>
        </select>
        <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
      </div>
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <input type="date" name="from_date" value="{{ request('from_date') }}" class="pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <button type="submit" class="bg-green-600 text-white px-3 py-1.5 text-sm rounded-lg hover:bg-green-700">Filter</button>
      @if(request()->hasAny(['supplier_id','status','from_date','to_date']))
      <a href="{{ route('product-purchases.index') }}" class="text-sm text-slate-400 hover:text-slate-600">Clear</a>
      @endif
      <div class="ml-auto text-sm text-slate-600 font-semibold">Total: PKR {{ number_format($filteredTotal) }}</div>
    </form>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($purchases->count())
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Invoice</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Supplier</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Due</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($purchases as $p)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm text-slate-600">{{ $p->date->format('d M Y') }}</td>
          <td class="px-4 py-3 text-sm text-slate-500">{{ $p->invoice_number ?? '—' }}</td>
          <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $p->supplier?->name ?? '—' }}</td>
          <td class="px-4 py-3 text-sm text-right font-semibold text-slate-900">{{ number_format($p->total_amount) }}</td>
          <td class="px-4 py-3 text-sm text-right {{ $p->amount_due > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">
            {{ $p->amount_due > 0 ? number_format($p->amount_due) : '—' }}
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
              {{ $p->payment_status === 'paid' ? 'bg-green-100 text-green-700' : ($p->payment_status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
              {{ ucfirst($p->payment_status) }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('product-purchases.show', $p) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium">View</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-100">{{ $purchases->links() }}</div>
    @else
    <div class="px-4 py-12 text-center">
      <i data-lucide="shopping-cart" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-400">No purchases yet</p>
    </div>
    @endif
  </div>
</div>
@endsection
