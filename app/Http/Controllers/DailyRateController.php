<?php
namespace App\Http\Controllers;
use App\Models\ChickenType;
use App\Models\DailyRate;
use Illuminate\Http\Request;

class DailyRateController extends Controller
{
    public function index(Request $request)
    {
        $chickenTypes  = ChickenType::where('is_active', true)->get();
        $today         = today()->toDateString();
        $todayRates    = DailyRate::where('date', $today)->with('chickenType')->get()->keyBy('chicken_type_id');
        $todayComplete = $chickenTypes->every(fn($t) => $todayRates->has($t->id));

        $query = DailyRate::with('chickenType')->orderByDesc('date')->orderBy('chicken_type_id');
        if ($request->filled('chicken_type_id')) {
            $query->where('chicken_type_id', $request->chicken_type_id);
        }
        $rates = $query->paginate(30)->withQueryString();

        return view('daily-rates.index', compact('chickenTypes','todayRates','todayComplete','rates'));
    }

    public function store(Request $request)
    {
        $request->validate(['rates' => 'required|array']);
        foreach ($request->rates as $typeId => $rateData) {
            if (empty($rateData['rate_per_kg'])) continue;
            DailyRate::updateOrCreate(
                ['chicken_type_id' => $typeId, 'date' => $rateData['date'] ?? today()->toDateString()],
                [
                    'rate_per_kg'         => $rateData['rate_per_kg'],
                    'rate_per_kg_dressed' => $rateData['rate_per_kg_dressed'] ?? 0,
                    'notes'               => $rateData['notes'] ?? null,
                ]
            );
        }
        return back()->with('success', 'Rates saved successfully.');
    }

    public function today()
    {
        $rates = DailyRate::where('date', today()->toDateString())->with('chickenType')->get();
        if ($rates->isEmpty()) {
            $rates = DailyRate::whereDate('date','<',today())
                ->orderByDesc('date')
                ->with('chickenType')
                ->get()
                ->unique('chicken_type_id');
        }
        return response()->json($rates->values());
    }
}
