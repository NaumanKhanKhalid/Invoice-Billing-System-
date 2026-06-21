<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailyRecord;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\SupplyOrder;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's quick stats
        $todaySupply    = SupplyOrder::whereDate('date', today())->sum('total_amount');
        $todayPurchases = PurchaseOrder::whereDate('date', today())->sum('total_amount');
        $todayExpenses  = Expense::whereDate('date', today())->sum('amount');

        // Today's day-end record (if closed)
        $todayRecord  = DailyRecord::whereDate('date', today())->first();
        $todayCounter = $todayRecord?->counter_cash ?? 0;
        $todayProfit  = $todayRecord?->net_profit ?? ($todaySupply + $todayCounter - $todayPurchases - $todayExpenses);

        // Outstanding balances
        $supplierDue  = Supplier::sum('balance');
        $customerDue  = Customer::sum('current_balance');
        $overdueCount = SupplyOrder::where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->count();

        // This month
        $monthSupply    = SupplyOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthPurchases = PurchaseOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthExpenses  = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $monthProfit    = $monthSupply - $monthPurchases - $monthExpenses;

        // Recent supply orders (last 10)
        $recentOrders = SupplyOrder::with(['customer', 'chickenType'])
            ->latest('date')
            ->limit(10)
            ->get();

        // Overdue supply orders
        $overdueOrders = SupplyOrder::with('customer')
            ->where('payment_status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // 6-month supply trend
        $monthlyLabels = [];
        $monthlySales  = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlyLabels[] = $m->format('M Y');
            $monthlySales[]  = (float) SupplyOrder::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('total_amount');
        }

        return view('dashboard.index', compact(
            'todaySupply', 'todayPurchases', 'todayExpenses', 'todayCounter', 'todayProfit',
            'supplierDue', 'customerDue', 'overdueCount',
            'monthSupply', 'monthPurchases', 'monthExpenses', 'monthProfit',
            'recentOrders', 'overdueOrders',
            'monthlyLabels', 'monthlySales'
        ));
    }
}
