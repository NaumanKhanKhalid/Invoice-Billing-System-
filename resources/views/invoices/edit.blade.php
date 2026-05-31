@extends('layouts.app')
@section('title', 'Edit Invoice')

@section('content')
<div class="max-w-5xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('invoices.show', $invoice) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Invoice</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ $invoice->invoice_number }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf @method('PUT')

        <div x-data="invoiceForm(@json($products->values()), @json($invoice->items))" class="space-y-5">

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Client <span class="text-red-500">*</span></label>
                    <select name="client_id" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none bg-white">
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ $invoice->client_id == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}{{ $client->company_name ? ' ('.$client->company_name.')' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Number</label>
                    <input type="text" value="{{ $invoice->invoice_number }}" readonly
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 text-slate-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Issue Date <span class="text-red-500">*</span></label>
                    <input type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Due Date <span class="text-red-500">*</span></label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-900">Line Items</h2>
                    <button type="button" @click="addItem"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Add Row
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase w-40">Product</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase w-20">Qty</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase w-28">Unit Price</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase w-20">Tax %</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase w-28">Amount</th>
                                <th class="px-4 py-2.5 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="border-b border-slate-50">
                                    <td class="px-4 py-2">
                                        <input type="text" :name="`items[${index}][description]`" x-model="item.description" required
                                               class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-indigo-300 outline-none">
                                    </td>
                                    <td class="px-4 py-2">
                                        <select :name="`items[${index}][product_id]`" x-model="item.product_id"
                                                @change="fillFromProduct(index)"
                                                class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-indigo-300 outline-none bg-white">
                                            <option value="">Manual</option>
                                            <template x-for="p in products" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity"
                                               @input="calcLine(index)" min="0.01" step="0.01" required
                                               class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-indigo-300 outline-none">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price"
                                               @input="calcLine(index)" min="0" step="0.01" required
                                               class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-indigo-300 outline-none">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" :name="`items[${index}][tax_rate]`" x-model.number="item.tax_rate"
                                               @input="calcLine(index)" min="0" max="100" step="0.01"
                                               class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-indigo-300 outline-none">
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="text-sm font-medium text-slate-900" x-text="'PKR ' + item.amount.toLocaleString('en-PK', {minimumFractionDigits:2})"></span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <button type="button" @click="removeItem(index)"
                                                class="text-red-400 hover:text-red-600 transition-colors"
                                                x-show="items.length > 1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-slate-100 flex justify-end">
                    <div class="w-72 space-y-2">
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span x-text="'PKR ' + subtotal.toLocaleString('en-PK', {minimumFractionDigits:2})"></span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Tax</span>
                            <span x-text="'PKR ' + taxAmount.toLocaleString('en-PK', {minimumFractionDigits:2})"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>Discount</span>
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-slate-400">PKR</span>
                                <input type="number" name="discount_amount" x-model.number="discount"
                                       @input="calcTotals()" min="0" step="0.01"
                                       class="w-24 px-2 py-1 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-indigo-300 outline-none text-right">
                            </div>
                        </div>
                        <div class="flex justify-between font-bold text-slate-900 pt-2 border-t border-slate-200 text-base">
                            <span>Total</span>
                            <span x-text="'PKR ' + total.toLocaleString('en-PK', {minimumFractionDigits:2})"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none resize-none">{{ old('notes', $invoice->notes) }}</textarea>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Terms & Conditions</label>
                    <textarea name="terms" rows="3"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none resize-none">{{ old('terms', $invoice->terms) }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pb-6">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors text-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Update Invoice
                </button>
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="px-5 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function invoiceForm(products, existingItems) {
    return {
        products: products,
        items: existingItems.length ? existingItems.map(i => ({
            description: i.description,
            product_id: i.product_id || '',
            quantity: parseFloat(i.quantity),
            unit_price: parseFloat(i.unit_price),
            tax_rate: parseFloat(i.tax_rate),
            amount: parseFloat(i.amount),
        })) : [{ description: '', product_id: '', quantity: 1, unit_price: 0, tax_rate: 0, amount: 0 }],
        discount: {{ $invoice->discount_amount }},
        subtotal: 0,
        taxAmount: 0,
        total: 0,

        addItem() {
            this.items.push({ description: '', product_id: '', quantity: 1, unit_price: 0, tax_rate: 0, amount: 0 });
            this.$nextTick(() => lucide.createIcons());
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calcTotals();
            }
        },

        fillFromProduct(index) {
            const item = this.items[index];
            const product = this.products.find(p => p.id == item.product_id);
            if (product) {
                item.description = product.name;
                item.unit_price = parseFloat(product.unit_price);
                item.tax_rate = parseFloat(product.tax_rate);
                this.calcLine(index);
            }
        },

        calcLine(index) {
            const item = this.items[index];
            const qty = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.unit_price) || 0;
            const tax = parseFloat(item.tax_rate) || 0;
            const lineAmt = qty * price;
            item.amount = lineAmt + (lineAmt * tax / 100);
            this.calcTotals();
        },

        calcTotals() {
            let sub = 0, tax = 0;
            for (const item of this.items) {
                const qty = parseFloat(item.quantity) || 0;
                const price = parseFloat(item.unit_price) || 0;
                const taxRate = parseFloat(item.tax_rate) || 0;
                const line = qty * price;
                sub += line;
                tax += line * taxRate / 100;
            }
            this.subtotal = sub;
            this.taxAmount = tax;
            this.total = Math.max(0, sub + tax - (parseFloat(this.discount) || 0));
        },

        init() {
            this.calcTotals();
            lucide.createIcons();
        }
    }
}
</script>
@endpush
