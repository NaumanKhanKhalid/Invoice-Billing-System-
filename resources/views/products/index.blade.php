@extends('layouts.app')
@section('title', 'Products & Services')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Products & Services</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $products->total() }} total items</p>
        </div>
        <a href="{{ route('products.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-lg transition-colors text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Product
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search products..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
                </div>
            </div>
            <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('status'))
            <a href="{{ route('products.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Product / Service</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tax Rate</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors" id="product-row-{{ $product->id }}">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                            @if($product->description)
                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $product->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-900">PKR {{ number_format($product->unit_price, 2) }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $product->unit }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $product->tax_rate }}%</td>
                        <td class="px-5 py-3.5">
                            <span id="status-badge-{{ $product->id }}">
                                <x-badge :status="$product->is_active ? 'active' : 'inactive'" />
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="toggleStatus({{ $product->id }})"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50 transition-all">
                                    <i data-lucide="toggle-left" class="w-3.5 h-3.5"></i>
                                    <span class="hidden sm:inline">Toggle</span>
                                </button>
                                <a href="{{ route('products.edit', $product) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 border border-slate-200 px-2.5 py-1.5 rounded-md hover:bg-slate-50 transition-all">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span class="hidden sm:inline">Edit</span>
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-red-600 border border-red-100 bg-red-50 px-2.5 py-1.5 rounded-md hover:bg-red-100 transition-all">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-slate-400">
                            <i data-lucide="package" class="w-10 h-10 mx-auto mb-3 text-slate-200"></i>
                            <p class="font-medium">No products found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
async function toggleStatus(id) {
    const res = await fetch(`/products/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        }
    });
    const data = await res.json();
    const badge = document.getElementById(`status-badge-${id}`);
    if (data.is_active) {
        badge.innerHTML = '<span class="badge badge-green"><i data-lucide="circle-check" style="width:12px;height:12px;"></i>Active</span>';
    } else {
        badge.innerHTML = '<span class="badge badge-red"><i data-lucide="circle-x" style="width:12px;height:12px;"></i>Inactive</span>';
    }
    lucide.createIcons({ nodes: [badge] });
}
</script>
@endpush
