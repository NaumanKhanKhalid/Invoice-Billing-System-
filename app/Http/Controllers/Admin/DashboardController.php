<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all();

        $activeTenants = $tenants->filter(fn ($t) => $t->is_active && (!$t->plan_expires_at || $t->plan_expires_at >= now()));
        $expired       = $tenants->filter(fn ($t) => $t->plan_expires_at && $t->plan_expires_at < now());

        // Monthly Recurring Revenue estimate from active paid plans (DB-backed plans)
        $mrr = $activeTenants->sum(fn ($t) => (int) plan_value($t->plan, 'price', 0));

        $stats = [
            'total'         => $tenants->count(),
            'active'        => $activeTenants->count(),
            'expired'       => $expired->count(),
            'mrr'           => $mrr,
            'revenue_month' => SubscriptionPayment::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount'),
            'revenue_total' => SubscriptionPayment::sum('amount'),
            'new_this_month'=> $tenants->filter(fn ($t) => $t->created_at && $t->created_at->isSameMonth(now()))->count(),
        ];

        // 6-month revenue + signups trend
        $labels = $revenue = $signups = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $labels[]  = $m->format('M');
            $revenue[] = (float) SubscriptionPayment::whereMonth('paid_at', $m->month)->whereYear('paid_at', $m->year)->sum('amount');
            $signups[] = $tenants->filter(fn ($t) => $t->created_at && $t->created_at->month === $m->month && $t->created_at->year === $m->year)->count();
        }

        // Plan distribution (by actual tenant plan values)
        $planDist = $tenants->groupBy(fn ($t) => $t->plan ?: 'free')->map->count()->sortDesc();

        // Shop-type breakdown
        $typeDist = $tenants->groupBy('shop_type')->map->count()->sortDesc();

        // Expiring within 7 days
        $expiringSoon = $tenants->filter(fn ($t) => $t->plan_expires_at && $t->plan_expires_at > now() && $t->plan_expires_at <= now()->addDays(7))
            ->sortBy('plan_expires_at')->values();

        // Recent signups
        $recent = $tenants->sortByDesc('created_at')->take(6)->values();

        return view('admin.dashboard', compact(
            'stats', 'labels', 'revenue', 'signups', 'planDist', 'typeDist', 'expiringSoon', 'recent'
        ));
    }
}
