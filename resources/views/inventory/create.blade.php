@extends('layouts.app')
@section('title','Add Stock Entry')
@section('content')
<div class="max-w-2xl mx-auto space-y-5" x-data="{
    opening: 0, purchased: 0, soldRetail: 0, soldSupply: 0, dead: 0, spoilage: 0,
    get closing() { return Math.max(0, parseFloat(this.opening||0) + parseFloat(this.purchased||0) - parseFloat(this.soldRetail||0) - parseFloat(this.soldSupply||0) - parseFloat(this.dead||0) - parseFloat(this.spoilage||0)); }
}">
  <div class="flex items-center gap-3">
    <a href="{{ route('inventory.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div><h1 class="text-2xl font-bold text-slate-900">Add Stock Entry</h1><p class="text-sm text-slate-500">Record daily inventory</p></div>
  </div>

  <form method="POST" action="{{ route('inventory.store') }}" class="space-y-5">
    @csrf
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Entry Details</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
          <input type="date" name="date" value="{{ old('date', today()->toDateString()) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Chicken Type <span class="text-red-500">*</span></label>
          <select name="chicken_type_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
            <option value="">— Select Type —</option>
            @foreach($chickenTypes as $t)<option value="{{ $t->id }}" {{ old('chicken_type_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
          </select>
          @error('chicken_type_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Stock Movement (kg)</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Opening Stock <span class="text-red-500">*</span></label>
          <input type="number" name="opening_stock_kg" x-model="opening" value="{{ old('opening_stock_kg',0) }}" step="0.001" min="0" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Purchased</label>
          <input type="number" name="total_purchased_kg" x-model="purchased" value="{{ old('total_purchased_kg',0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Sold Retail</label>
          <input type="number" name="total_sold_retail_kg" x-model="soldRetail" value="{{ old('total_sold_retail_kg',0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Sold Supply</label>
          <input type="number" name="total_sold_supply_kg" x-model="soldSupply" value="{{ old('total_sold_supply_kg',0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Dead on Arrival</label>
          <input type="number" name="dead_kg" x-model="dead" value="{{ old('dead_kg',0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Spoilage</label>
          <input type="number" name="spoilage_kg" x-model="spoilage" value="{{ old('spoilage_kg',0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>

      <div class="bg-green-50 rounded-lg p-4 border border-green-100 flex items-center justify-between">
        <div>
          <p class="text-sm text-green-700 font-medium">Closing Stock</p>
          <p class="text-xs text-green-600 mt-0.5">Opening + Purchased − Sold − Dead − Spoilage</p>
        </div>
        <p class="text-2xl font-bold text-green-700" x-text="closing.toFixed(1) + ' kg'">0.0 kg</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('notes') }}</textarea>
      </div>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Save Entry</button>
      <a href="{{ route('inventory.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
    </div>
  </form>
</div>
@endsection
