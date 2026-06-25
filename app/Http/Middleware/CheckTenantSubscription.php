<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->bound('tenant')) {
            return $next($request);
        }

        $tenant = tenant();

        if (!$tenant->is_active) {
            return response()->view('errors.tenant-suspended', ['tenant' => $tenant], 403);
        }

        if ($tenant->plan_expires_at && $tenant->plan_expires_at->isPast()) {
            return response()->view('errors.tenant-expired', ['tenant' => $tenant], 402);
        }

        return $next($request);
    }
}
