<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductPurchase::with('supplier')->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->supplier_id);
        if ($request->filled('status'))      $query->where('payment_status', $request->status);
        if ($request->filled('from_date'))   $query->where('date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->where('date', '<=', $request->to_date);

        $filteredTotal = (clone $query)->sum('total_amount');
        $purchases     = $query->paginate(15)->withQueryString();
        $suppliers     = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('product-purchases.index', compact('purchases', 'suppliers', 'filteredTotal'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        return view('product-purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'date'              => 'required|date',
            'invoice_number'    => 'nullable|string|max:50',
            'amount_paid'       => 'required|numeric|min:0',
            'due_date'          => 'nullable|date',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.unit_price'=> 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $total = collect($data['items'])->sum(fn($i) => $i['qty'] * $i['unit_price']);
            $paid  = (float) $data['amount_paid'];
            $due   = max(0, $total - $paid);

            $purchase = ProductPurchase::create([
                'supplier_id'    => $data['supplier_id'] ?? null,
                'date'           => $data['date'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'total_amount'   => $total,
                'amount_paid'    => $paid,
                'amount_due'     => $due,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'due_date'       => $data['due_date'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'total'      => $item['qty'] * $item['unit_price'],
                ]);

                // Auto stock increase
                $product = Product::find($item['product_id']);
                $product->addStock(
                    $item['qty'],
                    $item['unit_price'],
                    'purchase#' . $purchase->id,
                    'Product purchase'
                );
            }
        });

        return redirect()->route('product-purchases.index')
            ->with('success', 'Purchase recorded and stock updated.');
    }

    public function show(ProductPurchase $productPurchase)
    {
        $productPurchase->load('supplier', 'items.product');
        return view('product-purchases.show', compact('productPurchase'));
    }

    public function destroy(ProductPurchase $productPurchase)
    {
        // Reverse stock on delete
        foreach ($productPurchase->items as $item) {
            $item->product->removeStock($item->qty, 'purchase#' . $productPurchase->id . ' deleted');
        }
        $productPurchase->delete();
        return redirect()->route('product-purchases.index')->with('success', 'Purchase deleted and stock reversed.');
    }

    public function storePayment(Request $request, ProductPurchase $productPurchase)
    {
        $data = $request->validate(['amount' => 'required|numeric|min:0.01']);

        $newPaid = min($productPurchase->total_amount, $productPurchase->amount_paid + $data['amount']);
        $newDue  = max(0, $productPurchase->total_amount - $newPaid);

        $productPurchase->update([
            'amount_paid'    => $newPaid,
            'amount_due'     => $newDue,
            'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
        ]);

        return back()->with('success', 'Payment recorded.');
    }
}
