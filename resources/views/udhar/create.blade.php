@extends('layouts.app')
@section('title','New Udhar')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    amount: 0,
    dueDays: 7,
    saleDate: '{{ today()->toDateString() }}',
    get dueDate() {
        if (!this.saleDate || !this.dueDays) return '';
        const d = new Date(this.saleDate);
        d.setDate(d.getDate() + parseInt(this.dueDays || 0));
        return d.toLocaleDateString('en-PK', { day:'2-digit', month:'short', year:'numeric' });
    }
}">

  {{-- Header --}}
  <div class="flex items-center gap-3">
    <a href="{{ route('udhar.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">New Udhar Record</h1>
      <p class="text-sm text-slate-500">Record a credit sale</p>
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

  <form method="POST" action="{{ route('udhar.store') }}">
    @csrf
    <div class="grid grid-cols-3 gap-6">

      {{-- Left: Udhar Details --}}
      <div class="col-span-2 space-y-5">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-slate-50">
            <span class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center">
              <i data-lucide="user" class="w-3.5 h-3.5 text-slate-600"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">Udhar Details</p>
              <p class="text-xs text-slate-500">Customer and credit information</p>
            </div>
          </div>
          <div class="p-5 space-y-4">

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Customer Name <span class="text-red-500">*</span></label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                       placeholder="e.g. Ahmed Bhai">
                @error('customer_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Phone <span class="text-slate-400 font-normal">(WhatsApp)</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                       placeholder="03001234567">
                <p class="text-xs text-slate-400 mt-1">Used for WhatsApp reminder</p>
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Description <span class="text-slate-400 font-normal">(what they took)</span></label>
              <input type="text" name="description" value="{{ old('description') }}"
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                     placeholder="e.g. 5kg murgi">
              @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Amount (PKR) <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">PKR</span>
                  <input type="number" name="amount" x-model="amount" value="{{ old('amount') }}" step="0.01" min="1" required
                         class="w-full pl-14 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                </div>
                @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Due in Days <span class="text-red-500">*</span></label>
                <input type="number" name="due_days" x-model="dueDays" value="{{ old('due_days', 7) }}" min="1" required
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                <p class="text-xs text-slate-400 mt-1">Due date: <span class="font-medium text-slate-600" x-text="dueDate"></span></p>
                @error('due_days')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Sale Date <span class="text-red-500">*</span></label>
              <input type="date" name="sale_date" x-model="saleDate" value="{{ old('sale_date', today()->toDateString()) }}" required
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              @error('sale_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
              <textarea name="notes" rows="3" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">{{ old('notes') }}</textarea>
              @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

          </div>
        </div>
      </div>

      {{-- Right: Summary --}}
      <div class="space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-green-50">
            <span class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center">
              <i data-lucide="calculator" class="w-3.5 h-3.5 text-green-700"></i>
            </span>
            <p class="text-sm font-semibold text-slate-800">Summary</p>
          </div>
          <div class="p-5 space-y-4">
            <div class="text-center">
              <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Udhar Amount</p>
              <p class="text-3xl font-bold text-red-600" x-text="'PKR ' + parseFloat(amount||0).toLocaleString('en-PK', {minimumFractionDigits:0})">PKR 0</p>
            </div>
            <div class="border-t border-slate-100 pt-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500">Due in</span>
                <span class="font-medium text-slate-900" x-text="dueDays + ' days'"></span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500">Due Date</span>
                <span class="font-medium text-slate-900" x-text="dueDate"></span>
              </div>
            </div>
          </div>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
          <i data-lucide="check-circle" class="w-4 h-4"></i> Save Udhar Record
        </button>
        <a href="{{ route('udhar.index') }}" class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center transition-colors">
          Cancel
        </a>
      </div>

    </div>
  </form>
</div>
@endsection
