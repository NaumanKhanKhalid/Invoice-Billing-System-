<?php

namespace App\Http\Controllers;

use App\Models\PosSale;
use App\Models\PosSaleReturn;
use App\Models\PosSaleReturnItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SaleReturnController extends Controller
{
    public function index()
    {
        $returns = PosSaleReturn::with('sale')->latest()->paginate(20);
        return view('sale-returns.index', compact('returns'));
    }

    public function create(PosSale $sale)
    {
        $sale->load('items.product');
        return view('sale-returns.create', compact('sale'));
    }

    public function store(Request $request, PosSale $sale)
    {
        $data = $request->validate([
            'date'          => 'required|date',
            'refund_method' => 'required|in:cash,jazzcash,easypaisa,bank,credit',
            'reason'        => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:500',
            'items'         => 'required|array|min:1',
            'items.*.item_id'    => 'required|exists:pos_sale_items,id',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.return'     => 'sometimes|boolean',
        ]);

        $saleItems = $sale->items->keyBy('id');
        $returnItems = [];
        $subtotal = 0;

        foreach ($request->input('items', []) as $row) {
            if (empty($row['return'])) continue;
            $saleItem = $saleItems->get($row['item_id']);
            if (!$saleItem) continue;

            $qty   = min((float) $row['qty'], (float) $saleItem->qty);
            $price = (float) $saleItem->unit_price;
            $total = round($qty * $price, 2);
            $subtotal += $total;

            $returnItems[] = [
                'product_id'   => $saleItem->product_id,
                'product_name' => $saleItem->product_name,
                'qty'          => $qty,
                'unit_price'   => $price,
                'total'        => $total,
            ];
        }

        if (empty($returnItems)) {
            return back()->withErrors(['items' => 'Kam az kam ek item select karein return ke liye.']);
        }

        $return = PosSaleReturn::create([
            'return_number' => 'RET-' . strtoupper(Str::random(6)),
            'pos_sale_id'   => $sale->id,
            'date'          => $data['date'],
            'subtotal'      => $subtotal,
            'total'         => $subtotal,
            'refund_method' => $data['refund_method'],
            'reason'        => $data['reason'] ?? null,
            'notes'         => $data['notes'] ?? null,
        ]);

        foreach ($returnItems as $item) {
            PosSaleReturnItem::create(array_merge($item, ['pos_sale_return_id' => $return->id]));

            // Restore stock
            if ($item['product_id']) {
                Product::where('id', $item['product_id'])->increment('stock_qty', $item['qty']);
            }
        }

        return redirect()->route('sale-returns.show', $return)
            ->with('success', 'Return processed. Refund: PKR ' . number_format($subtotal));
    }

    public function show(PosSaleReturn $saleReturn)
    {
        $saleReturn->load('items', 'sale');
        return view('sale-returns.show', compact('saleReturn'));
    }
}
