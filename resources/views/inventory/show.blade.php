@extends('layouts.app')
@section('title', 'Inventory — '.$inventory->date->format('d M Y'))
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('inventory.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $inventory->date->format('d M Y') }}</h1>
        <p class="text-sm text-slate-500">{{ $inventory->chickenType?->name ?? '-' }}</p>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h2 class="font-semibold text-slate-900 mb-4">Stock Details</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
      <div class="bg-slate-50 rounded-lg p-3 text-center"><p class="text-xs text-slate-500 uppercase tracking-wider">Opening</p><p class="text-lg font-bold text-slate-700 mt-1">{{ number_format($inventory->opening_stock_kg,1) }} kg</p></div>
      <div class="bg-green-50 rounded-lg p-3 text-center"><p class="text-xs text-green-600 uppercase tracking-wider">Purchased</p><p class="text-lg font-bold text-green-700 mt-1">{{ number_format($inventory->total_purchased_kg,1) }} kg</p></div>
      <div class="bg-slate-50 rounded-lg p-3 text-center"><p class="text-xs text-slate-500 uppercase tracking-wider">Sold Retail</p><p class="text-lg font-bold text-slate-700 mt-1">{{ number_format($inventory->total_sold_retail_kg,1) }} kg</p></div>
      <div class="bg-slate-50 rounded-lg p-3 text-center"><p class="text-xs text-slate-500 uppercase tracking-wider">Sold Supply</p><p class="text-lg font-bold text-slate-700 mt-1">{{ number_format($inventory->total_sold_supply_kg,1) }} kg</p></div>
      <div class="bg-red-50 rounded-lg p-3 text-center"><p class="text-xs text-red-500 uppercase tracking-wider">Dead</p><p class="text-lg font-bold text-red-600 mt-1">{{ number_format($inventory->dead_kg,1) }} kg</p></div>
      <div class="bg-red-50 rounded-lg p-3 text-center"><p class="text-xs text-red-500 uppercase tracking-wider">Spoilage</p><p class="text-lg font-bold text-red-600 mt-1">{{ number_format($inventory->spoilage_kg,1) }} kg</p></div>
    </div>
    <div class="mt-4 bg-green-50 border border-green-100 rounded-lg p-4 flex items-center justify-between">
      <div><p class="text-sm text-green-700 font-semibold">Closing Stock</p><p class="text-xs text-green-600 mt-0.5">Opening + Purchased − Sold − Dead − Spoilage</p></div>
      <p class="text-2xl font-bold {{ $inventory->closing_stock_kg<50?'text-yellow-700':'text-green-700' }}">{{ number_format($inventory->closing_stock_kg,1) }} kg @if($inventory->closing_stock_kg<50)<span class="text-sm font-medium bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded ml-2">Low Stock</span>@endif</p>
    </div>
    @if($inventory->notes)
    <div class="mt-4 p-3 bg-slate-50 rounded-lg"><p class="text-xs text-slate-500 mb-1">Notes</p><p class="text-sm text-slate-700 whitespace-pre-line">{{ $inventory->notes }}</p></div>
    @endif
  </div>

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h2 class="font-semibold text-slate-900 mb-4">Adjust Stock</h2>
    <form method="POST" action="{{ route('inventory.adjust', $inventory) }}" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Adjustment (kg) <span class="text-red-500">*</span></label>
          <input type="number" name="adjusted_kg" value="{{ old('adjusted_kg') }}" step="0.001" placeholder="+10 or -5" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          <p class="text-xs text-slate-400 mt-1">Use positive to add, negative to deduct</p>
          @error('adjusted_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
          <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Reason for adjustment" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">Apply Adjustment</button>
    </form>
  </div>
</div>
@endsection
