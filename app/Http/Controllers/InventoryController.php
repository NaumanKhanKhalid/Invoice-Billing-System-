<?php

namespace App\Http\Controllers;

use App\Models\ChickenType;
use App\Models\DailyInventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyInventory::with('chickenType')->orderByDesc('date');

        if ($request->chicken_type_id) $query->where('chicken_type_id', $request->chicken_type_id);
        if ($request->from_date)       $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date)         $query->whereDate('date', '<=', $request->to_date);

        $records = $query->paginate(25)->withQueryString();

        $todayRecords  = DailyInventory::whereDate('date', today())->get();
        $lowStockCount = DailyInventory::whereDate('date', today())->where('closing_stock_kg', '<', 50)->count();

        $stats = [
            'today_stock_kg' => $todayRecords->sum('closing_stock_kg'),
            'low_stock'      => $lowStockCount,
            'total_types'    => $todayRecords->count(),
        ];

        $chickenTypes = ChickenType::where('is_active', true)->get();

        return view('inventory.index', compact('records', 'stats', 'chickenTypes'));
    }

    public function create()
    {
        $chickenTypes = ChickenType::where('is_active', true)->get();
        return view('inventory.create', compact('chickenTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'                  => 'required|date',
            'chicken_type_id'       => 'required|exists:chicken_types,id',
            'opening_stock_kg'      => 'required|numeric|min:0',
            'total_purchased_kg'    => 'nullable|numeric|min:0',
            'total_sold_retail_kg'  => 'nullable|numeric|min:0',
            'total_sold_supply_kg'  => 'nullable|numeric|min:0',
            'dead_kg'               => 'nullable|numeric|min:0',
            'spoilage_kg'           => 'nullable|numeric|min:0',
            'cold_storage_kg'       => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
        ]);

        foreach (['total_purchased_kg','total_sold_retail_kg','total_sold_supply_kg','dead_kg','spoilage_kg','cold_storage_kg'] as $f) {
            $data[$f] = $data[$f] ?? 0;
        }

        $data['closing_stock_kg'] = $data['opening_stock_kg']
            + $data['total_purchased_kg']
            - $data['total_sold_retail_kg']
            - $data['total_sold_supply_kg']
            - $data['dead_kg']
            - $data['spoilage_kg'];

        $inventory = DailyInventory::create($data);

        return redirect()->route('inventory.show', $inventory)->with('success', 'Stock entry saved.');
    }

    public function show(DailyInventory $inventory)
    {
        $inventory->load('chickenType');
        return view('inventory.show', compact('inventory'));
    }

    public function adjust(Request $request, DailyInventory $inventory)
    {
        $data = $request->validate([
            'adjusted_kg' => 'required|numeric',
            'notes'       => 'nullable|string|max:255',
        ]);

        $newClosing = $inventory->closing_stock_kg + $data['adjusted_kg'];
        $prefix = $data['adjusted_kg'] > 0 ? '+' : '';
        $adjNote = "Adj {$prefix}{$data['adjusted_kg']} kg" . ($data['notes'] ? ": {$data['notes']}" : '');
        $newNotes = $inventory->notes ? $inventory->notes . "\n" . $adjNote : $adjNote;

        $inventory->update([
            'closing_stock_kg' => max(0, $newClosing),
            'notes'            => $newNotes,
        ]);

        return redirect()->route('inventory.show', $inventory)->with('success', "Stock adjusted by {$prefix}{$data['adjusted_kg']} kg.");
    }
}
