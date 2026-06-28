<?php

namespace App\Http\Controllers;

use App\Models\OpenTab;
use App\Models\OpenTabItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OpenTabController extends Controller
{
    public function index()
    {
        $openTabs  = OpenTab::where('status', 'open')->withCount('items')->latest()->get();
        $closedTabs = OpenTab::where('status', 'closed')->withCount('items')->latest()->limit(30)->get();
        return view('open-tabs.index', compact('openTabs', 'closedTabs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'notes'          => 'nullable|string|max:500',
        ]);

        $tab = OpenTab::create(array_merge($data, [
            'tab_number' => OpenTab::nextNumber(),
        ]));

        return redirect()->route('open-tabs.show', $tab)
            ->with('success', 'Tab opened for ' . $tab->customer_name);
    }

    public function show(OpenTab $openTab)
    {
        $openTab->load('items.product');
        $products = Product::where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'sale_price', 'unit', 'stock_qty', 'category']);
        return view('open-tabs.show', compact('openTab', 'products'));
    }

    public function addItem(Request $request, OpenTab $openTab)
    {
        abort_if($openTab->status === 'closed', 403, 'Tab is closed.');

        $data = $request->validate([
            'product_id'   => 'nullable|exists:products,id',
            'product_name' => 'required|string|max:200',
            'qty'          => 'required|numeric|min:0.001',
            'price'        => 'required|numeric|min:0',
            'unit'         => 'nullable|string|max:20',
        ]);

        $openTab->items()->create([
            'product_id'   => $data['product_id'] ?? null,
            'product_name' => $data['product_name'],
            'unit'         => $data['unit'] ?? 'pcs',
            'qty'          => $data['qty'],
            'price'        => $data['price'],
            'total'        => round($data['qty'] * $data['price'], 2),
        ]);

        $openTab->recalculate();

        if ($request->expectsJson()) {
            $openTab->load('items');
            return response()->json([
                'items'    => $openTab->items,
                'subtotal' => $openTab->subtotal,
                'total'    => $openTab->total,
            ]);
        }

        return back()->with('success', 'Item added.');
    }

    public function removeItem(OpenTab $openTab, OpenTabItem $item)
    {
        abort_if($openTab->status === 'closed', 403, 'Tab is closed.');
        abort_if($item->open_tab_id !== $openTab->id, 403);

        $item->delete();
        $openTab->recalculate();

        if (request()->expectsJson()) {
            $openTab->load('items');
            return response()->json([
                'items'    => $openTab->items,
                'subtotal' => $openTab->subtotal,
                'total'    => $openTab->total,
            ]);
        }

        return back()->with('success', 'Item removed.');
    }

    public function close(Request $request, OpenTab $openTab)
    {
        abort_if($openTab->status === 'closed', 403, 'Tab already closed.');
        abort_if($openTab->items()->count() === 0, 422, 'Cannot close empty tab.');

        $data = $request->validate([
            'discount'       => 'nullable|numeric|min:0',
            'amount_paid'    => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online,other',
        ]);

        $subtotal = $openTab->items()->sum('total');
        $discount = $data['discount'] ?? 0;
        $total    = max(0, $subtotal - $discount);

        $openTab->update([
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'total'          => $total,
            'amount_paid'    => $data['amount_paid'],
            'payment_method' => $data['payment_method'],
            'status'         => 'closed',
            'closed_at'      => now(),
        ]);

        return redirect()->route('open-tabs.receipt', $openTab)
            ->with('success', 'Tab closed. Bill ready.');
    }

    public function receipt(OpenTab $openTab)
    {
        $openTab->load('items');
        return view('open-tabs.receipt', compact('openTab'));
    }

    public function destroy(OpenTab $openTab)
    {
        abort_if($openTab->status === 'closed', 403, 'Cannot delete closed tab.');
        $openTab->delete();
        return redirect()->route('open-tabs.index')->with('success', 'Tab deleted.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->input('q', '');
        $products = Product::where('is_active', true)
            ->where(fn($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('barcode', $q)
                ->orWhere('sku', 'like', "%{$q}%")
            )
            ->limit(10)
            ->get(['id', 'name', 'sku', 'barcode', 'sale_price', 'unit', 'stock_qty']);

        return response()->json($products);
    }
}
