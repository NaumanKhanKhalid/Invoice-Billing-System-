@extends('layouts.app')
@section('title','Add Product')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <div class="flex items-center gap-3">
    <a href="{{ route('products.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <h1 class="text-xl font-bold text-slate-900">Add Product</h1>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('products.store') }}" class="space-y-4" enctype="multipart/form-data"
          x-data="{ unit: '{{ old('unit', 'pcs') }}', pu: '{{ old('purchase_unit') }}', cf: '{{ old('conversion_factor') }}', imgPreview: '' }">
      @csrf

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1">Product Name *</label>
          <input type="text" name="name" value="{{ old('name') }}" required
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none @error('name') border-red-400 @enderror"
                 placeholder="e.g. Honda CD-70 Chain">
          @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">SKU / Code</label>
          <input type="text" name="sku" value="{{ old('sku') }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                 placeholder="e.g. CD70-CHN-001">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Barcode (EAN/UPC)</label>
          <input type="text" name="barcode" value="{{ old('barcode') }}"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none font-mono"
                 placeholder="e.g. 6901234567890">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Category</label>
          <input type="text" name="category" value="{{ old('category') }}" list="cat-list"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                 placeholder="e.g. Engine Parts">
          <datalist id="cat-list">
            @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
          </datalist>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Unit *</label>
          <div class="relative">
            <select name="unit" x-model="unit" class="appearance-none w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white pr-8">
              @foreach(['pcs'=>'Pieces','kg'=>'KG','liter'=>'Liter','meter'=>'Meter','box'=>'Box','dozen'=>'Dozen','pair'=>'Pair'] as $val=>$label)
              <option value="{{ $val }}" @selected(old('unit')===$val || $val==='pcs')>{{ $label }}</option>
              @endforeach
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Cost Price (PKR) *</label>
          <input type="number" name="cost_price" value="{{ old('cost_price', 0) }}" required min="0" step="0.01"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Sale Price (PKR) *</label>
          <input type="number" name="sale_price" value="{{ old('sale_price', 0) }}" required min="0" step="0.01"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Wholesale Price (optional)</label>
          <input type="number" name="wholesale_price" value="{{ old('wholesale_price') }}" min="0" step="0.01"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          <p class="text-xs text-slate-400 mt-1">Mechanic/thekedaar rate — khali chhodo to sab ko retail</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Opening Stock</label>
          <input type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" min="0"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Low Stock Alert At</label>
          <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', 5) }}" min="0"
                 class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
          <textarea name="description" rows="2"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none"
                    placeholder="Optional product details...">{{ old('description') }}</textarea>
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1">Product Image <span class="text-slate-400 font-normal">(optional — POS card par dikhega)</span></label>
          <div class="flex items-center gap-3">
            <div class="w-16 h-16 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
              <template x-if="imgPreview"><img :src="imgPreview" class="w-full h-full object-cover"></template>
              <template x-if="!imgPreview"><svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg></template>
            </div>
            <input type="file" name="image" accept="image/*"
                   @change="const f=$event.target.files[0]; imgPreview = f ? URL.createObjectURL(f) : ''"
                   class="text-sm text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:text-xs file:font-semibold hover:file:bg-slate-200">
          </div>
        </div>

        <div class="sm:col-span-2 flex items-start gap-3">
          <input type="checkbox" name="track_serial" value="1" id="track_serial" @checked(old('track_serial')) class="rounded border-slate-300 text-green-600 mt-0.5">
          <div>
            <label for="track_serial" class="text-sm text-slate-700">Track IMEI/Serial Numbers</label>
            <p class="text-xs text-slate-400">Mobile phones waghera ke liye — har sale par IMEI record hoga</p>
          </div>
        </div>

        <div class="sm:col-span-2 border border-slate-200 rounded-lg p-4 space-y-3">
          <h3 class="text-sm font-semibold text-slate-700">Unit Conversion (optional)</h3>
          <p class="text-xs text-slate-400">Maal bade unit mein aata hai to yahan set karo</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Purchase Unit</label>
              <input type="text" name="purchase_unit" x-model="pu" value="{{ old('purchase_unit') }}" maxlength="30"
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                     placeholder="e.g. Roll">
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Conversion Factor</label>
              <input type="number" name="conversion_factor" x-model="cf" value="{{ old('conversion_factor') }}" min="0.001" step="0.001"
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                     placeholder="e.g. 100">
            </div>
          </div>
          <p class="text-xs text-green-600" x-show="pu && cf" x-cloak>
            1 <span x-text="pu"></span> = <span x-text="cf"></span> <span x-text="unit"></span>
          </p>
        </div>

        <div class="sm:col-span-2 flex items-center gap-3">
          <input type="checkbox" name="is_active" value="1" id="is_active" checked class="rounded border-slate-300 text-green-600">
          <label for="is_active" class="text-sm text-slate-700">Active (visible in system)</label>
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <a href="{{ route('products.index') }}" class="flex-1 text-center px-4 py-2 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-700">Cancel</a>
        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Add Product</button>
      </div>
    </form>
  </div>
</div>
@endsection
