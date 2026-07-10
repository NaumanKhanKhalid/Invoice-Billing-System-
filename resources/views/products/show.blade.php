@extends('layouts.app')
@section('title',$product->name)
@section('content')
<div class="space-y-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('products.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
      <h1 class="text-xl font-bold text-slate-900">{{ $product->name }}</h1>
      @if($product->sku)<p class="text-sm text-slate-400">SKU: {{ $product->sku }}</p>@endif
    </div>
    <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">
      <i data-lucide="pencil" class="w-4 h-4"></i>Edit
    </a>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase mb-1">Stock</p>
      <p class="text-2xl font-bold {{ $product->stock_qty === 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-amber-600' : 'text-slate-900') }}">
        {{ $product->stock_qty }}
      </p>
      <p class="text-xs text-slate-400">{{ $product->unit }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase mb-1">Cost Price</p>
      <p class="text-xl font-bold text-slate-900">{{ number_format($product->cost_price) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase mb-1">Sale Price</p>
      <p class="text-xl font-bold text-green-600">{{ number_format($product->sale_price) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-center">
      <p class="text-xs text-slate-400 uppercase mb-1">Stock Value</p>
      <p class="text-xl font-bold text-slate-900">{{ number_format($product->stock_qty * $product->cost_price) }}</p>
    </div>
  </div>

  @if($product->wholesale_price || $product->track_serial || ($product->purchase_unit && $product->conversion_factor))
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-3">Pro Details</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      @if($product->wholesale_price)
      <div>
        <p class="text-xs text-slate-400 uppercase mb-1">Wholesale Price</p>
        <p class="text-sm font-semibold text-slate-900">PKR {{ number_format($product->wholesale_price) }}</p>
      </div>
      @endif
      @if($product->track_serial)
      <div>
        <p class="text-xs text-slate-400 uppercase mb-1">Serial Tracking</p>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700">IMEI/Serial Tracked</span>
      </div>
      @endif
      @if($product->purchase_unit && $product->conversion_factor)
      <div>
        <p class="text-xs text-slate-400 uppercase mb-1">Unit Conversion</p>
        <p class="text-sm font-semibold text-slate-900">1 {{ $product->purchase_unit }} = {{ $product->conversion_factor + 0 }} {{ $product->unit }}</p>
      </div>
      @endif
    </div>
  </div>
  @endif

  @if($product->track_serial)
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
    <h2 class="font-semibold text-slate-900 mb-3">Serials</h2>
    <div class="grid grid-cols-2 gap-4">
      <div class="text-center">
        <p class="text-xs text-slate-400 uppercase mb-1">In Stock</p>
        <p class="text-2xl font-bold text-green-600">{{ $serialsInStock }}</p>
      </div>
      <div class="text-center">
        <p class="text-xs text-slate-400 uppercase mb-1">Sold</p>
        <p class="text-2xl font-bold text-slate-900">{{ $serialsSold }}</p>
      </div>
    </div>
  </div>
  @endif

  {{-- Stock Adjustment --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5" x-data="{ open: false }">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold text-slate-900">Adjust Stock</h2>
      <button @click="open=!open" class="text-sm text-green-600 hover:underline flex items-center gap-1">
        <i data-lucide="plus-circle" class="w-4 h-4"></i><span x-text="open ? 'Cancel' : 'Add / Remove Stock'"></span>
      </button>
    </div>
    <div x-show="open" x-cloak>
      <form method="POST" action="{{ route('products.stock', $product) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @csrf
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Type *</label>
          <div class="relative">
            <select name="type" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
              <option value="in">Stock In (Purchase)</option>
              <option value="out">Stock Out (Sale/Use)</option>
              <option value="adjustment">Set Exact Qty</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Quantity *</label>
          <input type="number" name="qty" min="1" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Unit Price (optional)</label>
          <input type="number" name="unit_price" min="0" step="0.01" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
          <input type="text" name="notes" placeholder="Reason..." class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div class="sm:col-span-4">
          <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Update Stock</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Stock movement history --}}
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Stock History</h2>
      <a href="{{ route('stock-movements.index', ['product_id' => $product->id]) }}" class="text-sm text-green-600 hover:underline">View All &rarr;</a>
    </div>
    @if($movements->count())
    <table class="w-full">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Qty</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Unit Price</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Notes</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($movements as $m)
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-sm text-slate-600">{{ $m->created_at->format('d M Y H:i') }}</td>
          <td class="px-4 py-3">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
              {{ $m->type === 'in' ? 'bg-green-100 text-green-700' : ($m->type === 'out' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
              {{ ucfirst($m->type) }}
            </span>
          </td>
          <td class="px-4 py-3 text-sm text-right font-medium {{ $m->type === 'in' ? 'text-green-600' : ($m->type === 'out' ? 'text-red-600' : 'text-slate-600') }}">
            {{ $m->type === 'in' ? '+' : ($m->type === 'out' ? '-' : '=') }}{{ abs($m->qty) }}
          </td>
          <td class="px-4 py-3 text-sm text-right text-slate-600">{{ $m->unit_price ? number_format($m->unit_price) : '—' }}</td>
          <td class="px-4 py-3 text-sm text-slate-400">{{ $m->notes ?? $m->reference ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="px-4 py-8 text-center text-slate-400 text-sm">No stock movements yet</div>
    @endif
  </div>
</div>
@endsection
