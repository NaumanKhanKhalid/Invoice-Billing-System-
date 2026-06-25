<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%"));
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->stock === 'low') {
            $query->whereColumn('stock_qty', '<=', 'low_stock_alert');
        } elseif ($request->stock === 'out') {
            $query->where('stock_qty', 0);
        }
        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $products   = $query->orderBy('name')->paginate(30)->withQueryString();
        $categories = Product::distinct()->pluck('category')->filter()->sort()->values();
        $lowStockCount = Product::whereColumn('stock_qty', '<=', 'low_stock_alert')->count();
        $totalValue = Product::sum(DB::raw('stock_qty * cost_price'));

        return view('products.index', compact('products', 'categories', 'lowStockCount', 'totalValue'));
    }

    public function create()
    {
        $categories = Product::distinct()->pluck('category')->filter()->sort()->values();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'sku'              => 'nullable|string|max:50|unique:products,sku',
            'category'         => 'nullable|string|max:80',
            'description'      => 'nullable|string',
            'unit'             => 'required|in:pcs,kg,liter,meter,box,dozen,pair',
            'cost_price'       => 'required|numeric|min:0',
            'sale_price'       => 'required|numeric|min:0',
            'stock_qty'        => 'required|integer|min:0',
            'low_stock_alert'  => 'required|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        Product::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('products.index')->with('success', "Product '{$data['name']}' created.");
    }

    public function show(Product $product)
    {
        $movements = $product->stockMovements()->latest()->paginate(20);
        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        $categories = Product::distinct()->pluck('category')->filter()->sort()->values();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'sku'              => 'nullable|string|max:50|unique:products,sku,'.$product->id,
            'category'         => 'nullable|string|max:80',
            'description'      => 'nullable|string',
            'unit'             => 'required|in:pcs,kg,liter,meter,box,dozen,pair',
            'cost_price'       => 'required|numeric|min:0',
            'sale_price'       => 'required|numeric|min:0',
            'low_stock_alert'  => 'required|integer|min:0',
        ]);

        $product->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $data = $request->validate([
            'type'       => 'required|in:in,out,adjustment',
            'qty'        => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'notes'      => 'nullable|string|max:255',
        ]);

        if ($data['type'] === 'adjustment') {
            $diff = $data['qty'] - $product->stock_qty;
            $product->update(['stock_qty' => $data['qty']]);
            $product->stockMovements()->create([
                'type'      => 'adjustment',
                'qty'       => $diff,
                'notes'     => $data['notes'] ?? 'Manual adjustment',
            ]);
        } elseif ($data['type'] === 'in') {
            $product->addStock($data['qty'], $data['unit_price'] ?? null, null, $data['notes'] ?? null);
        } else {
            $product->removeStock($data['qty'], null, $data['notes'] ?? null);
        }

        return back()->with('success', 'Stock updated.');
    }
}
