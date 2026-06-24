@extends('layouts.app')
@section('title','Edit Udhar Record')
@section('content')
<div class="space-y-6">

  <div class="flex items-center gap-3">
    <a href="{{ route('udhar.show', $creditSale) }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-700 shadow-sm">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Edit Udhar Record</h1>
      <p class="text-sm text-slate-500">{{ $creditSale->customer_name }}</p>
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

  <form method="POST" action="{{ route('udhar.update', $creditSale) }}">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="flex items-center gap-2 px-5 py-3 border-b bg-slate-50">
            <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center">
              <i data-lucide="pencil" class="w-3.5 h-3.5 text-slate-600"></i>
            </div>
            <p class="text-sm font-semibold text-slate-800">Edit Details</p>
          </div>
          <div class="p-5 space-y-4">

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Customer Name <span class="text-red-500">*</span></label>
                <input type="text" name="customer_name" value="{{ old('customer_name', $creditSale->customer_name) }}" required
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
                @error('customer_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $creditSale->phone) }}"
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                       placeholder="03001234567">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Description</label>
              <input type="text" name="description" value="{{ old('description', $creditSale->description) }}"
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none"
                     placeholder="e.g. 5kg murgi">
              @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Due Date <span class="text-red-500">*</span></label>
              <input type="date" name="due_date" value="{{ old('due_date', $creditSale->due_date->toDateString()) }}" required
                     class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none">
              @error('due_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-500 mb-1.5">Notes</label>
              <textarea name="notes" rows="3"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-green-300 outline-none resize-none">{{ old('notes', $creditSale->notes) }}</textarea>
              @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

          </div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
          <h2 class="font-semibold text-slate-900 text-sm">Record Summary</h2>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Total Amount</span>
            <span class="font-bold text-slate-900">{{ formatCurrency($creditSale->amount) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Amount Paid</span>
            <span class="font-medium text-green-600">{{ formatCurrency($creditSale->amount_paid) }}</span>
          </div>
          <div class="border-t border-slate-100 pt-3 flex justify-between">
            <span class="font-semibold text-slate-700">Amount Due</span>
            <span class="font-bold text-red-600">{{ formatCurrency($creditSale->amount_due) }}</span>
          </div>
          <p class="text-xs text-slate-400">Note: Amount cannot be changed after payments have been recorded.</p>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
          <i data-lucide="save" class="w-4 h-4"></i>Save Changes
        </button>
        <a href="{{ route('udhar.show', $creditSale) }}" class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 py-2.5 rounded-xl text-sm font-medium flex items-center justify-center transition-colors">
          Cancel
        </a>
      </div>

    </div>
  </form>
</div>
@endsection
