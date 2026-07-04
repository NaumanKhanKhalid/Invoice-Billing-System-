<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with('product');

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $movements = $query->latest()->paginate(40)->withQueryString();
        $products  = Product::whereHas('stockMovements')->orderBy('name')->get(['id', 'name']);
        $types     = StockMovement::distinct()->orderBy('type')->pluck('type');

        return view('stock-movements.index', compact('movements', 'products', 'types'));
    }
}
