<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();

        $stats = [];
        foreach ($plans as $p) {
            $active = Tenant::where('plan', $p->key)->where('is_active', true)->count();
            $stats[$p->key] = [
                'tenants'  => Tenant::where('plan', $p->key)->count(),
                'active'   => $active,
                'monthly'  => $active * $p->price,
            ];
        }

        $totalMonthly = collect($stats)->sum('monthly');
        $collectedMonth = SubscriptionPayment::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount');

        return view('admin.plans', compact('plans', 'stats', 'totalMonthly', 'collectedMonth'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key'   => 'required|string|max:40|regex:/^[a-z0-9_]+$/|unique:plans,key',
            'name'  => 'required|string|max:60',
            'price' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:-1',
            'staff_module'     => 'nullable|boolean',
            'google_backup'    => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
        ]);
        $data['sort_order'] = (Plan::max('sort_order') ?? 0) + 1;
        Plan::create($this->flags($data, $request));

        return back()->with('success', 'Plan "' . $data['name'] . '" add ho gaya.');
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:60',
            'price' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:-1',
            'staff_module'     => 'nullable|boolean',
            'google_backup'    => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
            'is_popular'       => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
        ]);
        $plan->update($this->flags($data, $request, true));

        // Only one plan can be "most popular"
        if ($request->boolean('is_popular')) {
            Plan::where('id', '!=', $plan->id)->update(['is_popular' => false]);
        }

        return back()->with('success', $plan->name . ' plan update ho gaya.');
    }

    public function destroy(Plan $plan)
    {
        if (Tenant::where('plan', $plan->key)->exists()) {
            return back()->with('error', 'Is plan par tenants hain — pehle unhe move karein.');
        }
        $plan->delete();
        return back()->with('success', 'Plan delete ho gaya.');
    }

    private function flags(array $data, Request $request, bool $withToggles = false): array
    {
        $data['staff_module']     = $request->boolean('staff_module');
        $data['google_backup']    = $request->boolean('google_backup');
        $data['priority_support'] = $request->boolean('priority_support');
        if ($withToggles) {
            $data['is_popular'] = $request->boolean('is_popular');
            $data['is_active']  = $request->boolean('is_active');
        }
        return $data;
    }
}
