@extends('layouts.app')
@section('title', $invoice->invoice_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="{ paymentModal: false }">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $invoice->invoice_number }}</h1>
                    <x-badge :status="$invoice->status" />
                </div>
                <p class="text-sm text-slate-500 mt-0.5">{{ $invoice->client->name }} — {{ $invoice->issue_date->format('d M Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($invoice->status === 'draft')
            <a href="{{ route('invoices.edit', $invoice) }}"
               class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-3 py-2 rounded-lg text-sm">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit
            </a>
            <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-2 rounded-lg text-sm">
                    <i data-lucide="send" class="w-4 h-4"></i> Mark as Sent
                </button>
            </form>
            @endif

            @if(in_array($invoice->status, ['sent', 'overdue']))
            <button @click="paymentModal = true"
                    class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-3 py-2 rounded-lg text-sm">
                <i data-lucide="credit-card" class="w-4 h-4"></i> Record Payment
            </button>
            <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-3 py-2 rounded-lg text-sm">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> Mark Paid
                </button>
            </form>
            @endif

            <a href="{{ route('invoices.pdf', $invoice) }}"
               class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-3 py-2 rounded-lg text-sm">
                <i data-lucide="download" class="w-4 h-4"></i> PDF
            </a>

            <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 font-medium px-3 py-2 rounded-lg text-sm">
                    <i data-lucide="copy" class="w-4 h-4"></i> Duplicate
                </button>
            </form>

            @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                  onsubmit="return confirm('Delete this invoice permanently?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 font-medium px-3 py-2 rounded-lg text-sm">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Invoice body -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Company + Client header -->
        <div class="p-6 grid grid-cols-2 gap-6 border-b border-slate-100">
            <div>
                <p class="font-bold text-xl text-slate-900 mb-1">InvoicePro</p>
                <p class="text-sm text-slate-500">Your Company Name</p>
                <p class="text-sm text-slate-500">Karachi, Pakistan</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-slate-900">{{ $invoice->client->name }}</p>
                @if($invoice->client->company_name)
                <p class="text-sm text-slate-500">{{ $invoice->client->company_name }}</p>
                @endif
                <p class="text-sm text-slate-500">{{ $invoice->client->email }}</p>
                @if($invoice->client->phone)
                <p class="text-sm text-slate-500">{{ $invoice->client->phone }}</p>
                @endif
                @if($invoice->client->address)
                <p class="text-sm text-slate-500">{{ $invoice->client->address }}</p>
                @endif
            </div>
        </div>

        <!-- Invoice meta -->
        <div class="px-6 py-4 grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50 border-b border-slate-100">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase">Invoice #</p>
                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $invoice->invoice_number }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase">Issue Date</p>
                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $invoice->issue_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase">Due Date</p>
                <p class="text-sm font-semibold {{ $invoice->status === 'overdue' ? 'text-red-600' : 'text-slate-800' }} mt-0.5">{{ $invoice->due_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase">Amount Due</p>
                <p class="text-sm font-bold {{ $invoice->amount_due > 0 ? 'text-red-600' : 'text-emerald-600' }} mt-0.5">PKR {{ number_format($invoice->amount_due, 2) }}</p>
            </div>
        </div>

        <!-- Line items -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase w-20">Qty</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase w-28">Unit Price</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase w-20">Tax %</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase w-28">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-900">{{ $item->description }}</p>
                            @if($item->product)
                            <p class="text-xs text-slate-400">{{ $item->product->name }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right text-slate-600">{{ $item->quantity }}</td>
                        <td class="px-5 py-3.5 text-right text-slate-600">PKR {{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-5 py-3.5 text-right text-slate-600">{{ $item->tax_rate }}%</td>
                        <td class="px-5 py-3.5 text-right font-semibold text-slate-900">PKR {{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="px-5 py-4 border-t border-slate-100 flex justify-end">
            <div class="w-64 space-y-2">
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Subtotal</span>
                    <span>PKR {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Tax</span>
                    <span>PKR {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Discount</span>
                    <span class="text-red-500">- PKR {{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-slate-900 pt-2 border-t border-slate-200 text-base">
                    <span>Total</span>
                    <span>PKR {{ number_format($invoice->total, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Amount Paid</span>
                    <span class="text-emerald-600">PKR {{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold pt-1 border-t border-slate-200 {{ $invoice->amount_due > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                    <span>Amount Due</span>
                    <span>PKR {{ number_format($invoice->amount_due, 2) }}</span>
                </div>
            </div>
        </div>

        @if($invoice->notes || $invoice->terms)
        <div class="px-5 py-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if($invoice->notes)
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Notes</p>
                <p class="text-sm text-slate-600">{{ $invoice->notes }}</p>
            </div>
            @endif
            @if($invoice->terms)
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Terms</p>
                <p class="text-sm text-slate-600">{{ $invoice->terms }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Payments -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Payment History</h2>
            @if(in_array($invoice->status, ['sent', 'overdue']))
            <button @click="paymentModal = true"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Record Payment
            </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Method</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reference</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Amount</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($invoice->payments as $payment)
                    <tr>
                        <td class="px-5 py-3.5 text-slate-600">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $payment->method_label }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $payment->reference_number ?? '—' }}</td>
                        <td class="px-5 py-3.5 font-semibold text-emerald-600">PKR {{ number_format($payment->amount, 2) }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                                  onsubmit="return confirm('Delete this payment?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-red-600 border border-red-100 bg-red-50 px-2.5 py-1.5 rounded-md hover:bg-red-100">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">No payments recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="paymentModal" x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none;">
        <div class="absolute inset-0 bg-black/50" @click="paymentModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-slate-900 text-lg">Record Payment</h3>
                <button @click="paymentModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('payments.store', $invoice) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Amount (PKR) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" value="{{ $invoice->amount_due }}" required step="0.01" min="0.01"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                    <select name="method" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="easypaisa">EasyPaisa</option>
                        <option value="jazzcash">JazzCash</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reference Number</label>
                    <input type="text" name="reference_number"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none"
                           placeholder="Transaction ID, cheque number...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-300 outline-none resize-none"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-lg transition-colors text-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Save Payment
                    </button>
                    <button type="button" @click="paymentModal = false"
                            class="px-4 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
