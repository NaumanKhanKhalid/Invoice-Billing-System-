@extends('layouts.app')
@section('title','New Purchase')
@section('content')
<div class="max-w-3xl mx-auto space-y-5" x-data="{
    liveKg: 0,
    doa: 0,
    ratePerKg: 0,
    get totalAmount() { return (parseFloat(this.liveKg||0)*parseFloat(this.ratePerKg||0)).toFixed(2); },
    setRateFromType(typeId) {
        if(!typeId) return;
        fetch('{{ route('daily-rates.today') }}')
            .then(r=>r.json())
            .then(rates=>{
                var rate = rates.find(r=>r.chicken_type_id==typeId);
                if(rate) this.ratePerKg = rate.live_rate_per_kg;
            });
    }
}">
  <div class="flex items-center gap-3">
    <a href="{{ route('purchases.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div><h1 class="text-2xl font-bold text-slate-900">New Purchase Order</h1><p class="text-sm text-slate-500">Record chicken purchase from supplier</p></div>
  </div>

  <form method="POST" action="{{ route('purchases.store') }}" class="space-y-5">
    @csrf
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <h2 class="font-semibold text-slate-900 text-base border-b border-slate-100 pb-3">Purchase Details</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Supplier <span class="text-red-500">*</span></label>
          <select name="supplier_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none bg-white">
            <option value="">— Select Supplier —</option>
            @foreach($suppliers as $s)<option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }} ({{ $s->credit_days }}d)</option>@endforeach
          </select>
          @error('supplier_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
          <input type="date" name="date" value="{{ old('date', today()->toDateString()) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
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
      <h2 class="font-semibold text-slate-900 text-base border-b border-slate-100 pb-3">Weight & Rate</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Live Weight (kg) <span class="text-red-500">*</span></label>
          <input type="number" name="live_weight_kg" x-model="liveKg" value="{{ old('live_weight_kg') }}" step="0.001" min="0.001" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          @error('live_weight_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Dead on Arrival (kg)</label>
          <input type="number" name="dead_on_arrival_kg" x-model="doa" value="{{ old('dead_on_arrival_kg', 0) }}" step="0.001" min="0" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          <p class="text-xs text-slate-400 mt-1">Optional — supplier ne mari hui murgi di</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Rate per kg (Live) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
            <input type="number" name="rate_per_kg_live" x-model="ratePerKg" value="{{ old('rate_per_kg_live') }}" step="0.01" min="0" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
          </div>
          <p class="text-xs text-slate-400 mt-1">Auto-filled from today's live rate</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 border border-green-100 flex flex-col justify-center">
          <p class="text-xs text-green-600 font-medium uppercase tracking-wider">Total Amount</p>
          <p class="text-2xl font-bold text-green-700 mt-1" x-text="'PKR ' + parseFloat(totalAmount).toLocaleString('en-PK',{minimumFractionDigits:0})">PKR 0</p>
          <p class="text-xs text-green-500 mt-0.5" x-text="liveKg + ' kg × PKR ' + ratePerKg + '/kg'"></p>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">{{ old('notes') }}</textarea>
      </div>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Save Purchase Order</button>
      <a href="{{ route('purchases.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
    </div>
  </form>
</div>
@endsection
