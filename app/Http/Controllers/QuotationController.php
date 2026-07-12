<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::latest()->paginate(20);
        $counts = [
            'draft'    => Quotation::where('status', 'draft')->count(),
            'sent'     => Quotation::where('status', 'sent')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
        ];
        return view('quotations.index', compact('quotations', 'counts'));
    }

    public function create()
    {
        $products    = Product::orderBy('name')->get(['id', 'name', 'unit', 'sale_price']);
        $quoteNumber = Quotation::nextNumber();
        return view('quotations.create', compact('products', 'quoteNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quote_number'   => 'required|string|unique:quotations,quote_number',
            'date'           => 'required|date',
            'valid_until'    => 'nullable|date|after_or_equal:date',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'discount'       => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.qty'          => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        $discount = (float) ($data['discount'] ?? 0);
        $subtotal = 0;
        $rows = [];
        foreach ($request->items as $item) {
            $total     = round($item['qty'] * $item['unit_price'], 2);
            $subtotal += $total;
            $rows[]    = [
                'product_id'   => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'unit'         => $item['unit'] ?? null,
                'qty'          => $item['qty'],
                'unit_price'   => $item['unit_price'],
                'total'        => $total,
            ];
        }

        $quotation = DB::transaction(function () use ($data, $subtotal, $discount, $rows) {
            // Re-check under lock: another request may have taken this number
            // between validation and insert — regenerate if so
            $quoteNumber = $data['quote_number'];
            if (Quotation::where('quote_number', $quoteNumber)->lockForUpdate()->exists()) {
                $quoteNumber = Quotation::nextNumber();
            }

            $quotation = Quotation::create([
                'quote_number'   => $quoteNumber,
                'date'           => $data['date'],
                'valid_until'    => $data['valid_until'] ?? null,
                'customer_name'  => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => max(0, $subtotal - $discount),
                'notes'          => $data['notes'] ?? null,
                'status'         => 'draft',
            ]);

            $quotation->items()->createMany($rows);

            return $quotation;
        });

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation ' . $quotation->quote_number . ' created.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('items.product');
        return view('quotations.show', compact('quotation'));
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate(['status' => 'required|in:draft,sent,accepted,rejected,expired']);
        $quotation->update(['status' => $request->status]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted.');
    }
}
