@extends('layouts.app')
@section('title','Edit Expense')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex items-center gap-3">
    <a href="{{ route('expenses.index') }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Edit Expense</h1>
      <p class="text-sm text-slate-500">{{ $expense->date->format('d M Y') }} — {{ $expense->category }}</p>
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

  <form method="POST" action="{{ route('expenses.update',$expense) }}">
    @csrf @method('PUT')
    <div class="grid grid-cols-3 gap-6">

      {{-- Left column --}}
      <div class="col-span-2 space-y-5">

        {{-- Expense Details card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-slate-50">
            <span class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center">
              <i data-lucide="receipt" class="w-3.5 h-3.5 text-slate-600"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">Expense Details</p>
              <p class="text-xs text-slate-500">Date, category, and description</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Category <span class="text-red-500">*</span></label>
                <input type="text" name="category" value="{{ old('category', $expense->category) }}" required list="categories"
                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                <datalist id="categories">
                  <option value="Fuel"><option value="Utilities"><option value="Rent">
                  <option value="Labour"><option value="Ice"><option value="Packaging">
                  <option value="Maintenance"><option value="Transport"><option value="Miscellaneous">
                </datalist>
                @error('category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Description <span class="text-red-500">*</span></label>
              <input type="text" name="description" value="{{ old('description', $expense->description) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
          </div>
        </div>

        {{-- Payment Details card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-green-50">
            <span class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center">
              <i data-lucide="banknote" class="w-3.5 h-3.5 text-green-700"></i>
            </span>
            <div>
              <p class="text-sm font-semibold text-slate-800">Payment Details</p>
              <p class="text-xs text-slate-500">Amount, payee, and receipt</p>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Amount <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">PKR</span>
                  <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required
                    class="w-full pl-14 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                </div>
                @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Paid To</label>
                <input type="text" name="paid_to" value="{{ old('paid_to', $expense->paid_to) }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Receipt #</label>
                <input type="text" name="receipt_number" value="{{ old('receipt_number', $expense->receipt_number) }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              </div>
            </div>
          </div>
        </div>

      </div>

      {{-- Right column --}}
      <div class="space-y-4">

        {{-- Current amount summary --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-green-50">
            <span class="w-7 h-7 rounded-lg bg-green-200 flex items-center justify-center">
              <i data-lucide="banknote" class="w-3.5 h-3.5 text-green-700"></i>
            </span>
            <p class="text-sm font-semibold text-slate-800">Current Amount</p>
          </div>
          <div class="p-5 text-center">
            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Recorded Amount</p>
            <p class="text-2xl font-bold text-green-700">PKR {{ number_format($expense->amount, 0) }}</p>
            <p class="text-xs text-slate-400 mt-2">{{ $expense->category }}</p>
          </div>
        </div>

        {{-- Actions --}}
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
          <i data-lucide="check-circle" class="w-4 h-4"></i> Update Expense
        </button>
        <a href="{{ route('expenses.index') }}" class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center transition-colors">
          Cancel
        </a>

      </div>
    </div>
  </form>
</div>
@endsection
