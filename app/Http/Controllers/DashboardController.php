<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\Customer;
use App\Models\DailyRecord;
use App\Models\Expense;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SupplyOrder;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySupply    = SupplyOrder::whereDate('date', today())->sum('total_amount');
        $todayPurchases = PurchaseOrder::whereDate('date', today())->sum('total_amount');
        $todayExpenses  = Expense::whereDate('date', today())->sum('amount');

        $todaySupplyCount    = SupplyOrder::whereDate('date', today())->count();
        $todayPurchaseCount  = PurchaseOrder::whereDate('date', today())->count();
        $todayExpenseCount   = Expense::whereDate('date', today())->count();

        $todayRecord  = DailyRecord::whereDate('date', today())->first();
        $todayCounter = $todayRecord?->counter_cash ?? 0;
        $todayProfit  = $todayRecord?->net_profit ?? ($todaySupply + $todayCounter - $todayPurchases - $todayExpenses);

        $supplierDue  = Supplier::sum('balance');
        $customerDue  = Customer::sum('current_balance');
        $overdueCount = SupplyOrder::where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->count();

        $monthSupply    = SupplyOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthPurchases = PurchaseOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthExpenses  = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $monthProfit    = $monthSupply - $monthPurchases - $monthExpenses;

        $recentOrders = SupplyOrder::with('customer')
            ->latest('date')
            ->limit(10)
            ->get();

        $overdueOrders = SupplyOrder::with('customer')
            ->where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $monthlyLabels = [];
        $monthlySales  = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlyLabels[] = $m->format('M Y');
            $monthlySales[]  = (float) SupplyOrder::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('total_amount');
        }

        $lowStockProducts   = Product::whereColumn('stock_qty', '<=', 'low_stock_alert')->where('is_active', true)->count();

        $udharTotalDue      = CreditSale::whereIn('status', ['unpaid', 'partial'])->sum('amount_due');
        $udharOverdueCount  = CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->count();
        $udharDueTodayCount = CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', today())->count();
        $udharDueThisWeek   = CreditSale::whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [today(), today()->addDays(7)])->count();

        return view('dashboard.index', compact(
            'todaySupply', 'todayPurchases', 'todayExpenses', 'todayCounter', 'todayProfit',
            'todaySupplyCount', 'todayPurchaseCount', 'todayExpenseCount',
            'supplierDue', 'customerDue', 'overdueCount',
            'monthSupply', 'monthPurchases', 'monthExpenses', 'monthProfit',
            'recentOrders', 'overdueOrders',
            'monthlyLabels', 'monthlySales',
            'udharTotalDue', 'udharOverdueCount', 'udharDueTodayCount', 'udharDueThisWeek',
            'lowStockProducts'
        ));
    }
}
