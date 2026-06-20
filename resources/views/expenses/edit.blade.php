@extends('layouts.app')
@section('title','Edit Expense')
@section('content')
<div class="max-w-xl mx-auto space-y-5">
  <div class="flex items-center gap-3">
    <a href="{{ route('expenses.index') }}" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div><h1 class="text-2xl font-bold text-slate-900">Edit Expense</h1></div>
  </div>

  <form method="POST" action="{{ route('expenses.update',$expense) }}" class="space-y-5">
    @csrf @method('PUT')
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
          <input type="date" name="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Category <span class="text-red-500">*</span></label>
          <input type="text" name="category" value="{{ old('category', $expense->category) }}" required list="categories" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
          <datalist id="categories"><option value="Fuel"><option value="Utilities"><option value="Rent"><option value="Labour"><option value="Ice"><option value="Packaging"><option value="Maintenance"><option value="Transport"><option value="Miscellaneous"></datalist>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1">Description <span class="text-red-500">*</span></label>
          <input type="text" name="description" value="{{ old('description', $expense->description) }}" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Amount (PKR) <span class="text-red-500">*</span></label>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">PKR</span>
          <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required class="w-full pl-12 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"></div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Paid To</label>
          <input type="text" name="paid_to" value="{{ old('paid_to', $expense->paid_to) }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Receipt #</label>
          <input type="text" name="receipt_number" value="{{ old('receipt_number', $expense->receipt_number) }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
        </div>
      </div>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Update Expense</button>
      <a href="{{ route('expenses.index') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</a>
    </div>
  </form>
</div>
@endsection
