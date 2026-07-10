<?php
namespace App\Http\Controllers;
use App\Models\Product;

class BarcodeLabelController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sale_price', 'barcode', 'sku'])
            ->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'price' => (float) $p->sale_price,
                'code'  => $p->barcode ?: ($p->sku ?: (string) $p->id),
            ])
            ->values();

        return view('barcode-labels.index', compact('products'));
    }
}
