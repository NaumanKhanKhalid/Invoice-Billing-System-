<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProductPurchase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplyOrder;
use App\Models\UdharCustomer;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    // ── SUPPLIER LEDGER ───────────────────────────────────────────
    public function supplier(Request $request, Supplier $supplier)
    {
        $shopType  = tenant()->shop_type ?? 'general';
        $isChicken = $shopType === 'chicken';

        $entries = collect();

        if ($isChicken) {
            // Chicken: PurchaseOrders + their payments
            $orders = PurchaseOrder::with('payments')
                ->where('supplier_id', $supplier->id)
                ->orderBy('date')->get();

            foreach ($orders as $order) {
                $entries->push([
                    'date'        => $order->date,
                    'type'        => 'purchase',
                    'description' => 'Purchase — ' . $order->invoice_number,
                    'debit'       => $order->total_amount,  // we owe supplier
                    'credit'      => 0,
                    'ref'         => null,
                ]);
                foreach ($order->payments as $pay) {
                    $entries->push([
                        'date'        => $pay->payment_date,
                        'type'        => 'payment',
                        'description' => 'Payment — ' . ucfirst($pay->method),
                        'debit'       => 0,
                        'credit'      => $pay->amount,      // we paid
                        'ref'         => null,
                    ]);
                }
            }
        } else {
            // Product shops: ProductPurchases + payments + returns
            $purchases = ProductPurchase::with('items')
                ->where('supplier_id', $supplier->id)
                ->orderBy('date')->get();

            foreach ($purchases as $purchase) {
                $entries->push([
                    'date'        => $purchase->date,
                    'type'        => 'purchase',
                    'description' => 'Purchase — ' . ($purchase->invoice_number ?: '#'.$purchase->id),
                    'debit'       => $purchase->total_amount,
                    'credit'      => 0,
                    'ref'         => route('product-purchases.show', $purchase),
                ]);
                if ($purchase->amount_paid > 0) {
                    $entries->push([
                        'date'        => $purchase->date,
                        'type'        => 'payment',
                        'description' => 'Payment on purchase #'.$purchase->id,
                        'debit'       => 0,
                        'credit'      => $purchase->amount_paid,
                        'ref'         => null,
                    ]);
                }
            }

            // Purchase returns reduce what we owe
            $returns = PurchaseReturn::where('supplier_id', $supplier->id)->orderBy('date')->get();
            foreach ($returns as $ret) {
                $entries->push([
                    'date'        => $ret->date,
                    'type'        => 'return',
                    'description' => 'Return — ' . $ret->return_number,
                    'debit'       => 0,
                    'credit'      => $ret->total,
                    'ref'         => route('purchase-returns.show', $ret),
                ]);
            }
        }

        // Sort by date asc, compute running balance
        $entries  = $entries->sortBy('date')->values();
        $balance  = 0;
        $ledger   = [];
        foreach ($entries as $entry) {
            $balance += $entry['debit'] - $entry['credit'];
            $ledger[] = array_merge($entry, ['balance' => $balance]);
        }

        $openingBalance = 0;
        $totalDebit     = collect($ledger)->sum('debit');
        $totalCredit    = collect($ledger)->sum('credit');
        $closingBalance = $balance;

        return view('ledger.supplier', compact(
            'supplier', 'ledger', 'openingBalance',
            'totalDebit', 'totalCredit', 'closingBalance', 'shopType'
        ));
    }

    // ── CUSTOMER LEDGER (Chicken — supply orders) ─────────────────
    public function customer(Request $request, Customer $customer)
    {
        $orders = SupplyOrder::with('payments')
            ->where('customer_id', $customer->id)
            ->orderBy('date')->get();

        $entries = collect();
        foreach ($orders as $order) {
            $entries->push([
                'date'        => $order->date,
                'type'        => 'invoice',
                'description' => 'Supply Order — ' . $order->invoice_number,
                'debit'       => $order->total_amount,   // customer owes us
                'credit'      => 0,
                'ref'         => route('supply.show', $order),
            ]);
            foreach ($order->payments ?? [] as $pay) {
                $entries->push([
                    'date'        => $pay->payment_date,
                    'type'        => 'payment',
                    'description' => 'Payment received — ' . ucfirst($pay->method),
                    'debit'       => 0,
                    'credit'      => $pay->amount,
                    'ref'         => null,
                ]);
            }
        }

        $entries = $entries->sortBy('date')->values();
        $balance = 0;
        $ledger  = [];
        foreach ($entries as $entry) {
            $balance += $entry['debit'] - $entry['credit'];
            $ledger[] = array_merge($entry, ['balance' => $balance]);
        }

        $totalDebit     = collect($ledger)->sum('debit');
        $totalCredit    = collect($ledger)->sum('credit');
        $closingBalance = $balance;

        return view('ledger.customer', compact(
            'customer', 'ledger',
            'totalDebit', 'totalCredit', 'closingBalance'
        ));
    }

    // ── UDHAR CUSTOMER LEDGER ─────────────────────────────────────
    public function udharCustomer(Request $request, UdharCustomer $udharCustomer)
    {
        $sales = $udharCustomer->creditSales()->with('payments')->orderBy('sale_date')->get();

        $entries = collect();
        foreach ($sales as $sale) {
            $entries->push([
                'date'        => $sale->sale_date,
                'type'        => 'credit_sale',
                'description' => 'Udhar — ' . ($sale->description ?: 'Credit Sale'),
                'debit'       => $sale->amount,
                'credit'      => 0,
                'ref'         => route('udhar.show', $sale),
            ]);
            foreach ($sale->payments ?? [] as $pay) {
                $entries->push([
                    'date'        => $pay->payment_date,
                    'type'        => 'payment',
                    'description' => 'Payment received',
                    'debit'       => 0,
                    'credit'      => $pay->amount,
                    'ref'         => null,
                ]);
            }
        }

        $entries = $entries->sortBy('date')->values();
        $balance = 0;
        $ledger  = [];
        foreach ($entries as $entry) {
            $balance += $entry['debit'] - $entry['credit'];
            $ledger[] = array_merge($entry, ['balance' => $balance]);
        }

        $totalDebit     = collect($ledger)->sum('debit');
        $totalCredit    = collect($ledger)->sum('credit');
        $closingBalance = $balance;

        return view('ledger.udhar-customer', compact(
            'udharCustomer', 'ledger',
            'totalDebit', 'totalCredit', 'closingBalance'
        ));
    }
}
