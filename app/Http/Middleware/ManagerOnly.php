<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Management sections (reports, purchases, expenses, day-close, product
 * management, settings…) are for owners/managers. Cashiers are restricted
 * to the sales counter (POS, sales history, udhar collection, customers).
 */
class ManagerOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->canManage()) {
            abort(403, 'Ye section sirf owner/manager ke liye hai.');
        }

        return $next($request);
    }
}
