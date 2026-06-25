<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $superAdminEmail = config('app.super_admin_email', env('SUPER_ADMIN_EMAIL'));

        if (!auth()->check() || auth()->user()->email !== $superAdminEmail) {
            abort(403, 'Super admin access only.');
        }

        return $next($request);
    }
}
