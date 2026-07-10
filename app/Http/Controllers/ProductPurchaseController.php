<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductPurchase;
use App\Models\ProductPurchaseItem;
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

        // Last purchase rate per product (rate is stored per base unit)
        $lastRates = ProductPurchaseItem::query()
            ->join('product_purchases', 'product_purchases.id', '=', 'product_purchase_items.product_purchase_id')
            ->orderBy('product_purchase_items.id')
            ->get(['product_purchase_items.product_id', 'product_purchase_items.unit_price', 'product_purchases.date'])
            ->keyBy('product_id')
            ->map(fn ($i) => ['rate' => (float) $i->unit_price, 'date' => \Carbon\Carbon::parse($i->date)->format('d M Y')]);

        $isMedical = (tenant()->shop_type ?? 'general') === 'medical';

        return view('product-purchases.create', compact('suppliers', 'products', 'lastRates', 'isMedical'));
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
            'items.*.batch_no'  => 'nullable|string|max:50',
            'items.*.expiry'    => 'nullable|date_format:Y-m',
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

            $isMedical = (tenant()->shop_type ?? 'general') === 'medical';

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);

                // Unit conversion: qty is entered in purchase_unit (e.g. carton/dozen).
                // product_purchase_items only has a single qty column, so we store the
                // CONVERTED base-unit qty (and a per-base-unit price so total stays correct).
                $factor    = ($product->purchase_unit && $product->conversion_factor > 0) ? (float) $product->conversion_factor : 1;
                $baseQty   = (int) round($item['qty'] * $factor);
                $basePrice = $factor > 1 ? round($item['unit_price'] / $factor, 2) : $item['unit_price'];

                $purchaseItem = $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $baseQty,
                    'unit_price' => $basePrice,
                    'total'      => $item['qty'] * $item['unit_price'],
                ]);

                // Auto stock increase (in base units)
                $product->addStock(
                    $baseQty,
                    $basePrice,
                    'purchase#' . $purchase->id,
                    'Product purchase'
                );

                // Batch + expiry tracking (medical shops)
                if ($isMedical && !empty($item['expiry'])) {
                    ProductBatch::create([
                        'product_id'               => $product->id,
                        'batch_no'                 => $item['batch_no'] ?? null,
                        // End of the chosen expiry month
                        'expiry_date'              => \Carbon\Carbon::createFromFormat('Y-m', $item['expiry'])
                                                        ->startOfMonth()->addMonth()->subDay()->toDateString(),
                        'qty'                      => $baseQty,
                        'product_purchase_item_id' => $purchaseItem->id,
                    ]);
                }
            }
        });

        return redirect()->route('product-purchases.index')
            ->with('success', 'Purchase recorded and stock updated.');
    }

    public function show(ProductPurchase $productPurchase)
    {
        $productPurchase->load('supplier', 'items.product');
        $batches = ProductBatch::whereIn('product_purchase_item_id', $productPurchase->items->pluck('id'))
            ->get()->keyBy('product_purchase_item_id');
        return view('product-purchases.show', compact('productPurchase', 'batches'));
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
