<?php

namespace App\Http\Controllers;

use App\Models\ChickenType;
use App\Models\Customer;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'chickenType'])->latest('date');

        if ($request->customer_id)     $query->where('customer_id', $request->customer_id);
        if ($request->order_type)      $query->where('order_type', $request->order_type);
        if ($request->chicken_type_id) $query->where('chicken_type_id', $request->chicken_type_id);
        if ($request->status)          $query->where('payment_status', $request->status);
        if ($request->from_date)       $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date)         $query->whereDate('date', '<=', $request->to_date);

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total_today'    => SalesOrder::whereDate('date', today())->sum('total_amount'),
            'total_due'      => SalesOrder::where('payment_status', '!=', 'paid')->sum('amount_due'),
            'overdue_count'  => SalesOrder::where('payment_status', '!=', 'paid')->whereDate('due_date', '<', today())->count(),
            'this_month_kg'  => SalesOrder::whereMonth('date', now()->month)->sum('dressed_weight_kg'),
        ];

        $customers    = Customer::where('is_active', true)->orderBy('name')->get();
        $chickenTypes = ChickenType::where('is_active', true)->get();

        return view('sales.index', compact('orders', 'stats', 'customers', 'chickenTypes'));
    }

    public function create()
    {
        $customers    = Customer::where('is_active', true)->where('is_blacklisted', false)->orderBy('name')->get();
        $chickenTypes = ChickenType::where('is_active', true)->get();
        $nextNumber   = $this->nextInvoiceNumber();

        return view('sales.create', compact('customers', 'chickenTypes', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'       => 'nullable|exists:customers,id',
            'date'              => 'required|date',
            'order_type'        => 'required|in:retail,supply',
            'chicken_type_id'   => 'required|exists:chicken_types,id',
            'dressed_weight_kg' => 'required|numeric|min:0.001',
            'rate_per_kg'       => 'required|numeric|min:0',
            'delivery_address'  => 'nullable|string',
            'delivery_notes'    => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        $total = round($data['dressed_weight_kg'] * $data['rate_per_kg'], 2);

        $creditDays = 0;
        if ($data['customer_id']) {
            $customer   = Customer::findOrFail($data['customer_id']);
            $creditDays = $customer->credit_days ?? 0;
        }

        $dueDate = $creditDays > 0
            ? Carbon::parse($data['date'])->addDays($creditDays)->toDateString()
            : $data['date'];

        $sale = SalesOrder::create(array_merge($data, [
            'invoice_number' => $this->nextInvoiceNumber(),
            'total_amount'   => $total,
            'amount_paid'    => 0,
            'amount_due'     => $total,
            'payment_status' => 'unpaid',
            'due_date'       => $dueDate,
        ]));

        if ($data['customer_id']) {
            Customer::findOrFail($data['customer_id'])->increment('current_balance', $total);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded: ' . $sale->invoice_number);
    }

    public function show(SalesOrder $sale)
    {
        $sale->load(['customer', 'chickenType', 'salePayments']);

        $whatsappLink = null;
        if ($sale->customer && $sale->customer->phone) {
            $phone = preg_replace('/\D/', '', $sale->customer->phone);
            if (str_starts_with($phone, '0')) $phone = '92' . substr($phone, 1);
            $msg  = "Dear {$sale->customer->name},\n";
            $msg .= "Invoice: {$sale->invoice_number}\n";
            $msg .= "Date: " . Carbon::parse($sale->date)->format('d M Y') . "\n";
            $msg .= "Amount: PKR " . number_format($sale->total_amount, 0) . "\n";
            $msg .= "Due: PKR " . number_format($sale->amount_due, 0) . "\n";
            $msg .= "- Anwar Chicken Center";
            $whatsappLink = "https://wa.me/{$phone}?text=" . urlencode($msg);
        }

        return view('sales.show', compact('sale', 'whatsappLink'));
    }

    public function edit(SalesOrder $sale)
    {
        abort_if($sale->payment_status === 'paid', 403, 'Cannot edit a fully paid sale.');
        $customers    = Customer::where('is_active', true)->orderBy('name')->get();
        $chickenTypes = ChickenType::where('is_active', true)->get();

        return view('sales.edit', compact('sale', 'customers', 'chickenTypes'));
    }

    public function update(Request $request, SalesOrder $sale)
    {
        abort_if($sale->payment_status === 'paid', 403);

        $data = $request->validate([
            'customer_id'       => 'nullable|exists:customers,id',
            'date'              => 'required|date',
            'order_type'        => 'required|in:retail,supply',
            'chicken_type_id'   => 'required|exists:chicken_types,id',
            'dressed_weight_kg' => 'required|numeric|min:0.001',
            'rate_per_kg'       => 'required|numeric|min:0',
            'delivery_address'  => 'nullable|string',
            'delivery_notes'    => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        $oldTotal = $sale->total_amount;
        $newTotal = round($data['dressed_weight_kg'] * $data['rate_per_kg'], 2);

        $creditDays = 0;
        if ($data['customer_id']) {
            $creditDays = Customer::findOrFail($data['customer_id'])->credit_days ?? 0;
        }

        $dueDate = $creditDays > 0
            ? Carbon::parse($data['date'])->addDays($creditDays)->toDateString()
            : $data['date'];

        $sale->update(array_merge($data, [
            'total_amount' => $newTotal,
            'amount_due'   => $newTotal - $sale->amount_paid,
            'due_date'     => $dueDate,
        ]));

        // Adjust customer balance
        $oldCustomer = $sale->getOriginal('customer_id');
        if ($oldCustomer) Customer::findOrFail($oldCustomer)->decrement('current_balance', $oldTotal);
        if ($data['customer_id']) Customer::findOrFail($data['customer_id'])->increment('current_balance', $newTotal);

        return redirect()->route('sales.show', $sale)->with('success', 'Sale updated.');
    }

    public function destroy(SalesOrder $sale)
    {
        abort_if($sale->payment_status !== 'unpaid', 403, 'Only unpaid sales can be deleted.');
        if ($sale->customer_id) {
            Customer::findOrFail($sale->customer_id)->decrement('current_balance', $sale->total_amount);
        }
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale deleted.');
    }

    public function storePayment(Request $request, SalesOrder $sale)
    {
        $data = $request->validate([
            'amount'       => 'required|numeric|min:0.01|max:' . $sale->amount_due,
            'payment_date' => 'required|date',
            'method'       => 'required|in:cash,bank,jazzcash,easypaisa',
            'note'         => 'nullable|string',
        ]);

        $sale->salePayments()->create($data);

        $newPaid = $sale->amount_paid + $data['amount'];
        $newDue  = $sale->total_amount - $newPaid;
        $status  = $newDue <= 0 ? 'paid' : 'partial';

        $sale->update([
            'amount_paid'    => $newPaid,
            'amount_due'     => max(0, $newDue),
            'payment_status' => $status,
        ]);

        if ($sale->customer_id) {
            Customer::findOrFail($sale->customer_id)->decrement('current_balance', $data['amount']);
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Payment of PKR ' . number_format($data['amount'], 0) . ' recorded.');
    }

    private function nextInvoiceNumber(): string
    {
        $year  = date('Y');
        $count = SalesOrder::whereYear('created_at', $year)->count() + 1;
        return 'SO-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
