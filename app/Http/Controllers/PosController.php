<?php

namespace App\Http\Controllers;

use App\Models\PosSale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $sales = PosSale::orderByDesc('date')->orderByDesc('id')->paginate(20)->withQueryString();
        $todayTotal = PosSale::whereDate('date', today())->sum('total');
        $todayCount = PosSale::whereDate('date', today())->count();
        return view('pos.index', compact('sales', 'todayTotal', 'todayCount'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->where('stock_qty', '>', 0)->orderBy('name')->get(['id','name','sku','barcode','sale_price','stock_qty','unit']);
        return view('pos.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'      => 'nullable|string|max:100',
            'customer_phone'     => 'nullable|string|max:20',
            'payment_method'     => 'required|in:cash,jazzcash,easypaisa,bank,credit',
            'discount'           => 'nullable|numeric|min:0',
            'amount_paid'        => 'required|numeric|min:0',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $subtotal = collect($data['items'])->sum(fn($i) => $i['qty'] * $i['unit_price']);
            $discount = (float)($data['discount'] ?? 0);
            $total    = max(0, $subtotal - $discount);
            $paid     = (float)$data['amount_paid'];
            $change   = max(0, $paid - $total);

            $sale = PosSale::create([
                'sale_number'    => 'POS-' . date('Ymd') . '-' . str_pad(PosSale::whereDate('date', today())->count() + 1, 3, '0', STR_PAD_LEFT),
                'date'           => today(),
                'customer_name'  => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'amount_paid'    => $paid,
                'change_due'     => $change,
                'payment_method' => $data['payment_method'],
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);

                $sale->items()->create([
                    'product_id'   => $item['product_id'],
                    'product_name' => $product->name,
                    'qty'          => $item['qty'],
                    'unit_price'   => $item['unit_price'],
                    'total'        => $item['qty'] * $item['unit_price'],
                ]);

                // Auto stock decrease
                $product->removeStock($item['qty'], 'POS#' . $sale->id);
            }

            session(['last_pos_sale_id' => $sale->id]);
        });

        return redirect()->route('pos.receipt', session('last_pos_sale_id'))
            ->with('success', 'Sale complete!');
    }

    public function show(PosSale $posSale)
    {
        $posSale->load('items.product');
        return view('pos.show', compact('posSale'));
    }

    public function receipt(PosSale $posSale)
    {
        $posSale->load('items');
        return view('pos.receipt', compact('posSale'));
    }

    public function productsApi()
    {
        $products = Product::where('is_active', true)
            ->where('stock_qty', '>', 0)
            ->select('id', 'name', 'sku', 'sale_price', 'stock_qty', 'unit')
            ->orderBy('name')
            ->get();
        return response()->json($products);
    }
}
