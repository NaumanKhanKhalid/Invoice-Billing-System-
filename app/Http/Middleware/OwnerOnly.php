<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerOnly
{
    /**
     * Restrict sensitive tenant actions (settings, team, destructive deletes)
     * to the shop owner. Staff roles (manager/cashier) are blocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['owner', 'admin'])) {
            abort(403, 'Only the shop owner can perform this action.');
        }

        return $next($request);
    }
}
