<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $methods = ['cash', 'bank_transfer', 'easypaisa', 'jazzcash', 'cheque'];

        $paidInvoices = Invoice::where('status', 'paid')->get();

        foreach ($paidInvoices as $invoice) {
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
                'payment_date' => $invoice->due_date->subDays(rand(0, 10))->toDateString(),
                'method' => $methods[array_rand($methods)],
                'reference_number' => 'TXN' . strtoupper(substr(md5($invoice->id), 0, 8)),
                'notes' => 'Full payment received.',
            ]);
        }

        // Partial payment for one sent invoice
        $sentInvoice = Invoice::where('status', 'sent')->first();
        if ($sentInvoice) {
            Payment::create([
                'invoice_id' => $sentInvoice->id,
                'amount' => round($sentInvoice->total * 0.5, 2),
                'payment_date' => now()->subDays(5)->toDateString(),
                'method' => 'easypaisa',
                'reference_number' => 'EP' . strtoupper(substr(md5($sentInvoice->id), 0, 8)),
                'notes' => 'Partial payment — 50%.',
            ]);
        }
    }
}
