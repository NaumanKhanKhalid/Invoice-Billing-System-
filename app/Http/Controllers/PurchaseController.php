<?php
namespace App\Http\Controllers;
use App\Models\ChickenType;
use App\Models\DailyRate;
use App\Models\PurchaseOrder;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier','chickenType'])->orderByDesc('date')->orderByDesc('id');
        if ($request->filled('supplier_id'))     { $query->where('supplier_id', $request->supplier_id); }
        if ($request->filled('chicken_type_id')) { $query->where('chicken_type_id', $request->chicken_type_id); }
        if ($request->filled('status'))          { $query->where('payment_status', $request->status); }
        if ($request->filled('from_date'))       { $query->where('date', '>=', $request->from_date); }
        if ($request->filled('to_date'))         { $query->where('date', '<=', $request->to_date); }
        $orders = $query->paginate(15)->withQueryString();
        $suppliers    = Supplier::where('is_active',true)->orderBy('name')->get();
        $chickenTypes = ChickenType::where('is_active',true)->get();
        $stats = [
            'total_orders'   => PurchaseOrder::count(),
            'total_amount'   => PurchaseOrder::sum('total_amount'),
            'total_paid'     => PurchaseOrder::sum('amount_paid'),
            'total_due'      => PurchaseOrder::where('payment_status','!=','paid')->sum('amount_due'),
            'overdue_count'  => PurchaseOrder::where('payment_status','!=','paid')->where('due_date','<',today())->count(),
        ];
        return view('purchases.index', compact('orders','suppliers','chickenTypes','stats'));
    }

    public function create()
    {
        $suppliers    = Supplier::where('is_active',true)->orderBy('name')->get();
        $chickenTypes = ChickenType::where('is_active',true)->get();
        $todayRates   = DailyRate::where('date', today()->toDateString())->get()->keyBy('chicken_type_id');
        // fallback to latest rate if today not set
        if ($todayRates->isEmpty()) {
            $todayRates = DailyRate::whereDate('date','<',today())->orderByDesc('date')->get()->unique('chicken_type_id')->keyBy('chicken_type_id');
        }
        $nextNumber = $this->generateInvoiceNumber();
        return view('purchases.create', compact('suppliers','chickenTypes','todayRates','nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'date'               => 'required|date',
            'chicken_type_id'    => 'required|exists:chicken_types,id',
            'live_weight_kg'     => 'required|numeric|min:0.001',
            'dead_on_arrival_kg' => 'nullable|numeric|min:0',
            'rate_per_kg_live'   => 'required|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($data['supplier_id']);
        $liveKg   = (float)$data['live_weight_kg'];
        $doa      = (float)($data['dead_on_arrival_kg'] ?? 0);
        $total    = round($liveKg * (float)$data['rate_per_kg_live'], 2);
        $dueDate  = \Carbon\Carbon::parse($data['date'])->addDays($supplier->credit_days)->toDateString();

        $order = PurchaseOrder::create([
            'supplier_id'        => $data['supplier_id'],
            'date'               => $data['date'],
            'invoice_number'     => $this->generateInvoiceNumber(),
            'chicken_type_id'    => $data['chicken_type_id'],
            'live_weight_kg'     => $liveKg,
            'dead_on_arrival_kg' => $doa,
            'rate_per_kg_live'   => $data['rate_per_kg_live'],
            'total_amount'       => $total,
            'amount_paid'        => 0,
            'amount_due'         => $total,
            'due_date'           => $dueDate,
            'payment_status'     => 'unpaid',
            'notes'              => $data['notes'] ?? null,
        ]);

        // Update supplier balance
        $supplier->increment('balance', $total);

        return redirect()->route('purchases.show', $order)->with('success', "Purchase order {$order->invoice_number} created successfully.");
    }

    public function show(PurchaseOrder $purchase)
    {
        $purchase->load(['supplier','chickenType','purchasePayments']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(PurchaseOrder $purchase)
    {
        if ($purchase->payment_status === 'paid') {
            return back()->with('error', 'Paid purchase orders cannot be edited.');
        }
        $suppliers    = Supplier::where('is_active',true)->orderBy('name')->get();
        $chickenTypes = ChickenType::where('is_active',true)->get();
        return view('purchases.edit', compact('purchase','suppliers','chickenTypes'));
    }

    public function update(Request $request, PurchaseOrder $purchase)
    {
        if ($purchase->payment_status === 'paid') {
            return back()->with('error', 'Paid purchase orders cannot be edited.');
        }
        $data = $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'date'               => 'required|date',
            'chicken_type_id'    => 'required|exists:chicken_types,id',
            'live_weight_kg'     => 'required|numeric|min:0.001',
            'dead_on_arrival_kg' => 'nullable|numeric|min:0',
            'rate_per_kg_live'   => 'required|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);
        $supplier   = Supplier::findOrFail($data['supplier_id']);
        $liveKg     = (float)$data['live_weight_kg'];
        $doa        = (float)($data['dead_on_arrival_kg'] ?? 0);
        $newTotal   = round($liveKg * (float)$data['rate_per_kg_live'], 2);
        $oldTotal   = $purchase->total_amount;
        $amountPaid = $purchase->amount_paid;
        $dueDate    = \Carbon\Carbon::parse($data['date'])->addDays($supplier->credit_days)->toDateString();

        $purchase->update([
            'supplier_id'        => $data['supplier_id'],
            'date'               => $data['date'],
            'chicken_type_id'    => $data['chicken_type_id'],
            'live_weight_kg'     => $liveKg,
            'dead_on_arrival_kg' => $doa,
            'rate_per_kg_live'   => $data['rate_per_kg_live'],
            'total_amount'       => $newTotal,
            'amount_due'         => max(0, $newTotal - $amountPaid),
            'due_date'           => $dueDate,
            'notes'              => $data['notes'] ?? null,
        ]);

        // Adjust supplier balance
        $supplier->increment('balance', $newTotal - $oldTotal);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchase)
    {
        if ($purchase->payment_status !== 'unpaid') {
            return back()->with('error', 'Only unpaid orders with no payments can be deleted.');
        }
        $purchase->supplier->decrement('balance', $purchase->total_amount);
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase order deleted.');
    }

    public function storePayment(Request $request, PurchaseOrder $purchase)
    {
        $data = $request->validate([
            'amount'       => 'required|numeric|min:0.01|max:'.$purchase->amount_due,
            'payment_date' => 'required|date',
            'method'       => 'required|in:cash,bank,jazzcash,easypaisa',
            'note'         => 'nullable|string',
        ]);

        PurchasePayment::create(array_merge($data, ['purchase_order_id' => $purchase->id]));

        $newPaid = $purchase->amount_paid + (float)$data['amount'];
        $newDue  = max(0, $purchase->total_amount - $newPaid);
        $status  = $newDue <= 0 ? 'paid' : 'partial';

        $purchase->update(['amount_paid' => $newPaid, 'amount_due' => $newDue, 'payment_status' => $status]);
        $purchase->supplier->decrement('balance', (float)$data['amount']);

        return back()->with('success', 'Payment recorded successfully.');
    }

    private function generateInvoiceNumber(): string
    {
        $year  = date('Y');
        $count = PurchaseOrder::whereYear('created_at', $year)->count() + 1;
        return 'PO-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
