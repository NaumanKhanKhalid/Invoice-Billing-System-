@extends('layouts.app')
@section('title','Stock History')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900">Stock History</h1>
      <p class="text-sm text-slate-500">{{ $movements->total() }} movements</p>
    </div>
  </div>

  {{-- Filters --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
      <div class="relative">
        <i data-lucide="package" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <select name="product_id" class="appearance-none pl-8 pr-7 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="">All Products</option>
          @foreach($products as $p)
          <option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->name }}</option>
          @endforeach
        </select>
        <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
      </div>
      <div class="relative">
        <i data-lucide="arrow-down-up" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <select name="type" class="appearance-none pl-8 pr-7 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="">All Types</option>
          @foreach($types as $type)
          <option value="{{ $type }}" @selected(request('type')===$type)>{{ ucfirst($type) }}</option>
          @endforeach
        </select>
        <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
      </div>
      <div class="relative">
        <i data-lucide="calendar" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <input type="date" name="date" value="{{ request('date') }}"
               class="pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
      </div>
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium">Filter</button>
      @if(request()->hasAny(['product_id','type','date']))
      <a href="{{ route('stock-movements.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
      @endif
    </form>
  </div>

  {{-- Movements table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($movements->count())
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">When</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Product</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Qty</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reference / Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($movements as $m)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{ $m->created_at->format('d M Y H:i') }}</td>
            <td class="px-4 py-3 text-sm">
              @if($m->product)
              <a href="{{ route('products.show', $m->product) }}" class="font-medium text-slate-900 hover:text-green-600">{{ $m->product->name }}</a>
              @else
              <span class="text-slate-400">Deleted product</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
                {{ $m->type === 'in' ? 'bg-green-100 text-green-700' : ($m->type === 'out' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                {{ ucfirst($m->type) }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-right font-medium {{ $m->type === 'in' ? 'text-green-600' : ($m->type === 'out' ? 'text-red-600' : 'text-slate-600') }}">
              {{ $m->type === 'in' ? '+' : ($m->type === 'out' ? '-' : '=') }}{{ abs($m->qty) }}
            </td>
            <td class="px-4 py-3 text-sm text-slate-400">{{ $m->reference ?? $m->notes ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $movements->links() }}</div>
    @else
    <div class="px-4 py-12 text-center">
      <i data-lucide="history" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-500 text-sm font-medium">No stock movements found</p>
      <p class="text-slate-400 text-xs mt-1">Try adjusting the filters or record stock changes from a product page.</p>
    </div>
    @endif
  </div>
</div>
@endsection
