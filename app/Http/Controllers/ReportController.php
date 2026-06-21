<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\SalaryPayment;
use App\Models\SupplyOrder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from_date', now()->startOfMonth()->toDateString());
        $to   = $request->input('to_date', now()->toDateString());

        // Supply summary
        $salesTotal    = SupplyOrder::whereBetween('date', [$from, $to])->sum('total_amount');
        $salesKg       = SupplyOrder::whereBetween('date', [$from, $to])->sum('dressed_weight_kg');
        $salesDue      = SupplyOrder::whereBetween('date', [$from, $to])->sum('amount_due');

        // Purchase summary
        $purchaseTotal = PurchaseOrder::whereBetween('date', [$from, $to])->sum('total_amount');
        $purchaseKg    = PurchaseOrder::whereBetween('date', [$from, $to])->sum('live_weight_kg');

        // Expenses
        $expensesTotal = Expense::whereBetween('date', [$from, $to])->sum('amount');
        $salariesTotal = SalaryPayment::whereBetween('payment_date', [$from, $to])->sum('amount');

        $grossProfit = $salesTotal - $purchaseTotal;
        $netProfit   = $grossProfit - $expensesTotal - $salariesTotal;

        // Monthly trend
        $monthlyLabels    = [];
        $monthlySales     = [];
        $monthlyPurchases = [];
        $monthlyExpenses  = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[]    = $month->format('M Y');
            $monthlySales[]     = (float) SupplyOrder::whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('total_amount');
            $monthlyPurchases[] = (float) PurchaseOrder::whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('total_amount');
            $monthlyExpenses[]  = (float) Expense::whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('amount');
        }

        // Top customers by supply
        $topCustomers = SupplyOrder::with('customer')
            ->whereBetween('date', [$from, $to])
            ->whereNotNull('customer_id')
            ->selectRaw('customer_id, SUM(total_amount) as total, COUNT(*) as orders')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Expense breakdown by category
        $expenseByCategory = Expense::whereBetween('date', [$from, $to])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('reports.index', compact(
            'from', 'to',
            'salesTotal', 'salesKg', 'salesDue',
            'purchaseTotal', 'purchaseKg',
            'expensesTotal', 'salariesTotal',
            'grossProfit', 'netProfit',
            'monthlyLabels', 'monthlySales', 'monthlyPurchases', 'monthlyExpenses',
            'topCustomers', 'expenseByCategory'
        ));
    }
}
