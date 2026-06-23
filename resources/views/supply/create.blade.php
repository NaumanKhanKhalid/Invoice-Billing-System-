@extends('layouts.app')
@section('title','New Supply Order')
@section('content')
<div class="space-y-6" x-data="{
    dressedKg: '{{ old('dressed_weight_kg', '') }}',
    ratePerKg: '{{ old('rate_per_kg', '') }}',
    get totalAmount() { return (parseFloat(this.dressedKg||0)*parseFloat(this.ratePerKg||0)).toFixed(2); }
}">

  {{-- Header --}}
  <div class="flex items-center gap-3">
    <a href="{{ route('supply.index') }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-slate-700 hover:border-slate-300 transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">New Supply Order</h1>
      <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $nextNumber }}</p>
    </div>
  </div>

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 flex items-start gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form method="POST" action="{{ route('supply.store') }}" class="space-y-4">
    @csrf

    {{-- Top row: Customer + Date | Weight & Rate + Total --}}
    <div class="grid grid-cols-3 gap-4">

      {{-- Left 2 cols: Order Info --}}
      <div class="col-span-2 space-y-4">

        {{-- Customer & Date --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b border-slate-100 bg-slate-50">
            <div class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
              <i data-lucide="building-2" class="w-3.5 h-3.5 text-green-700"></i>
            </div>
            <p class="text-sm font-semibold text-slate-800">Order Details</p>
          </div>
          <div class="p-5 grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Customer <span class="text-red-500">*</span></label>
              <select name="customer_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
                <option value="">— Select Customer —</option>
                @foreach($customers as $type => $group)
                <optgroup label="{{ ucfirst($type) }}">
                  @foreach($group as $c)
                  <option value="{{ $c->id }}" {{ old('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                  @endforeach
                </optgroup>
                @endforeach
              </select>
              @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Order Date <span class="text-red-500">*</span></label>
              <input type="date" name="date" value="{{ old('date', today()->toDateString()) }}" required
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Delivery Date <span class="text-slate-400 font-normal">(if different from order date)</span></label>
              <input type="date" name="delivery_date" value="{{ old('delivery_date') }}"
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              <p class="text-[11px] text-slate-400 mt-1">Leave blank if delivering today</p>
            </div>
          </div>
        </div>

        {{-- Weight & Rate --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b border-green-100 bg-green-50">
            <div class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center flex-shrink-0">
              <i data-lucide="scale" class="w-3.5 h-3.5 text-green-700"></i>
            </div>
            <p class="text-sm font-semibold text-green-900">Weight & Pricing</p>
          </div>
          <div class="p-5 grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Dressed Weight (kg) <span class="text-red-500">*</span></label>
              <input type="number" name="dressed_weight_kg" x-model="dressedKg"
                     step="0.001" min="0.001" required
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              @error('dressed_weight_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Rate per kg <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">PKR</span>
                <input type="number" name="rate_per_kg" x-model="ratePerKg"
                       step="0.01" min="0" required
                       class="w-full pl-14 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              </div>
              @error('rate_per_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
          </div>
        </div>

        {{-- Delivery --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b border-slate-100 bg-slate-50">
            <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0">
              <i data-lucide="truck" class="w-3.5 h-3.5 text-slate-600"></i>
            </div>
            <p class="text-sm font-semibold text-slate-800">Delivery Details <span class="text-xs font-normal text-slate-400">(optional)</span></p>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Delivery Address</label>
              <textarea name="delivery_address" rows="2" placeholder="Street, area, city..."
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none">{{ old('delivery_address') }}</textarea>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Delivery Notes</label>
              <input type="text" name="delivery_notes" value="{{ old('delivery_notes') }}" placeholder="Time, instructions..."
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
            </div>
          </div>
        </div>
      </div>

      {{-- Right col: Summary + Notes --}}
      <div class="space-y-4">

        {{-- Live Total Preview --}}
        <div class="bg-white rounded-xl border border-green-200 shadow-sm overflow-hidden">
          <div class="bg-green-600 px-5 py-4 text-center">
            <p class="text-green-100 text-xs font-medium uppercase tracking-wider mb-1">Total Amount</p>
            <p class="text-3xl font-bold text-white" x-text="'PKR ' + parseFloat(totalAmount).toLocaleString('en-PK', {minimumFractionDigits:0})">PKR 0</p>
            <p class="text-green-200 text-xs mt-1" x-text="(dressedKg||'0') + ' kg × PKR ' + (ratePerKg||'0') + '/kg'"></p>
          </div>
          <div class="p-4 space-y-2 text-sm">
            <div class="flex justify-between text-slate-500">
              <span>Dressed Weight</span>
              <span class="font-medium text-slate-800" x-text="(parseFloat(dressedKg||0)).toLocaleString() + ' kg'">— kg</span>
            </div>
            <div class="flex justify-between text-slate-500">
              <span>Rate / kg</span>
              <span class="font-medium text-slate-800" x-text="'PKR ' + parseFloat(ratePerKg||0).toLocaleString()">PKR —</span>
            </div>
          </div>
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b border-slate-100 bg-slate-50">
            <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0">
              <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-600"></i>
            </div>
            <p class="text-sm font-semibold text-slate-800">Notes</p>
          </div>
          <div class="p-4">
            <textarea name="notes" rows="4" placeholder="Any special notes..."
                      class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none">{{ old('notes') }}</textarea>
          </div>
        </div>

        {{-- Actions --}}
        <div class="space-y-2">
          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Save Order
          </button>
          <a href="{{ route('supply.index') }}"
             class="w-full inline-flex items-center justify-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 py-2.5 rounded-xl text-sm font-medium transition-colors">
            Cancel
          </a>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
