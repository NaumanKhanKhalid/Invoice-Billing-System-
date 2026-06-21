<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\SupplyOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplyOrder::with('customer')->latest('date');

        if ($request->customer_id) $query->where('customer_id', $request->customer_id);
        if ($request->status)      $query->where('payment_status', $request->status);
        if ($request->from_date)   $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date)     $query->whereDate('date', '<=', $request->to_date);

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total_today'    => SupplyOrder::whereDate('date', today())->sum('total_amount'),
            'total_due'      => SupplyOrder::where('payment_status', '!=', 'paid')->sum('amount_due'),
            'overdue_count'  => SupplyOrder::where('payment_status', '!=', 'paid')->whereDate('due_date', '<', today())->count(),
            'this_month_kg'  => SupplyOrder::whereMonth('date', now()->month)->sum('dressed_weight_kg'),
        ];

        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('supply.index', compact('orders', 'stats', 'customers'));
    }

    public function schedule()
    {
        $today    = today();
        $tomorrow = today()->addDay();
        $dayAfter = today()->addDays(2);

        $baseQuery = fn() => SupplyOrder::with('customer')->where('is_delivered', false);

        $overdue  = (clone $baseQuery())->whereDate('delivery_date', '<', $today)->orderBy('delivery_date')->get();
        $todayOrders    = (clone $baseQuery())->whereDate('delivery_date', $today)->orderBy('delivery_date')->get();
        $tomorrowOrders = (clone $baseQuery())->whereDate('delivery_date', $tomorrow)->orderBy('delivery_date')->get();
        $upcoming = (clone $baseQuery())->whereDate('delivery_date', '>', $tomorrow)->orderBy('delivery_date')->get();

        return view('supply.schedule', compact('overdue', 'todayOrders', 'tomorrowOrders', 'upcoming', 'today', 'tomorrow', 'dayAfter'));
    }

    public function create()
    {
        $customers  = Customer::where('is_active', true)->where('is_blacklisted', false)
            ->whereIn('type', ['hotel', 'catering', 'restaurant', 'company', 'reseller'])
            ->orderBy('type')->orderBy('name')->get()
            ->groupBy('type');
        $nextNumber = $this->nextInvoiceNumber();

        return view('supply.create', compact('customers', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'date'              => 'required|date',
            'delivery_date'     => 'nullable|date',
            'dressed_weight_kg' => 'required|numeric|min:0.001',
            'rate_per_kg'       => 'required|numeric|min:0',
            'delivery_address'  => 'nullable|string',
            'delivery_notes'    => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        $data['delivery_date'] = $data['delivery_date'] ?? $data['date'];

        $total    = round($data['dressed_weight_kg'] * $data['rate_per_kg'], 2);
        $customer = Customer::findOrFail($data['customer_id']);
        $dueDate  = Carbon::parse($data['date'])->addDays($customer->credit_days ?? 30)->toDateString();

        $order = SupplyOrder::create(array_merge($data, [
            'invoice_number' => $this->nextInvoiceNumber(),
            'total_amount'   => $total,
            'amount_paid'    => 0,
            'amount_due'     => $total,
            'payment_status' => 'unpaid',
            'due_date'       => $dueDate,
        ]));

        $customer->increment('current_balance', $total);

        return redirect()->route('supply.show', $order)->with('success', 'Supply order recorded: ' . $order->invoice_number);
    }

    public function show(SupplyOrder $supply)
    {
        $supply->load(['customer', 'payments']);

        $whatsappLink = null;
        if ($supply->customer?->phone) {
            $phone = preg_replace('/\D/', '', $supply->customer->phone);
            if (str_starts_with($phone, '0')) $phone = '92' . substr($phone, 1);
            $msg  = "Dear {$supply->customer->name},\nInvoice: {$supply->invoice_number}\n";
            $msg .= "Date: " . Carbon::parse($supply->date)->format('d M Y') . "\n";
            $msg .= "Amount: PKR " . number_format($supply->total_amount, 0) . "\n";
            $msg .= "Due: PKR " . number_format($supply->amount_due, 0) . "\n- Anwar Chicken Center";
            $whatsappLink = "https://wa.me/{$phone}?text=" . urlencode($msg);
        }

        return view('supply.show', compact('supply', 'whatsappLink'));
    }

    public function edit(SupplyOrder $supply)
    {
        abort_if($supply->payment_status === 'paid', 403, 'Cannot edit a fully paid order.');
        $customers = Customer::where('is_active', true)->where('is_blacklisted', false)
            ->whereIn('type', ['hotel', 'catering', 'restaurant', 'company', 'reseller'])
            ->orderBy('type')->orderBy('name')->get()
            ->groupBy('type');
        return view('supply.edit', compact('supply', 'customers'));
    }

    public function update(Request $request, SupplyOrder $supply)
    {
        abort_if($supply->payment_status === 'paid', 403);

        $data = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'date'              => 'required|date',
            'delivery_date'     => 'nullable|date',
            'dressed_weight_kg' => 'required|numeric|min:0.001',
            'rate_per_kg'       => 'required|numeric|min:0',
            'delivery_address'  => 'nullable|string',
            'delivery_notes'    => 'nullable|string',
            'notes'             => 'nullable|string',
        ]);

        $data['delivery_date'] = $data['delivery_date'] ?? $data['date'];

        $oldTotal = $supply->total_amount;
        $newTotal = round($data['dressed_weight_kg'] * $data['rate_per_kg'], 2);
        $customer = Customer::findOrFail($data['customer_id']);
        $dueDate  = Carbon::parse($data['date'])->addDays($customer->credit_days ?? 30)->toDateString();

        $supply->update(array_merge($data, [
            'total_amount' => $newTotal,
            'amount_due'   => $newTotal - $supply->amount_paid,
            'due_date'     => $dueDate,
        ]));

        if ($supply->customer_id) Customer::findOrFail($supply->customer_id)->decrement('current_balance', $oldTotal);
        Customer::findOrFail($data['customer_id'])->increment('current_balance', $newTotal);

        return redirect()->route('supply.show', $supply)->with('success', 'Order updated.');
    }

    public function destroy(SupplyOrder $supply)
    {
        abort_if($supply->payment_status !== 'unpaid', 403, 'Only unpaid orders can be deleted.');
        if ($supply->customer_id) {
            Customer::findOrFail($supply->customer_id)->decrement('current_balance', $supply->total_amount);
        }
        $supply->delete();
        return redirect()->route('supply.index')->with('success', 'Order deleted.');
    }

    public function storePayment(Request $request, SupplyOrder $supply)
    {
        $data = $request->validate([
            'amount'       => 'required|numeric|min:0.01|max:' . $supply->amount_due,
            'payment_date' => 'required|date',
            'method'       => 'required|in:cash,bank,jazzcash,easypaisa',
            'note'         => 'nullable|string',
            'proof'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment-proofs', 'public');
        }

        $supply->payments()->create(array_merge($data, ['proof_path' => $proofPath]));

        $newPaid = $supply->amount_paid + $data['amount'];
        $newDue  = $supply->total_amount - $newPaid;

        $supply->update([
            'amount_paid'    => $newPaid,
            'amount_due'     => max(0, $newDue),
            'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
        ]);

        if ($supply->customer_id) {
            Customer::findOrFail($supply->customer_id)->decrement('current_balance', $data['amount']);
        }

        return redirect()->route('supply.show', $supply)->with('success', 'Payment of PKR ' . number_format($data['amount'], 0) . ' recorded.');
    }

    public function markDelivered(SupplyOrder $supply)
    {
        $supply->update([
            'is_delivered' => true,
            'delivered_at' => now(),
        ]);
        return back()->with('success', 'Order delivered mark ho gaya.');
    }

    private function nextInvoiceNumber(): string
    {
        $year  = date('Y');
        $count = SupplyOrder::whereYear('created_at', $year)->count() + 1;
        return 'SO-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
