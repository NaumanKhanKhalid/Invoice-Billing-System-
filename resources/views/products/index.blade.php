@extends('layouts.app')
@section('title','Products & Inventory')
@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900">{{ __('product.title') }}</h1>
      <p class="text-sm text-slate-500">{{ $products->total() }} products</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reorder.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="shopping-cart" class="w-4 h-4"></i>{{ __('product.reorder') }}
      </a>
      <a href="{{ route('barcode-labels.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="barcode" class="w-4 h-4"></i>Print Labels
      </a>
      <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
        <i data-lucide="plus" class="w-4 h-4"></i>{{ __('product.add_product') }}
      </a>
    </div>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ __('product.total_products') }}</p>
      <p class="text-2xl font-bold text-slate-900">{{ $products->total() }}</p>
    </div>
    <div class="bg-white rounded-xl border {{ $lowStockCount > 0 ? 'border-amber-200' : 'border-slate-200' }} shadow-sm p-4 text-center">
      <p class="text-xs {{ $lowStockCount > 0 ? 'text-amber-500' : 'text-slate-400' }} uppercase tracking-wider mb-1">{{ __('product.low_stock') }}</p>
      <p class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $lowStockCount }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ __('product.stock_value') }}</p>
      <p class="text-xl font-bold text-slate-900">PKR {{ number_format($totalValue) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ __('product.categories') }}</p>
      <p class="text-2xl font-bold text-slate-900">{{ $categories->count() }}</p>
    </div>
  </div>

  {{-- Filters --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
      <div class="relative flex-1 min-w-36">
        <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('product.search_ph') }}"
               class="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
      </div>
      <div class="relative">
        <i data-lucide="tag" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <select name="category" class="appearance-none pl-8 pr-7 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="">{{ __('product.all_categories') }}</option>
          @foreach($categories as $cat)
          <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
          @endforeach
        </select>
        <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
      </div>
      <div class="relative">
        <i data-lucide="package" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none"></i>
        <select name="stock" class="appearance-none pl-8 pr-7 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
          <option value="">{{ __('product.all_stock') }}</option>
          <option value="low" @selected(request('stock')==='low')>{{ __('product.low_stock') }}</option>
          <option value="out" @selected(request('stock')==='out')>{{ __('product.out_of_stock') }}</option>
        </select>
        <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
      </div>
      <button type="submit" class="bg-green-600 text-white px-3 py-1.5 text-sm rounded-lg hover:bg-green-700">{{ __('common.filter') }}</button>
      @if(request()->hasAny(['search','category','stock']))
      <a href="{{ route('products.index') }}" class="text-sm text-slate-400 hover:text-slate-600">{{ __('common.clear') }}</a>
      @endif
      <div class="ml-auto text-sm text-slate-500">{{ $products->total() }} products</div>
    </form>
  </div>

  {{-- Products table --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($products->count())
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('product.col_product') }}</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">{{ __('product.col_category') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('product.col_cost') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('product.col_sale_price') }}</th>
          <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">{{ __('product.col_stock') }}</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">{{ __('product.col_actions') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($products as $product)
        <tr class="hover:bg-slate-50 {{ $product->stock_qty === 0 ? 'bg-red-50' : ($product->isLowStock() ? 'bg-amber-50' : '') }}">
          <td class="px-4 py-3">
            <div class="flex items-center gap-1.5">
              <a href="{{ route('products.show', $product) }}" class="font-medium text-slate-900 hover:text-green-600">{{ $product->name }}</a>
              @if($product->track_serial)
                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">IMEI</span>
              @endif
            </div>
            @if($product->sku)<p class="text-xs text-slate-400">SKU: {{ $product->sku }}</p>@endif
          </td>
          <td class="px-4 py-3 text-sm text-slate-500">{{ $product->category ?? '—' }}</td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ number_format($product->cost_price) }}</td>
          <td class="px-4 py-3 text-sm text-right font-semibold text-slate-900">
            {{ number_format($product->sale_price) }}
            @if($product->wholesale_price)
              <p class="text-xs font-normal text-slate-400">W: PKR {{ number_format($product->wholesale_price) }}</p>
            @endif
          </td>
          <td class="px-4 py-3 text-center">
            @if($product->stock_qty === 0)
              <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">{{ __('product.out_of_stock') }}</span>
            @elseif($product->isLowStock())
              <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700">{{ $product->stock_qty }} {{ $product->unit }} ⚠️</span>
            @else
              <span class="text-sm font-medium text-slate-900">{{ $product->stock_qty }} {{ $product->unit }}</span>
            @endif
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <a href="{{ route('products.show', $product) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-green-100 text-slate-500 hover:text-green-700 text-xs font-medium">{{ __('common.view') }}</a>
              <a href="{{ route('products.edit', $product) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-700 text-xs font-medium">{{ __('common.edit') }}</a>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-4 py-3 border-t border-slate-100">{{ $products->links() }}</div>
    @else
    <div class="px-4 py-12 text-center">
      <i data-lucide="package" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
      <p class="text-slate-400">{{ __('product.no_products') }}</p>
      <a href="{{ route('products.create') }}" class="text-green-600 text-sm hover:underline mt-1 inline-block">{{ __('product.add_first') }}</a>
    </div>
    @endif
  </div>
</div>
@endsection
