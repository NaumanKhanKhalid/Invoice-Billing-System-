<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;

class PaymentController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        $data = $request->validated();
        $data['invoice_id'] = $invoice->id;

        Payment::create($data);

        $this->invoiceService->updateStatusIfPaid($invoice);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();

        // Recalculate: if status was paid but now has amount due, revert to sent
        $invoice->load('payments');
        $amountDue = $invoice->total - $invoice->payments->sum('amount');
        if ($amountDue > 0 && $invoice->status === 'paid') {
            $invoice->update(['status' => 'sent']);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment deleted successfully.');
    }
}
