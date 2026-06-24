<?php
namespace App\Http\Controllers;
use App\Models\DailyRate;
use Illuminate\Http\Request;

class DailyRateController extends Controller
{
    public function index(Request $request)
    {
        $today         = today()->toDateString();
        $todayRate     = DailyRate::where('date', $today)->first();
        $todayComplete = $todayRate !== null;
        $rates         = DailyRate::orderByDesc('date')->paginate(30);

        return view('daily-rates.index', compact('todayRate','todayComplete','rates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'live_rate_per_kg'   => 'required|numeric|min:0',
            'retail_rate_per_kg' => 'nullable|numeric|min:0',
            'supply_rate_per_kg' => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        DailyRate::updateOrCreate(
            ['date' => today()->toDateString()],
            [
                'live_rate_per_kg'   => $data['live_rate_per_kg'],
                'retail_rate_per_kg' => $data['retail_rate_per_kg'] ?? 0,
                'supply_rate_per_kg' => $data['supply_rate_per_kg'] ?? 0,
                'notes'              => $data['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Rates saved successfully.');
    }

    public function destroy(DailyRate $dailyRate)
    {
        $dailyRate->delete();
        return redirect()->route('daily-rates.index')->with('success', 'Rate entry deleted.');
    }

    public function today()
    {
        $rate = DailyRate::where('date', today()->toDateString())->first()
             ?? DailyRate::whereDate('date','<',today())->orderByDesc('date')->first();
        return response()->json($rate);
    }
}
