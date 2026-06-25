<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // tenant.php routes run with InitializeTenancyByDomain middleware
        // so app()->bound('tenant') is reliably true only on tenant subdomains.
        // Admin uses /admin/login (web.php) — no tenancy middleware — so false.
        if (app()->bound('tenant')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return redirect()->intended(route('admin.tenants.index', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
