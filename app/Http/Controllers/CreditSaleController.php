<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\CreditPayment;
use App\Models\UdharCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CreditSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditSale::with('udharCustomer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $status = $request->get('status', 'all');
        if ($status === 'unpaid') {
            $query->where('status', 'unpaid');
        } elseif ($status === 'overdue') {
            $query->whereIn('status', ['unpaid', 'partial'])
                  ->whereDate('due_date', '<', today());
        }

        $records = $query->orderBy('due_date')->paginate(20)->withQueryString();

        $stats = [
            'total_due'     => CreditSale::whereIn('status', ['unpaid', 'partial'])->sum('amount_due'),
            'overdue_count' => CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->count(),
            'today_due'     => CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', today())->count(),
        ];

        return view('udhar.index', compact('records', 'stats'));
    }

    public function create(Request $request)
    {
        $udharCustomers = UdharCustomer::orderBy('name')->get();
        $selectedCustomer = $request->filled('customer_id')
            ? UdharCustomer::find($request->customer_id)
            : null;

        return view('udhar.create', compact('udharCustomers', 'selectedCustomer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'udhar_customer_id' => 'nullable|exists:udhar_customers,id',
            'customer_name'     => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'amount'            => 'required|numeric|min:1',
            'due_days'          => 'required|integer|min:1',
            'description'       => 'nullable|string|max:255',
            'sale_date'         => 'required|date',
            'notes'             => 'nullable|string',
        ]);

        $saleDate = Carbon::parse($validated['sale_date']);
        $dueDate  = $saleDate->copy()->addDays((int) $validated['due_days']);

        $sale = CreditSale::create([
            'udhar_customer_id' => $validated['udhar_customer_id'] ?? null,
            'customer_name'     => $validated['customer_name'],
            'phone'             => $validated['phone'],
            'amount'            => $validated['amount'],
            'amount_paid'       => 0,
            'amount_due'        => $validated['amount'],
            'sale_date'         => $saleDate,
            'due_date'          => $dueDate,
            'description'       => $validated['description'],
            'status'            => 'unpaid',
            'notes'             => $validated['notes'],
        ]);

        // update customer totals
        if ($sale->udhar_customer_id) {
            $customer = UdharCustomer::find($sale->udhar_customer_id);
            $customer->increment('total_given', $validated['amount']);
            $customer->increment('current_balance', $validated['amount']);
        }

        return redirect()->route('udhar.index')->with('success', 'Udhar record created successfully.');
    }

    public function show(CreditSale $creditSale)
    {
        $creditSale->load(['payments', 'udharCustomer']);
        return view('udhar.show', compact('creditSale'));
    }

    public function storePayment(Request $request, CreditSale $creditSale)
    {
        $validated = $request->validate([
            'amount'       => 'required|numeric|min:0.01|max:' . $creditSale->amount_due,
            'payment_date' => 'required|date',
            'method'       => 'required|in:cash,bank,jazzcash,easypaisa',
            'note'         => 'nullable|string|max:255',
            'proof_photo'  => 'nullable|image|max:4096',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_photo')) {
            $proofPath = $request->file('proof_photo')->store('udhar-proofs', 'public');
        }

        CreditPayment::create([
            'credit_sale_id' => $creditSale->id,
            'amount'         => $validated['amount'],
            'payment_date'   => $validated['payment_date'],
            'method'         => $validated['method'],
            'note'           => $validated['note'],
            'proof_photo'    => $proofPath,
        ]);

        $newAmountPaid = $creditSale->amount_paid + $validated['amount'];
        $newAmountDue  = $creditSale->amount - $newAmountPaid;

        if ($newAmountDue <= 0) {
            $status = 'paid';
            $newAmountDue = 0;
        } elseif ($newAmountPaid > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        $creditSale->update([
            'amount_paid' => $newAmountPaid,
            'amount_due'  => $newAmountDue,
            'status'      => $status,
        ]);

        // update customer totals
        if ($creditSale->udhar_customer_id) {
            $customer = UdharCustomer::find($creditSale->udhar_customer_id);
            $customer->increment('total_received', $validated['amount']);
            $customer->decrement('current_balance', $validated['amount']);
        }

        return redirect()->route('udhar.show', $creditSale)->with('success', 'Payment recorded successfully.');
    }

    public function destroy(CreditSale $creditSale)
    {
        if ($creditSale->status !== 'unpaid') {
            return redirect()->route('udhar.index')->with('error', 'Only unpaid records can be deleted.');
        }

        // reverse customer totals
        if ($creditSale->udhar_customer_id) {
            $customer = UdharCustomer::find($creditSale->udhar_customer_id);
            $customer->decrement('total_given', $creditSale->amount);
            $customer->decrement('current_balance', $creditSale->amount);
        }

        $creditSale->delete();

        return redirect()->route('udhar.index')->with('success', 'Udhar record deleted.');
    }

    public function report(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $sales = CreditSale::whereMonth('sale_date', $month)->whereYear('sale_date', $year)->get();

        $totalGiven    = $sales->sum('amount');
        $totalReceived = $sales->sum('amount_paid');
        $netDue        = $sales->sum('amount_due');

        // customer breakdown (use udhar_customer_id if set, else group by customer_name)
        $customerBreakdown = $sales->groupBy(fn($s) => $s->customer_name)
            ->map(fn($group) => [
                'name'          => $group->first()->customer_name,
                'total_given'   => $group->sum('amount'),
                'total_received'=> $group->sum('amount_paid'),
                'balance'       => $group->sum('amount_due'),
                'count'         => $group->count(),
            ])->sortByDesc('balance')->values();

        // payments received this month
        $paymentsThisMonth = CreditPayment::whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->sum('amount');

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::create()->month($m)->format('F');
        }
        $years = range(now()->year - 2, now()->year + 1);

        return view('udhar.report', compact(
            'month', 'year', 'totalGiven', 'totalReceived', 'netDue',
            'customerBreakdown', 'paymentsThisMonth', 'months', 'years'
        ));
    }
}
