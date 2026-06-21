<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\CreditPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CreditSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditSale::query();

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
            'total_due' => CreditSale::whereIn('status', ['unpaid', 'partial'])->sum('amount_due'),
            'overdue_count' => CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->count(),
            'today_due' => CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', today())->count(),
        ];

        return view('udhar.index', compact('records', 'stats'));
    }

    public function create()
    {
        return view('udhar.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:1',
            'due_days' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $saleDate = Carbon::parse($validated['sale_date']);
        $dueDate = $saleDate->copy()->addDays((int) $validated['due_days']);

        CreditSale::create([
            'customer_name' => $validated['customer_name'],
            'phone' => $validated['phone'],
            'amount' => $validated['amount'],
            'amount_paid' => 0,
            'amount_due' => $validated['amount'],
            'sale_date' => $saleDate,
            'due_date' => $dueDate,
            'description' => $validated['description'],
            'status' => 'unpaid',
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('udhar.index')->with('success', 'Udhar record created successfully.');
    }

    public function show(CreditSale $creditSale)
    {
        $creditSale->load('payments');
        return view('udhar.show', compact('creditSale'));
    }

    public function storePayment(Request $request, CreditSale $creditSale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $creditSale->amount_due,
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank,jazzcash,easypaisa',
            'note' => 'nullable|string|max:255',
        ]);

        CreditPayment::create([
            'credit_sale_id' => $creditSale->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'method' => $validated['method'],
            'note' => $validated['note'],
        ]);

        $newAmountPaid = $creditSale->amount_paid + $validated['amount'];
        $newAmountDue = $creditSale->amount - $newAmountPaid;

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
            'amount_due' => $newAmountDue,
            'status' => $status,
        ]);

        return redirect()->route('udhar.show', $creditSale)->with('success', 'Payment recorded successfully.');
    }

    public function destroy(CreditSale $creditSale)
    {
        if ($creditSale->status !== 'unpaid') {
            return redirect()->route('udhar.index')->with('error', 'Only unpaid records can be deleted.');
        }

        $creditSale->delete();

        return redirect()->route('udhar.index')->with('success', 'Udhar record deleted.');
    }
}
