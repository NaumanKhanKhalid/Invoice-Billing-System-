<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailyInventory;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's quick stats
        $todaySales     = SalesOrder::whereDate('date', today())->sum('total_amount');
        $todayPurchases = PurchaseOrder::whereDate('date', today())->sum('total_amount');
        $todayExpenses  = Expense::whereDate('date', today())->sum('amount');
        $todayProfit    = $todaySales - $todayPurchases - $todayExpenses;

        // Outstanding balances
        $supplierDue  = Supplier::sum('balance');
        $customerDue  = Customer::sum('current_balance');
        $overdueSales = SalesOrder::where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->count();

        // This month
        $monthSales     = SalesOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthPurchases = PurchaseOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthExpenses  = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $monthProfit    = $monthSales - $monthPurchases - $monthExpenses;

        // Recent sales (last 10)
        $recentSales = SalesOrder::with(['customer', 'chickenType'])
            ->latest('date')
            ->limit(10)
            ->get();

        // Low stock alerts
        $lowStock = DailyInventory::with('chickenType')
            ->whereDate('date', today())
            ->where('closing_stock_kg', '<', 50)
            ->get();

        // Overdue sales
        $overdueOrders = SalesOrder::with('customer')
            ->where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // 6-month sales trend
        $monthlyLabels = [];
        $monthlySales  = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlyLabels[] = $m->format('M Y');
            $monthlySales[]  = (float) SalesOrder::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('total_amount');
        }

        return view('dashboard', compact(
            'todaySales', 'todayPurchases', 'todayExpenses', 'todayProfit',
            'supplierDue', 'customerDue', 'overdueSales',
            'monthSales', 'monthPurchases', 'monthExpenses', 'monthProfit',
            'recentSales', 'lowStock', 'overdueOrders',
            'monthlyLabels', 'monthlySales'
        ));
    }
}
