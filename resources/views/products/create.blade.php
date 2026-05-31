@extends('layouts.app')
@section('title', 'Add Product')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('products.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Add Product / Service</h1>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 text-sm border @error('name') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none"
                       placeholder="Web Development">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="2"
                          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none resize-none"
                          placeholder="Brief description of this product or service">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Unit Price (PKR) <span class="text-red-500">*</span></label>
                    <input type="number" name="unit_price" value="{{ old('unit_price') }}" required step="0.01" min="0"
                           class="w-full px-3 py-2 text-sm border @error('unit_price') border-red-400 @else border-slate-200 @enderror rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none"
                           placeholder="5000.00">
                    @error('unit_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                    <input type="text" name="unit" value="{{ old('unit', 'item') }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none"
                           placeholder="hour / item / month">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" value="{{ old('tax_rate', 0) }}" step="0.01" min="0" max="100"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                    <div class="w-10 h-5 bg-slate-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:bg-indigo-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                    <span class="ml-2 text-sm font-medium text-slate-700">Active (available in invoices)</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors text-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Product
                </button>
                <a href="{{ route('products.index') }}"
                   class="px-5 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
