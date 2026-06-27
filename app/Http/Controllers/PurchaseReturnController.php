<?php

namespace App\Http\Controllers;

use App\Models\ProductPurchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $returns = PurchaseReturn::with('supplier', 'purchase')->latest()->paginate(20);
        return view('purchase-returns.index', compact('returns'));
    }

    public function create(ProductPurchase $purchase)
    {
        $purchase->load('items.product', 'supplier');
        return view('purchase-returns.create', compact('purchase'));
    }

    public function store(Request $request, ProductPurchase $purchase)
    {
        $data = $request->validate([
            'date'              => 'required|date',
            'adjustment_method' => 'required|in:deduct_balance,cash_refund,exchange',
            'reason'            => 'nullable|string|max:500',
            'notes'             => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|exists:product_purchase_items,id',
            'items.*.qty'       => 'required|numeric|min:0.01',
            'items.*.return'    => 'sometimes|boolean',
        ]);

        $purchaseItems = $purchase->items->keyBy('id');
        $returnItems   = [];
        $total         = 0;

        foreach ($request->input('items', []) as $row) {
            if (empty($row['return'])) continue;
            $pItem = $purchaseItems->get($row['item_id']);
            if (!$pItem) continue;

            $qty     = min((float) $row['qty'], (float) $pItem->qty);
            $price   = (float) $pItem->unit_price;
            $rowTotal = round($qty * $price, 2);
            $total  += $rowTotal;

            $returnItems[] = [
                'product_id'   => $pItem->product_id,
                'product_name' => $pItem->product?->name ?? 'Unknown',
                'qty'          => $qty,
                'unit_price'   => $price,
                'total'        => $rowTotal,
            ];
        }

        if (empty($returnItems)) {
            return back()->withErrors(['items' => 'Kam az kam ek item select karein return ke liye.']);
        }

        $return = PurchaseReturn::create([
            'return_number'     => 'PR-' . strtoupper(Str::random(6)),
            'product_purchase_id' => $purchase->id,
            'supplier_id'       => $purchase->supplier_id,
            'date'              => $data['date'],
            'total'             => $total,
            'adjustment_method' => $data['adjustment_method'],
            'reason'            => $data['reason'] ?? null,
            'notes'             => $data['notes'] ?? null,
        ]);

        foreach ($returnItems as $item) {
            PurchaseReturnItem::create(array_merge($item, ['purchase_return_id' => $return->id]));

            // Reduce stock (items going back to supplier)
            if ($item['product_id']) {
                Product::where('id', $item['product_id'])
                    ->where('stock_qty', '>', 0)
                    ->decrement('stock_qty', $item['qty']);
            }
        }

        // If deducting from supplier balance — reduce what we owe them
        if ($data['adjustment_method'] === 'deduct_balance' && $purchase->supplier_id) {
            Supplier::where('id', $purchase->supplier_id)
                ->decrement('balance', $total);
        }

        return redirect()->route('purchase-returns.show', $return)
            ->with('success', 'Purchase return recorded. Amount: PKR ' . number_format($total));
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('items', 'purchase', 'supplier');
        return view('purchase-returns.show', compact('purchaseReturn'));
    }
}
