<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class TenantOnboarding
{
    public function handle(Request $request, Closure $next)
    {
        if (!tenancy()->initialized) {
            return $next($request);
        }

        // Skip if already on setup or settings route
        if ($request->routeIs('setup.*') || $request->routeIs('settings.*') || $request->routeIs('impersonate.*')) {
            return $next($request);
        }

        // If company_name is not configured, redirect to setup
        if (!Setting::getValue('company_name')) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
