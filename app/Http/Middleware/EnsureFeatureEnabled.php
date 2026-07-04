<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * Block access to a route group whose feature has been turned off
     * in the tenant's Settings (usage: middleware('feature:open_tabs')).
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!feature_enabled($feature)) {
            $label = config("features.$feature.label", $feature);
            abort(403, "'$label' feature is turned off. Enable it from Settings.");
        }

        return $next($request);
    }
}
