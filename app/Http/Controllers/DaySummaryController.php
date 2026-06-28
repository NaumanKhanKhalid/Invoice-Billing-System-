<?php

namespace App\Http\Controllers;

use App\Models\DaySummary;
use App\Models\Expense;
use App\Models\PosSale;
use App\Models\ProductPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DaySummaryController extends Controller
{
    public function index(Request $request)
    {
        $query = DaySummary::orderByDesc('date');

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $records = $query->paginate(30)->withQueryString();

        $totalRevenue = $records->sum('pos_revenue');
        $totalProfit  = $records->sum('net_profit');

        return view('day-summary.index', compact('records', 'totalRevenue', 'totalProfit'));
    }

    public function create(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $existing = DaySummary::where('date', $date)->first();

        $posSalesCount = PosSale::whereDate('date', $date)->count();
        $posRevenue    = PosSale::whereDate('date', $date)->sum('total');
        $purchaseCost  = ProductPurchase::whereDate('date', $date)->sum('total_amount');
        $totalExpenses = Expense::whereDate('date', $date)->sum('amount');

        // Opening cash = yesterday's closing cash (cash_received) if available
        $yesterday   = Carbon::parse($date)->subDay()->toDateString();
        $prevSummary = DaySummary::where('date', $yesterday)->first();
        $openingCash = $existing?->opening_cash ?? $prevSummary?->cash_received ?? 0;

        $expectedCash = $openingCash + $posRevenue - $totalExpenses;

        return view('day-summary.create', compact(
            'date', 'existing', 'posSalesCount', 'posRevenue',
            'purchaseCost', 'totalExpenses', 'openingCash', 'expectedCash'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'          => 'required|date',
            'opening_cash'  => 'required|numeric|min:0',
            'cash_received' => 'required|numeric|min:0',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $date          = $data['date'];
        $posSalesCount = PosSale::whereDate('date', $date)->count();
        $posRevenue    = PosSale::whereDate('date', $date)->sum('total');
        $purchaseCost  = ProductPurchase::whereDate('date', $date)->sum('total_amount');
        $totalExpenses = Expense::whereDate('date', $date)->sum('amount');

        $expectedCash   = $data['opening_cash'] + $posRevenue - $totalExpenses;
        $cashDifference = $data['cash_received'] - $expectedCash;
        $netProfit      = $posRevenue - $purchaseCost - $totalExpenses;

        $summary = DaySummary::updateOrCreate(
            ['date' => $date],
            [
                'opening_cash'     => $data['opening_cash'],
                'pos_sales_count'  => $posSalesCount,
                'pos_revenue'      => $posRevenue,
                'purchase_cost'    => $purchaseCost,
                'total_expenses'   => $totalExpenses,
                'expected_cash'    => $expectedCash,
                'cash_received'    => $data['cash_received'],
                'cash_difference'  => $cashDifference,
                'net_profit'       => $netProfit,
                'notes'            => $data['notes'] ?? null,
            ]
        );

        return redirect()->route('day-summary.show', $summary)
            ->with('success', 'Day summary saved for ' . Carbon::parse($date)->format('d M Y'));
    }

    public function show(DaySummary $daySummary)
    {
        // Re-fetch live data for display
        $date          = $daySummary->date->toDateString();
        $posSales      = PosSale::whereDate('date', $date)->latest()->take(20)->get();
        $expenses      = Expense::whereDate('date', $date)->get();

        return view('day-summary.show', compact('daySummary', 'posSales', 'expenses'));
    }

    public function close(DaySummary $daySummary)
    {
        abort_if($daySummary->is_closed, 403, 'Day already closed.');
        $daySummary->update(['is_closed' => true, 'closed_at' => now()]);
        return redirect()->route('day-summary.show', $daySummary)->with('success', 'Day closed and locked.');
    }

    public function destroy(DaySummary $daySummary)
    {
        abort_if($daySummary->is_closed, 403, 'Cannot delete a closed day summary.');
        $daySummary->delete();
        return redirect()->route('day-summary.index')->with('success', 'Day summary deleted.');
    }
}
