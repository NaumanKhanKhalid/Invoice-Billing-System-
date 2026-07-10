<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\OpenTab;
use App\Models\PosSale;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\UdharCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $products = Product::where('is_active', true)->where('stock_qty', '>', 0)->orderBy('name')->get(['id','name','sku','barcode','sale_price','wholesale_price','track_serial','stock_qty','unit','category']);
        $todaySales = PosSale::whereDate('date', today())->count();
        $todayRevenue = PosSale::whereDate('date', today())->sum('total');
        $lowStock = Product::where('is_active', true)->whereColumn('stock_qty', '<=', 'low_stock_alert')->count();
        $heldSales = feature_enabled('open_tabs') ? $this->openHolds() : collect();
        return view('pos.create', compact('products', 'todaySales', 'todayRevenue', 'lowStock', 'heldSales'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'      => 'required_if:payment_method,credit|nullable|string|max:100',
            'customer_phone'     => 'required_if:payment_method,credit|nullable|string|max:20',
            'payment_method'     => 'required|in:cash,jazzcash,easypaisa,bank,credit',
            'discount'           => 'nullable|numeric|min:0',
            'amount_paid'        => 'required|numeric|min:0',
            'notes'              => 'nullable|string',
            'client_uuid'        => 'nullable|string|max:64',
            'hold_id'            => 'nullable|integer|exists:open_tabs,id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'serials'            => 'nullable|array',
            'serials.*'          => 'array',
            'serials.*.*'        => 'string|max:64',
        ], [
            'customer_name.required_if'  => 'Udhar sale ke liye customer name zaroori hai.',
            'customer_phone.required_if' => 'Udhar sale ke liye customer phone zaroori hai.',
        ]);

        // Offline-queue dedupe: the POS page retries queued sales when the
        // connection comes back, so the same sale may be POSTed twice. We tag
        // each sale with its client_uuid inside the existing `notes` column
        // (no schema change) and skip creating a duplicate if already stored.
        $clientUuid = $data['client_uuid'] ?? null;
        if ($clientUuid) {
            $existing = PosSale::where('notes', 'like', '%[client_uuid:' . $clientUuid . ']%')->first();
            if ($existing) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'ok'          => true,
                        'sale_number' => $existing->sale_number,
                        'receipt_url' => route('pos.receipt', $existing),
                        'duplicate'   => true,
                    ]);
                }
                return redirect()->route('pos.receipt', $existing)->with('success', 'Sale complete!');
            }
        }

        DB::transaction(function () use ($data, $clientUuid) {
            $subtotal = collect($data['items'])->sum(fn($i) => $i['qty'] * $i['unit_price']);
            $discount = (float)($data['discount'] ?? 0);
            $total    = max(0, $subtotal - $discount);
            $paid     = (float)$data['amount_paid'];
            $change   = max(0, $paid - $total);

            if (feature_enabled('stock_guard')) {
                foreach ($data['items'] as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    if ($product && $item['qty'] > $product->stock_qty) {
                        throw ValidationException::withMessages([
                            'items' => "Not enough stock for {$product->name}. Available: {$product->stock_qty} {$product->unit}.",
                        ]);
                    }
                }
            }

            // lockForUpdate so two simultaneous sales cannot get the same number
            $sale = PosSale::create([
                'sale_number'    => 'POS-' . date('Ymd') . '-' . str_pad(PosSale::whereDate('date', today())->lockForUpdate()->count() + 1, 3, '0', STR_PAD_LEFT),
                'date'           => today(),
                'customer_name'  => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'amount_paid'    => $paid,
                'change_due'     => $change,
                'payment_method' => $data['payment_method'],
                'notes'          => trim(($data['notes'] ?? '') . ($clientUuid ? ' [client_uuid:' . $clientUuid . ']' : '')) ?: null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);

                $saleItem = $sale->items()->create([
                    'product_id'   => $item['product_id'],
                    'product_name' => $product->name,
                    'qty'          => $item['qty'],
                    'unit_price'   => $item['unit_price'],
                    'total'        => $item['qty'] * $item['unit_price'],
                ]);

                // IMEI/serial capture — optional, shopkeeper may skip
                foreach ($data['serials'][$item['product_id']] ?? [] as $serial) {
                    $serial = trim($serial);
                    if ($serial === '') continue;
                    $existing = ProductSerial::where('product_id', $item['product_id'])->where('serial', $serial)->first();
                    if ($existing) {
                        if ($existing->status === 'in_stock') {
                            $existing->update(['status' => 'sold', 'pos_sale_item_id' => $saleItem->id]);
                        }
                    } else {
                        ProductSerial::create([
                            'product_id'       => $item['product_id'],
                            'serial'           => $serial,
                            'status'           => 'sold',
                            'pos_sale_item_id' => $saleItem->id,
                        ]);
                    }
                }

                // Auto stock decrease
                $product->removeStock($item['qty'], 'POS#' . $sale->id);
            }

            // Udhar sale — record the unpaid amount in the Udhar Book
            if ($data['payment_method'] === 'credit') {
                $unpaid = round($total - $paid, 2);
                if ($unpaid > 0) {
                    $udharCustomer = UdharCustomer::firstOrCreate(
                        ['phone' => $data['customer_phone']],
                        ['name'  => $data['customer_name']]
                    );

                    CreditSale::create([
                        'udhar_customer_id' => $udharCustomer->id,
                        'customer_name'     => $data['customer_name'],
                        'phone'             => $data['customer_phone'],
                        'amount'            => $unpaid,
                        'amount_paid'       => 0,
                        'amount_due'        => $unpaid,
                        'sale_date'         => today(),
                        'due_date'          => today()->addDays(30),
                        'description'       => 'POS ' . $sale->sale_number,
                        'status'            => 'unpaid',
                    ]);

                    $udharCustomer->increment('total_given', $unpaid);
                    $udharCustomer->increment('current_balance', $unpaid);
                }
            }

            // Sale resumed from a hold — the hold is now settled, remove it
            if (!empty($data['hold_id'])) {
                $tab = OpenTab::where('status', 'open')->find($data['hold_id']);
                if ($tab) {
                    $tab->items()->delete();
                    $tab->delete();
                }
            }

            session(['last_pos_sale_id' => $sale->id]);
        });

        $sale = PosSale::find(session('last_pos_sale_id'));

        if ($request->wantsJson()) {
            return response()->json([
                'ok'          => true,
                'sale_number' => $sale?->sale_number,
                'receipt_url' => $sale ? route('pos.receipt', $sale) : route('pos.index'),
            ]);
        }

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

    /**
     * Hold the current POS cart as an OpenTab (POST /pos/hold — pos.hold).
     * Passing hold_id updates that hold in place (re-hold after resume)
     * instead of creating a duplicate.
     */
    public function holdSale(Request $request)
    {
        $data = $request->validate([
            'hold_id'            => 'nullable|integer|exists:open_tabs,id',
            'customer_name'      => 'required|string|max:100',
            'customer_phone'     => 'nullable|string|max:20',
            'notes'              => 'nullable|string|max:500',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.name'       => 'required|string|max:200',
            'items.*.qty'        => 'required|numeric|min:0.001',
            'items.*.price'      => 'required|numeric|min:0',
            'items.*.unit'       => 'nullable|string|max:20',
        ]);

        $tab = DB::transaction(function () use ($data) {
            if (!empty($data['hold_id'])) {
                $tab = OpenTab::where('status', 'open')->findOrFail($data['hold_id']);
                $tab->update([
                    'customer_name'  => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'notes'          => $data['notes'] ?? null,
                ]);
                $tab->items()->delete();
            } else {
                $tab = OpenTab::create([
                    'tab_number'     => OpenTab::nextNumber(),
                    'customer_name'  => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'notes'          => $data['notes'] ?? null,
                ]);
            }

            foreach ($data['items'] as $item) {
                $tab->items()->create([
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['name'],
                    'unit'         => $item['unit'] ?? 'pcs',
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                    'total'        => round($item['qty'] * $item['price'], 2),
                ]);
            }

            $tab->recalculate();
            return $tab;
        });

        return response()->json([
            'ok'         => true,
            'tab_number' => $tab->tab_number,
            'holds'      => $this->openHolds(),
        ]);
    }

    /** Items of a hold, for resuming into the cart (GET /pos/hold/{openTab} — pos.hold.show). */
    public function holdShow(OpenTab $openTab)
    {
        abort_if($openTab->status === 'closed', 403, 'Hold is closed.');

        return response()->json([
            'id'             => $openTab->id,
            'tab_number'     => $openTab->tab_number,
            'customer_name'  => $openTab->customer_name,
            'customer_phone' => $openTab->customer_phone,
            'notes'          => $openTab->notes,
            'items'          => $openTab->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'name'       => $i->product_name,
                'qty'        => $i->qty,
                'price'      => $i->price,
                'unit'       => $i->unit,
            ])->values(),
        ]);
    }

    /** Delete a hold (DELETE /pos/hold/{openTab} — pos.hold.delete). */
    public function holdDelete(OpenTab $openTab)
    {
        abort_if($openTab->status === 'closed', 403, 'Cannot delete closed hold.');
        $openTab->items()->delete();
        $openTab->delete();

        return response()->json(['ok' => true, 'holds' => $this->openHolds()]);
    }

    /** Open holds in the shape the POS "Held" panel expects. */
    private function openHolds()
    {
        return OpenTab::where('status', 'open')->withCount('items')->latest()->get()
            ->map(fn ($t) => [
                'id'             => $t->id,
                'tab_number'     => $t->tab_number,
                'customer_name'  => $t->customer_name,
                'customer_phone' => $t->customer_phone,
                'items_count'    => $t->items_count,
                'total'          => $t->total,
                'created_at'     => $t->created_at?->toIso8601String(),
            ])->values();
    }

    public function productsApi()
    {
        $products = Product::where('is_active', true)
            ->where('stock_qty', '>', 0)
            ->select('id', 'name', 'sku', 'sale_price', 'wholesale_price', 'track_serial', 'stock_qty', 'unit')
            ->orderBy('name')
            ->get();
        return response()->json($products);
    }
}
