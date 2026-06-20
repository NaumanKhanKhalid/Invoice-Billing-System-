<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::orderByDesc('date');

        if ($request->category)  $query->where('category', $request->category);
        if ($request->from_date) $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date)   $query->whereDate('date', '<=', $request->to_date);

        $expenses = $query->paginate(25)->withQueryString();

        $stats = [
            'today'       => Expense::whereDate('date', today())->sum('amount'),
            'this_month'  => Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount'),
            'total'       => Expense::sum('amount'),
        ];

        $categories = Expense::distinct()->pluck('category')->filter()->values();

        return view('expenses.index', compact('expenses', 'stats', 'categories'));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'           => 'required|date',
            'category'       => 'required|string|max:100',
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'paid_to'        => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:50',
        ]);

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense of PKR ' . number_format($data['amount'], 0) . ' recorded.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'date'           => 'required|date',
            'category'       => 'required|string|max:100',
            'description'    => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'paid_to'        => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:50',
        ]);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
