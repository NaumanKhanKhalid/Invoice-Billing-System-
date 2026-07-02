<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosSaleReturn;
use App\Models\Product;
use App\Models\ProductPurchase;
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

        if ((tenant()->shop_type ?? 'general') === 'chicken') {
            return $this->chickenReport($from, $to);
        }

        return $this->productReport($from, $to);
    }

    // ── PRODUCT SHOPS (hardware / mobile / bike / general / medical) ──
    private function productReport(string $from, string $to)
    {
        $salesTotal    = PosSale::whereBetween('date', [$from, $to])->sum('total');
        $salesCount    = PosSale::whereBetween('date', [$from, $to])->count();
        $discountTotal = PosSale::whereBetween('date', [$from, $to])->sum('discount');
        $returnsTotal  = PosSaleReturn::whereBetween('date', [$from, $to])->sum('total');

        $purchaseTotal = ProductPurchase::whereBetween('date', [$from, $to])->sum('total_amount');
        $purchaseDue   = ProductPurchase::whereBetween('date', [$from, $to])->sum('amount_due');

        $expensesTotal = Expense::whereBetween('date', [$from, $to])->sum('amount');
        $salariesTotal = SalaryPayment::whereBetween('payment_date', [$from, $to])->sum('amount');

        $grossProfit = $salesTotal - $returnsTotal - $purchaseTotal;
        $netProfit   = $grossProfit - $expensesTotal - $salariesTotal;

        // Top selling products (by revenue) in range
        $topProducts = PosSaleItem::whereHas('sale', fn($q) => $q->whereBetween('date', [$from, $to]))
            ->selectRaw('product_name, SUM(qty) as total_qty, SUM(total) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // Payment method breakdown
        $paymentBreakdown = PosSale::whereBetween('date', [$from, $to])
            ->selectRaw('payment_method, COUNT(*) as orders, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Expense breakdown by category
        $expenseByCategory = Expense::whereBetween('date', [$from, $to])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Low / out-of-stock products (current state, not range-bound)
        $lowStockProducts = Product::whereColumn('stock_qty', '<=', 'low_stock_alert')
            ->where('is_active', true)
            ->orderBy('stock_qty')
            ->limit(10)
            ->get();

        // Current stock valuation
        $stockValueCost = (float) Product::where('is_active', true)
            ->selectRaw('COALESCE(SUM(stock_qty * cost_price), 0) as v')->value('v');
        $stockValueSale = (float) Product::where('is_active', true)
            ->selectRaw('COALESCE(SUM(stock_qty * sale_price), 0) as v')->value('v');

        // Monthly trend (last 6 months)
        $monthlyLabels = $monthlySales = $monthlyPurchases = $monthlyExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[]    = $month->format('M Y');
            $monthlySales[]     = (float) PosSale::whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('total');
            $monthlyPurchases[] = (float) ProductPurchase::whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('total_amount');
            $monthlyExpenses[]  = (float) Expense::whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('amount');
        }

        return view('reports.product', compact(
            'from', 'to',
            'salesTotal', 'salesCount', 'discountTotal', 'returnsTotal',
            'purchaseTotal', 'purchaseDue',
            'expensesTotal', 'salariesTotal',
            'grossProfit', 'netProfit',
            'topProducts', 'paymentBreakdown', 'expenseByCategory',
            'lowStockProducts', 'stockValueCost', 'stockValueSale',
            'monthlyLabels', 'monthlySales', 'monthlyPurchases', 'monthlyExpenses'
        ));
    }

    // ── CHICKEN SHOP ──
    private function chickenReport(string $from, string $to)
    {
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
