<?php

namespace App\Http\Controllers;

use App\Models\ChickenType;
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
        $records = DailyRecord::with('chickenType')
            ->orderByDesc('date')
            ->paginate(30);

        return view('day-end.index', compact('records'));
    }

    public function create(Request $request)
    {
        $date         = $request->input('date', today()->toDateString());
        $chickenTypes = ChickenType::where('is_active', true)->get();

        // Pre-fill supply totals from today's supply orders
        $supplyTotals = [];
        foreach ($chickenTypes as $ct) {
            $orders = SupplyOrder::whereDate('date', $date)
                ->where('chicken_type_id', $ct->id)
                ->get();
            $supplyTotals[$ct->id] = [
                'dressed_kg' => $orders->sum('dressed_weight_kg'),
                'revenue'    => $orders->sum('total_amount'),
            ];
        }

        // Today's purchases
        $purchaseTotals = [];
        foreach ($chickenTypes as $ct) {
            $purchases = PurchaseOrder::whereDate('date', $date)
                ->where('chicken_type_id', $ct->id)
                ->get();
            $purchaseTotals[$ct->id] = [
                'live_kg' => $purchases->sum('live_weight_kg'),
                'cost'    => $purchases->sum('total_amount'),
            ];
        }

        // Today's expenses
        $totalExpenses = Expense::whereDate('date', $date)->sum('amount');

        // Previous day's closing stock (opening for today)
        $yesterday = Carbon::parse($date)->subDay()->toDateString();
        $prevRecords = DailyRecord::where('date', $yesterday)->get()->keyBy('chicken_type_id');

        return view('day-end.create', compact(
            'date', 'chickenTypes', 'supplyTotals', 'purchaseTotals',
            'totalExpenses', 'prevRecords'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'                       => 'required|date',
            'chicken_type_id'            => 'required|exists:chicken_types,id',
            'opening_stock_live_kg'      => 'required|numeric|min:0',
            'opening_stock_dressed_kg'   => 'required|numeric|min:0',
            'total_purchased_live_kg'    => 'required|numeric|min:0',
            'purchase_cost'              => 'required|numeric|min:0',
            'total_supply_dressed_kg'    => 'required|numeric|min:0',
            'total_supply_revenue'       => 'required|numeric|min:0',
            'counter_cash'               => 'required|numeric|min:0',
            'closing_stock_live_kg'      => 'required|numeric|min:0',
            'closing_stock_dressed_kg'   => 'required|numeric|min:0',
            'closing_stock_value'        => 'required|numeric|min:0',
            'dead_kg'                    => 'nullable|numeric|min:0',
            'spoilage_kg'                => 'nullable|numeric|min:0',
            'waste_notes'                => 'nullable|string',
            'notes'                      => 'nullable|string',
            'total_expenses'             => 'required|numeric|min:0',
        ]);

        // Calculate profits
        $totalRevenue  = $data['total_supply_revenue'] + $data['counter_cash'];
        $totalCost     = $data['purchase_cost'] - $data['closing_stock_value'];
        $grossProfit   = $totalRevenue - $totalCost;
        $netProfit     = $grossProfit - $data['total_expenses'];

        DailyRecord::updateOrCreate(
            ['date' => $data['date'], 'chicken_type_id' => $data['chicken_type_id']],
            array_merge($data, [
                'total_revenue' => $totalRevenue,
                'total_cost'    => $totalCost,
                'gross_profit'  => $grossProfit,
                'net_profit'    => $netProfit,
                'dead_kg'       => $data['dead_kg'] ?? 0,
                'spoilage_kg'   => $data['spoilage_kg'] ?? 0,
            ])
        );

        return redirect()->route('day-end.index')
            ->with('success', 'Day end entry saved for ' . Carbon::parse($data['date'])->format('d M Y'));
    }

    public function show(DailyRecord $dayEnd)
    {
        $dayEnd->load('chickenType');
        return view('day-end.show', compact('dayEnd'));
    }

    public function close(Request $request, DailyRecord $dayEnd)
    {
        abort_if($dayEnd->is_closed, 403, 'Day already closed.');
        $dayEnd->update(['is_closed' => true, 'closed_at' => now()]);

        return redirect()->route('day-end.show', $dayEnd)
            ->with('success', 'Day closed successfully.');
    }
}
