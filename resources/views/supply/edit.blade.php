@extends('layouts.app')
@section('title','Edit Supply Order')
@section('content')
<div class="max-w-3xl mx-auto space-y-5" x-data="{
    dressedKg: {{ $supply->dressed_weight_kg }},
    ratePerKg: {{ $supply->rate_per_kg }},
    get totalAmount() { return (parseFloat(this.dressedKg||0)*parseFloat(this.ratePerKg||0)).toFixed(2); }
}">
  <div class="flex items-center gap-3">
    <a href="{{ route('supply.show',$supply) }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div><h1 class="text-2xl font-bold text-slate-900">Edit Supply Order</h1><p class="text-sm text-slate-500">{{ $supply->invoice_number }}</p></div>
  </div>

  <form method="POST" action="{{ route('supply.update',$supply) }}" class="space-y-5">
    @csrf
    @method('PUT')
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Order Details</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Customer <span class="text-red-500">*</span></label>
          <select name="customer_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="">— Select Customer —</option>
            @foreach($customers as $c)<option value="{{ $c->id }}" {{ old('customer_id',$supply->customer_id)==$c->id?'selected':'' }}>{{ $c->name }} ({{ ucfirst($c->type) }})</option>@endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
          <input type="date" name="date" value="{{ old('date', $supply->date->format('Y-m-d')) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Chicken Type <span class="text-red-500">*</span></label>
          <select name="chicken_type_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="">— Select Type —</option>
            @foreach($chickenTypes as $t)<option value="{{ $t->id }}" {{ old('chicken_type_id',$supply->chicken_type_id)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Weight &amp; Pricing</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Dressed Weight (kg) <span class="text-red-500">*</span></label>
          <input type="number" name="dressed_weight_kg" x-model="dressedKg" value="{{ old('dressed_weight_kg', $supply->dressed_weight_kg) }}" step="0.001" min="0.001" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Rate per kg <span class="text-red-500">*</span></label>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
          <input type="number" name="rate_per_kg" x-model="ratePerKg" value="{{ old('rate_per_kg', $supply->rate_per_kg) }}" step="0.01" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none"></div>
        </div>
      </div>
      <div class="bg-green-50 rounded-lg p-4 border border-green-100 flex items-center justify-between">
        <div><p class="text-sm text-green-700 font-medium">Total Amount</p></div>
        <p class="text-2xl font-bold text-green-700" x-text="'PKR ' + parseFloat(totalAmount).toLocaleString('en-PK',{minimumFractionDigits:0})">PKR 0</p>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Delivery Details</h2>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Address</label>
        <textarea name="delivery_address" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('delivery_address', $supply->delivery_address) }}</textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Notes</label>
        <input type="text" name="delivery_notes" value="{{ old('delivery_notes', $supply->delivery_notes) }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
      <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('notes', $supply->notes) }}</textarea>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Update Order</button>
      <a href="{{ route('supply.show',$supply) }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
    </div>
  </form>
</div>
@endsection
