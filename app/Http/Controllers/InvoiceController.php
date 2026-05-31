<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private PdfService $pdfService
    ) {}

    public function index(Request $request)
    {
        $query = Invoice::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('from_date')) {
            $query->where('issue_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('issue_date', '<=', $request->to_date);
        }

        $invoices = $query->paginate(15)->withQueryString();
        $clients = Client::active()->orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'clients'));
    }

    public function create()
    {
        $clients = Client::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $invoiceNumber = $this->invoiceService->generateInvoiceNumber();

        return view('invoices.create', compact('clients', 'products', 'invoiceNumber'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $items = $data['items'];
        $discount = (float) ($data['discount_amount'] ?? 0);

        $totals = $this->invoiceService->calculateTotals($items, $discount);

        $invoice = Invoice::create([
            'user_id' => auth()->id(),
            'client_id' => $data['client_id'],
            'invoice_number' => $this->invoiceService->generateInvoiceNumber(),
            'status' => $request->input('action') === 'send' ? 'sent' : 'draft',
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'discount_amount' => $discount,
            'total' => $totals['total'],
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
        ]);

        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $lineAmount = $qty * $price;
            $taxAmount = $lineAmount * ($taxRate / 100);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'tax_rate' => $taxRate,
                'amount' => $lineAmount + $taxAmount,
            ]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items.product', 'payments', 'user']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if (!in_array($invoice->status, ['draft', 'sent'])) {
            return back()->with('error', 'Only draft or sent invoices can be edited.');
        }

        $clients = Client::active()->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'clients', 'products'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $data = $request->validated();
        $items = $data['items'];
        $discount = (float) ($data['discount_amount'] ?? 0);

        $totals = $this->invoiceService->calculateTotals($items, $discount);

        $invoice->update([
            'client_id' => $data['client_id'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'discount_amount' => $discount,
            'total' => $totals['total'],
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
        ]);

        $invoice->items()->delete();

        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $lineAmount = $qty * $price;
            $taxAmount = $lineAmount * ($taxRate / 100);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'tax_rate' => $taxRate,
                'amount' => $lineAmount + $taxAmount,
            ]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function markAsSent(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);
        return back()->with('success', 'Invoice marked as sent.');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update(['status' => 'paid']);
        return back()->with('success', 'Invoice marked as paid.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $pdf = $this->pdfService->generate($invoice);
        $filename = 'Invoice-' . $invoice->invoice_number . '.pdf';
        return $pdf->download($filename);
    }

    public function duplicate(Invoice $invoice)
    {
        $newInvoice = $invoice->replicate();
        $newInvoice->invoice_number = $this->invoiceService->generateInvoiceNumber();
        $newInvoice->status = 'draft';
        $newInvoice->issue_date = now()->toDateString();
        $newInvoice->due_date = now()->addDays(30)->toDateString();
        $newInvoice->save();

        foreach ($invoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $newInvoice->id;
            $newItem->save();
        }

        return redirect()->route('invoices.show', $newInvoice)->with('success', 'Invoice duplicated successfully.');
    }
}
