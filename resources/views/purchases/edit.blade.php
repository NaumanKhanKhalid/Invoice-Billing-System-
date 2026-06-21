@extends('layouts.app')
@section('title','Edit Purchase')
@section('content')
<div class="max-w-3xl mx-auto space-y-5" x-data="{
    liveKg: {{ $purchase->live_weight_kg }},
    doa: {{ $purchase->dead_on_arrival_kg }},
    ratePerKg: {{ $purchase->rate_per_kg_live }},
    get totalAmount() { return (parseFloat(this.liveKg||0)*parseFloat(this.ratePerKg||0)).toFixed(2); }
}">
  <div class="flex items-center gap-3">
    <a href="{{ route('purchases.show',$purchase) }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div><h1 class="text-2xl font-bold text-slate-900">Edit Purchase Order</h1><p class="text-sm text-slate-500">{{ $purchase->invoice_number }}</p></div>
  </div>
  <form method="POST" action="{{ route('purchases.update',$purchase) }}" class="space-y-5">
    @csrf @method('PUT')
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Purchase Details</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Supplier <span class="text-red-500">*</span></label>
          <select name="supplier_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="">— Select Supplier —</option>
            @foreach($suppliers as $s)<option value="{{ $s->id }}" {{ old('supplier_id',$purchase->supplier_id)==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
          <input type="date" name="date" value="{{ old('date',$purchase->date->format('Y-m-d')) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1">Invoice #</label>
          <input type="text" value="{{ $purchase->invoice_number }}" disabled class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 text-slate-500 cursor-not-allowed">
        </div>
      </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Weight & Rate</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Live Weight (kg) <span class="text-red-500">*</span></label>
          <input type="number" name="live_weight_kg" x-model="liveKg" value="{{ old('live_weight_kg',$purchase->live_weight_kg) }}" step="0.001" min="0.001" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Dead on Arrival (kg)</label>
          <input type="number" name="dead_on_arrival_kg" x-model="doa" value="{{ old('dead_on_arrival_kg',$purchase->dead_on_arrival_kg) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Rate per kg (Live) <span class="text-red-500">*</span></label>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
          <input type="number" name="rate_per_kg_live" x-model="ratePerKg" value="{{ old('rate_per_kg_live',$purchase->rate_per_kg_live) }}" step="0.01" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none"></div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 border border-green-100 flex flex-col justify-center">
          <p class="text-xs text-green-600 font-medium uppercase tracking-wider">Total Amount</p>
          <p class="text-2xl font-bold text-green-700 mt-1" x-text="'PKR ' + parseFloat(totalAmount).toLocaleString('en-PK',{minimumFractionDigits:0})"></p>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('notes',$purchase->notes) }}</textarea>
      </div>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Update Purchase Order</button>
      <a href="{{ route('purchases.show',$purchase) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
    </div>
  </form>
</div>
@endsection
