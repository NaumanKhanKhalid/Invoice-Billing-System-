<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        $prefix = Setting::getValue('invoice_prefix', 'INV');
        $year = date('Y');

        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            // Extract sequence from last invoice number like INV-2024-0015
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastSeq = (int) end($parts);
            $sequence = $lastSeq + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    public function calculateTotals(array $items, float $discount = 0): array
    {
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $lineAmount = $qty * $price;
            $subtotal += $lineAmount;
            $taxAmount += $lineAmount * ($taxRate / 100);
        }

        $total = $subtotal + $taxAmount - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => round(max(0, $total), 2),
        ];
    }

    public function updateStatusIfPaid(Invoice $invoice): void
    {
        $invoice->load('payments');
        $amountDue = $invoice->total - $invoice->payments->sum('amount');
        if ($amountDue <= 0 && in_array($invoice->status, ['sent', 'overdue'])) {
            $invoice->update(['status' => 'paid']);
        }
    }
}
