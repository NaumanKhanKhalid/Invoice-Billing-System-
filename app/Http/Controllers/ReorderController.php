<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Supplier;

class ReorderController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->whereColumn('stock_qty', '<=', 'low_stock_alert')
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $p->suggested_qty = max($p->low_stock_alert * 3 - $p->stock_qty, 1);
                return $p;
            });

        $suppliers = Supplier::where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('reorder.index', compact('products', 'suppliers'));
    }
}
