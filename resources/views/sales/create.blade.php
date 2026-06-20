@extends('layouts.app')
@section('title','New Sale')
@section('content')
<div class="max-w-3xl mx-auto space-y-5" x-data="{
    dressedKg: 0,
    ratePerKg: 0,
    orderType: 'retail',
    get totalAmount() { return (parseFloat(this.dressedKg||0)*parseFloat(this.ratePerKg||0)).toFixed(2); },
    setRateFromType(typeId) {
        if(!typeId) return;
        fetch('{{ route('daily-rates.today') }}')
            .then(r=>r.json())
            .then(rates=>{
                var rate = rates.find(r=>r.chicken_type_id==typeId);
                if(rate && rate.rate_per_kg_dressed) this.ratePerKg = rate.rate_per_kg_dressed;
            });
    }
}">
  <div class="flex items-center gap-3">
    <a href="{{ route('sales.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div><h1 class="text-2xl font-bold text-slate-900">New Sale</h1><p class="text-sm text-slate-500">Record chicken sale to customer</p></div>
  </div>

  <form method="POST" action="{{ route('sales.store') }}" class="space-y-5">
    @csrf
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Sale Details</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Order Type <span class="text-red-500">*</span></label>
          <select name="order_type" x-model="orderType" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="retail">Retail (Walk-in)</option>
            <option value="supply">Supply (Hotel/Restaurant)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
          <input type="date" name="date" value="{{ old('date', today()->toDateString()) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Customer <span x-show="orderType==='retail'" class="text-slate-400 text-xs">(optional for retail)</span></label>
          <select name="customer_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="">Walk-in / No Customer</option>
            @foreach($customers as $c)<option value="{{ $c->id }}" {{ old('customer_id')==$c->id?'selected':'' }}>{{ $c->name }} ({{ ucfirst($c->type) }})</option>@endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Chicken Type <span class="text-red-500">*</span></label>
          <select name="chicken_type_id" required @change="setRateFromType($event.target.value)" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="">— Select Type —</option>
            @foreach($chickenTypes as $t)<option value="{{ $t->id }}" {{ old('chicken_type_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
          </select>
          @error('chicken_type_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Invoice # <span class="text-slate-400">(auto)</span></label>
          <input type="text" value="{{ $nextNumber }}" disabled class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 text-slate-500 cursor-not-allowed">
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Weight &amp; Pricing</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Dressed Weight (kg) <span class="text-red-500">*</span></label>
          <input type="number" name="dressed_weight_kg" x-model="dressedKg" value="{{ old('dressed_weight_kg') }}" step="0.001" min="0.001" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          @error('dressed_weight_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Rate per kg (Dressed) <span class="text-red-500">*</span></label>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
          <input type="number" name="rate_per_kg" x-model="ratePerKg" value="{{ old('rate_per_kg') }}" step="0.01" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none"></div>
          <p class="text-xs text-slate-400 mt-1">Auto-filled from today's dressed rate</p>
        </div>
      </div>

      <div class="bg-green-50 rounded-lg p-4 border border-green-100 flex items-center justify-between">
        <div>
          <p class="text-sm text-green-700 font-medium">Total Amount</p>
          <p class="text-xs text-green-600 mt-0.5" x-text="dressedKg + ' kg × PKR ' + ratePerKg + '/kg'"></p>
        </div>
        <p class="text-2xl font-bold text-green-700" x-text="'PKR ' + parseFloat(totalAmount).toLocaleString('en-PK',{minimumFractionDigits:0})">PKR 0</p>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4" x-show="orderType==='supply'">
      <h2 class="font-semibold text-slate-900 border-b border-slate-100 pb-3">Delivery Details</h2>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Address</label>
        <textarea name="delivery_address" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('delivery_address') }}</textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Delivery Notes</label>
        <input type="text" name="delivery_notes" value="{{ old('delivery_notes') }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
      <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
      <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('notes') }}</textarea>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Save Sale</button>
      <a href="{{ route('sales.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
    </div>
  </form>
</div>
@endsection
