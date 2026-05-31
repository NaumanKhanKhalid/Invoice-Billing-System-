<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $statuses = ['draft', 'sent', 'paid', 'paid', 'paid', 'sent', 'overdue', 'overdue', 'cancelled', 'paid'];

        for ($i = 1; $i <= 20; $i++) {
            $clientId = (($i - 1) % 10) + 1;
            $status = $statuses[($i - 1) % count($statuses)];
            $issueDate = now()->subDays(rand(5, 90));
            $dueDate = (clone $issueDate)->addDays(30);

            $selectedProducts = $products->random(rand(2, 4));
            $subtotal = 0;
            $taxAmount = 0;
            $items = [];

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 10);
                $price = $product->unit_price;
                $taxRate = $product->tax_rate;
                $lineAmt = $qty * $price;
                $lineTax = $lineAmt * $taxRate / 100;
                $amount = $lineAmt + $lineTax;
                $subtotal += $lineAmt;
                $taxAmount += $lineTax;
                $items[] = [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'amount' => $amount,
                ];
            }

            $discount = $i % 5 === 0 ? 500 : 0;
            $total = max(0, $subtotal + $taxAmount - $discount);

            $year = $issueDate->year;
            $seq = str_pad($i, 4, '0', STR_PAD_LEFT);
            $invoiceNumber = "INV-{$year}-{$seq}";

            $invoice = Invoice::create([
                'user_id' => 1,
                'client_id' => $clientId,
                'invoice_number' => $invoiceNumber,
                'status' => $status,
                'issue_date' => $issueDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => $discount,
                'total' => round($total, 2),
                'notes' => 'Thank you for your business!',
                'terms' => 'Payment is due within 30 days of invoice date.',
            ]);

            foreach ($items as $item) {
                InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
            }
        }
    }
}
