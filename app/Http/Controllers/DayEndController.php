<?php

namespace App\Http\Controllers;

use App\Models\DailyRecord;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\SupplyOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DayEndController extends Controller
{
    public function index(Request $request)
    {
        $records = DailyRecord::orderByDesc('date')->paginate(30);
        return view('day-end.index', compact('records'));
    }

    public function create(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $supplyTotal   = SupplyOrder::whereDate('date', $date)->sum('total_amount');
        $supplyKg      = SupplyOrder::whereDate('date', $date)->sum('dressed_weight_kg');
        $purchaseLiveKg = PurchaseOrder::whereDate('date', $date)->sum('live_weight_kg');
        $purchaseCost  = PurchaseOrder::whereDate('date', $date)->sum('total_amount');
        $totalExpenses = Expense::whereDate('date', $date)->sum('amount');

        $yesterday  = Carbon::parse($date)->subDay()->toDateString();
        $prevRecord = DailyRecord::where('date', $yesterday)->first();

        return view('day-end.create', compact(
            'date', 'supplyTotal', 'supplyKg', 'purchaseLiveKg',
            'purchaseCost', 'totalExpenses', 'prevRecord'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'                     => 'required|date',
            'opening_stock_live_kg'    => 'required|numeric|min:0',
            'opening_stock_dressed_kg' => 'required|numeric|min:0',
            'total_purchased_live_kg'  => 'required|numeric|min:0',
            'purchase_cost'            => 'required|numeric|min:0',
            'total_supply_dressed_kg'  => 'required|numeric|min:0',
            'total_supply_revenue'     => 'required|numeric|min:0',
            'counter_cash'             => 'required|numeric|min:0',
            'closing_stock_live_kg'    => 'required|numeric|min:0',
            'closing_stock_dressed_kg' => 'required|numeric|min:0',
            'closing_stock_value'      => 'required|numeric|min:0',
            'dead_kg'                  => 'nullable|numeric|min:0',
            'spoilage_kg'              => 'nullable|numeric|min:0',
            'waste_notes'              => 'nullable|string',
            'notes'                    => 'nullable|string',
            'total_expenses'           => 'required|numeric|min:0',
        ]);

        $totalRevenue = $data['total_supply_revenue'] + $data['counter_cash'];
        $totalCost    = $data['purchase_cost'] - $data['closing_stock_value'];
        $grossProfit  = $totalRevenue - $totalCost;
        $netProfit    = $grossProfit - $data['total_expenses'];

        DailyRecord::updateOrCreate(
            ['date' => $data['date']],
            array_merge($data, [
                'total_revenue' => $totalRevenue,
                'total_cost'    => $totalCost,
                'gross_profit'  => $grossProfit,
                'net_profit'    => $netProfit,
                'dead_kg'       => $data['dead_kg'] ?? 0,
                'spoilage_kg'   => $data['spoilage_kg'] ?? 0,
            ])
        );

        return redirect()->route('day-end.index')->with('success', 'Day end entry saved for ' . Carbon::parse($data['date'])->format('d M Y'));
    }

    public function show(DailyRecord $dayEnd)
    {
        return view('day-end.show', compact('dayEnd'));
    }

    public function edit(DailyRecord $dayEnd)
    {
        abort_if($dayEnd->is_closed, 403, 'Cannot edit a closed day record.');
        return view('day-end.edit', compact('dayEnd'));
    }

    public function update(Request $request, DailyRecord $dayEnd)
    {
        abort_if($dayEnd->is_closed, 403, 'Cannot edit a closed day record.');

        $data = $request->validate([
            'opening_stock_live_kg'    => 'required|numeric|min:0',
            'opening_stock_dressed_kg' => 'required|numeric|min:0',
            'total_purchased_live_kg'  => 'required|numeric|min:0',
            'purchase_cost'            => 'required|numeric|min:0',
            'total_supply_dressed_kg'  => 'required|numeric|min:0',
            'total_supply_revenue'     => 'required|numeric|min:0',
            'counter_cash'             => 'required|numeric|min:0',
            'closing_stock_live_kg'    => 'required|numeric|min:0',
            'closing_stock_dressed_kg' => 'required|numeric|min:0',
            'closing_stock_value'      => 'required|numeric|min:0',
            'dead_kg'                  => 'nullable|numeric|min:0',
            'spoilage_kg'              => 'nullable|numeric|min:0',
            'waste_notes'              => 'nullable|string',
            'notes'                    => 'nullable|string',
            'total_expenses'           => 'required|numeric|min:0',
        ]);

        $totalRevenue = $data['total_supply_revenue'] + $data['counter_cash'];
        $totalCost    = $data['purchase_cost'] - $data['closing_stock_value'];
        $grossProfit  = $totalRevenue - $totalCost;
        $netProfit    = $grossProfit - $data['total_expenses'];

        $dayEnd->update(array_merge($data, [
            'total_revenue' => $totalRevenue,
            'total_cost'    => $totalCost,
            'gross_profit'  => $grossProfit,
            'net_profit'    => $netProfit,
            'dead_kg'       => $data['dead_kg'] ?? 0,
            'spoilage_kg'   => $data['spoilage_kg'] ?? 0,
        ]));

        return redirect()->route('day-end.show', $dayEnd)->with('success', 'Day record updated.');
    }

    public function destroy(DailyRecord $dayEnd)
    {
        abort_if($dayEnd->is_closed, 403, 'Cannot delete a closed day record.');
        $dayEnd->delete();
        return redirect()->route('day-end.index')->with('success', 'Day record deleted.');
    }

    public function close(Request $request, DailyRecord $dayEnd)
    {
        abort_if($dayEnd->is_closed, 403, 'Day already closed.');
        $dayEnd->update(['is_closed' => true, 'closed_at' => now()]);
        return redirect()->route('day-end.show', $dayEnd)->with('success', 'Day closed successfully.');
    }
}
