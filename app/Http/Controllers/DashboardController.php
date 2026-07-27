<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\Customer;
use App\Models\DailyRecord;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PosSale;
use App\Models\ProductPurchase;
use App\Models\PurchaseOrder;
use App\Models\SupplyOrder;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $shopType  = tenant()->shop_type ?? 'general';

        if ($shopType === 'coaching') {
            return redirect()->route('coaching.dashboard');
        }

        $isChicken = $shopType === 'chicken';
        $isProduct = in_array($shopType, ['hardware', 'mobile', 'bike', 'general', 'medical']);

        // Cashiers don't see the profit dashboard — straight to the sales counter
        if ($isProduct && auth()->user()->isCashier()) {
            return redirect()->route('pos.create');
        }

        // ── Common (all shop types) ──────────────────────────────
        $todayExpenses = Expense::whereDate('date', today())->sum('amount');
        $monthExpenses = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $supplierDue   = Supplier::sum('balance');

        $udharTotalDue      = CreditSale::whereIn('status', ['unpaid', 'partial'])->sum('amount_due');
        $udharOverdueCount  = CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->count();
        $udharDueTodayCount = CreditSale::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', today())->count();
        $udharDueThisWeek   = CreditSale::whereIn('status', ['unpaid', 'partial'])->whereBetween('due_date', [today(), today()->addDays(7)])->count();

        $monthlyLabels = [];
        $monthlySales  = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlyLabels[] = $m->format('M');
        }

        // ── CHICKEN SHOP ─────────────────────────────────────────
        if ($isChicken) {
            $todaySales    = SupplyOrder::whereDate('date', today())->sum('total_amount');
            $todayPurchases= PurchaseOrder::whereDate('date', today())->sum('total_amount');
            $todayProfit   = $todaySales - $todayPurchases - $todayExpenses;

            $monthSales    = SupplyOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
            $monthPurchases= PurchaseOrder::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
            $monthProfit   = $monthSales - $monthPurchases - $monthExpenses;

            $customerDue   = Customer::sum('current_balance');
            $overdueCount  = SupplyOrder::where('payment_status', '!=', 'paid')->whereDate('due_date', '<', today())->count();

            $recentOrders  = SupplyOrder::with('customer')->latest('date')->limit(8)->get();
            $overdueOrders = SupplyOrder::with('customer')->where('payment_status', '!=', 'paid')
                                ->whereDate('due_date', '<', today())->orderBy('due_date')->limit(5)->get();

            for ($i = 5; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                $monthlySales[] = (float) SupplyOrder::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('total_amount');
            }

            return view('dashboard.chicken', compact(
                'todaySales', 'todayPurchases', 'todayExpenses', 'todayProfit',
                'monthSales', 'monthPurchases', 'monthExpenses', 'monthProfit',
                'supplierDue', 'customerDue', 'overdueCount',
                'recentOrders', 'overdueOrders',
                'monthlyLabels', 'monthlySales',
                'udharTotalDue', 'udharOverdueCount', 'udharDueTodayCount', 'udharDueThisWeek'
            ));
        }

        // ── PRODUCT SHOPS (hardware / mobile / bike / general) ───
        $todaySales     = PosSale::whereDate('date', today())->sum('total');
        $todayPurchases = ProductPurchase::whereDate('date', today())->sum('total_amount');
        $todayTxCount   = PosSale::whereDate('date', today())->count();
        $todayProfit    = $todaySales - $todayPurchases - $todayExpenses;

        $monthSales     = PosSale::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total');
        $monthPurchases = ProductPurchase::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthProfit    = $monthSales - $monthPurchases - $monthExpenses;

        $lowStock       = Product::whereColumn('stock_qty', '<=', 'low_stock_alert')->where('is_active', true)->count();
        $totalProducts  = Product::where('is_active', true)->count();
        $outOfStock     = Product::where('stock_qty', 0)->where('is_active', true)->count();

        $supplierDue2   = ProductPurchase::where('payment_status', '!=', 'paid')->sum('amount_due');
        $overdueCount   = ProductPurchase::where('payment_status', '!=', 'paid')->whereDate('due_date', '<', today())->count();

        $recentSales    = PosSale::latest('date')->limit(8)->get();

        // Expiry alerts — for any shop that records batch expiry (medical etc.)
        $expiredCount      = ProductBatch::where('qty', '>', 0)->whereDate('expiry_date', '<', today())->count();
        $expiringSoonCount = ProductBatch::where('qty', '>', 0)
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('expiry_date', '<=', today()->addDays(30))
            ->count();
        $expiringItems = ($expiredCount + $expiringSoonCount) > 0
            ? ProductBatch::with('product')->where('qty', '>', 0)
                ->whereDate('expiry_date', '<=', today()->addDays(30))
                ->orderBy('expiry_date')->limit(5)->get()
            : collect();

        $monthlyProfit = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlySales[] = (float) PosSale::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('total');

            // Gross margin = revenue − cost of goods sold (using current cost as proxy)
            $row = \DB::table('pos_sale_items as i')
                ->join('pos_sales as s', 's.id', '=', 'i.pos_sale_id')
                ->join('products as p', 'p.id', '=', 'i.product_id')
                ->whereYear('s.date', $m->year)->whereMonth('s.date', $m->month)
                ->selectRaw('COALESCE(SUM(i.total),0) as rev, COALESCE(SUM(i.qty * p.cost_price),0) as cogs')
                ->first();
            $monthlyProfit[] = round((float) $row->rev - (float) $row->cogs, 2);
        }

        // Top-selling products this month (by quantity)
        $topProducts = \DB::table('pos_sale_items as i')
            ->join('pos_sales as s', 's.id', '=', 'i.pos_sale_id')
            ->whereYear('s.date', now()->year)->whereMonth('s.date', now()->month)
            ->selectRaw('i.product_name, SUM(i.qty) as qty, SUM(i.total) as revenue')
            ->groupBy('i.product_name')
            ->orderByDesc('qty')
            ->limit(5)->get();

        return view('dashboard.product', compact(
            'todaySales', 'todayPurchases', 'todayExpenses', 'todayProfit', 'todayTxCount',
            'monthSales', 'monthPurchases', 'monthExpenses', 'monthProfit',
            'lowStock', 'totalProducts', 'outOfStock', 'expiringSoonCount', 'expiredCount', 'expiringItems',
            'supplierDue', 'supplierDue2', 'overdueCount',
            'recentSales',
            'monthlyLabels', 'monthlySales', 'monthlyProfit', 'topProducts',
            'udharTotalDue', 'udharOverdueCount', 'udharDueTodayCount', 'udharDueThisWeek',
            'shopType'
        ));
    }
}
