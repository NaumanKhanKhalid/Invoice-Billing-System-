@extends('layouts.app')
@section('title','Edit — '.$product->name)
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('products.show', $product) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <h1 class="text-xl font-bold text-slate-900">Edit Product</h1>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4">
      @csrf @method('PUT')

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1">Product Name *</label>
          <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('name') border-red-400 @enderror">
          @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">SKU / Code</label>
          <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Barcode (EAN/UPC)</label>
          <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none font-mono"
                 placeholder="e.g. 6901234567890">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Category</label>
          <input type="text" name="category" value="{{ old('category', $product->category) }}" list="cat-list"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          <datalist id="cat-list">
            @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
          </datalist>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Unit *</label>
          <div class="relative">
            <select name="unit" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
              @foreach(['pcs'=>'Pieces','kg'=>'KG','liter'=>'Liter','meter'=>'Meter','box'=>'Box','dozen'=>'Dozen','pair'=>'Pair'] as $val=>$label)
              <option value="{{ $val }}" @selected(old('unit', $product->unit)===$val)>{{ $label }}</option>
              @endforeach
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Cost Price (PKR) *</label>
          <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" required min="0" step="0.01"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Sale Price (PKR) *</label>
          <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" required min="0" step="0.01"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Low Stock Alert At</label>
          <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', $product->low_stock_alert) }}" min="0"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
          <textarea name="description" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="sm:col-span-2 flex items-center gap-3">
          <input type="checkbox" name="is_active" value="1" id="is_active" {{ $product->is_active ? 'checked' : '' }} class="rounded border-slate-300 text-green-600">
          <label for="is_active" class="text-sm text-slate-700">Active</label>
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <a href="{{ route('products.show', $product) }}" class="flex-1 text-center px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-700">Cancel</a>
        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Save Changes</button>
      </div>
    </form>
  </div>

  <form method="POST" action="{{ route('products.destroy', $product) }}"
        data-confirm-title="Delete Product?" data-confirm-message="This will delete {{ $product->name }} and all its stock movements." data-confirm-danger="true">
    @csrf @method('DELETE')
    <button type="submit" class="w-full border border-red-200 text-red-600 hover:bg-red-50 py-2 rounded-lg text-sm font-medium transition-colors">
      Delete Product
    </button>
  </form>
</div>
@endsection
