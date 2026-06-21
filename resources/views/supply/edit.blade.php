@extends('layouts.app')
@section('title','Edit Supply Order')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    dressedKg: {{ $supply->dressed_weight_kg }},
    ratePerKg: {{ $supply->rate_per_kg }},
    get totalAmount() { return (parseFloat(this.dressedKg||0)*parseFloat(this.ratePerKg||0)).toFixed(2); }
}">

  {{-- Header --}}
  <div class="flex items-center gap-3">
    <a href="{{ route('supply.show',$supply) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Edit Supply Order</h1>
      <p class="text-sm text-slate-500">{{ $supply->invoice_number }}</p>
    </div>
  </div>

  @if($errors->any())
  <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
    <ul class="text-sm text-red-700 space-y-0.5">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('supply.update',$supply) }}">
    @csrf @method('PUT')
    <div class="grid grid-cols-3 gap-6">

      {{-- Left column --}}
      <div class="col-span-2 space-y-5">

        {{-- Order Details card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-slate-50">
            <span class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center">
              <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-slate-600"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">Order Details</p>
              <p class="text-xs text-slate-500">Customer and date information</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none bg-white">
                  <option value="">— Select Customer —</option>
                  @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id',$supply->customer_id)==$c->id?'selected':'' }}>{{ $c->name }} ({{ ucfirst($c->type) }})</option>
                  @endforeach
                </select>
                @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', $supply->date->format('Y-m-d')) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>
          </div>
        </div>

        {{-- Weight & Pricing card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-green-50">
            <span class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center">
              <i data-lucide="scale" class="w-3.5 h-3.5 text-green-700"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">Weight & Pricing</p>
              <p class="text-xs text-slate-500">Dressed weight and rate per kg</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Dressed Weight (kg) <span class="text-red-500">*</span></label>
                <input type="number" name="dressed_weight_kg" x-model="dressedKg" value="{{ old('dressed_weight_kg', $supply->dressed_weight_kg) }}" step="0.001" min="0.001" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('dressed_weight_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Rate per kg <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">PKR</span>
                  <input type="number" name="rate_per_kg" x-model="ratePerKg" value="{{ old('rate_per_kg', $supply->rate_per_kg) }}" step="0.01" min="0" required class="w-full pl-14 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                </div>
                @error('rate_per_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>
          </div>
        </div>

        {{-- Delivery Details card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-blue-50">
            <span class="w-7 h-7 rounded-lg bg-blue-200 flex items-center justify-center">
              <i data-lucide="truck" class="w-3.5 h-3.5 text-blue-700"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">Delivery Details</p>
              <p class="text-xs text-slate-500">Optional delivery address and notes</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Delivery Address</label>
              <textarea name="delivery_address" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('delivery_address', $supply->delivery_address) }}</textarea>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Delivery Notes</label>
              <input type="text" name="delivery_notes" value="{{ old('delivery_notes', $supply->delivery_notes) }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
            </div>
          </div>
        </div>

      </div>

      {{-- Right column --}}
      <div class="space-y-4">

        {{-- Live Total summary card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-green-50">
            <span class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center">
              <i data-lucide="calculator" class="w-3.5 h-3.5 text-green-700"></i>
            </span>
            <p class="text-sm font-semibold text-slate-800">Order Total</p>
          </div>
          <div class="p-5 text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Amount</p>
            <p class="text-3xl font-bold text-green-700" x-text="'PKR ' + parseFloat(totalAmount).toLocaleString('en-PK',{minimumFractionDigits:0})">PKR 0</p>
            <p class="text-xs text-slate-400 mt-2" x-text="dressedKg + ' kg × PKR ' + ratePerKg + '/kg'"></p>
          </div>
        </div>

        {{-- Notes card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-yellow-50">
            <span class="w-7 h-7 rounded-lg bg-yellow-200 flex items-center justify-center">
              <i data-lucide="sticky-note" class="w-3.5 h-3.5 text-yellow-700"></i>
            </span>
            <p class="text-sm font-semibold text-slate-800">Notes</p>
          </div>
          <div class="p-5">
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Internal notes</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('notes', $supply->notes) }}</textarea>
          </div>
        </div>

        {{-- Actions --}}
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
          <i data-lucide="check-circle" class="w-4 h-4"></i> Update Order
        </button>
        <a href="{{ route('supply.show',$supply) }}" class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center transition-colors">
          Cancel
        </a>

      </div>
    </div>
  </form>
</div>
@endsection
