<?php

use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// ─── Central Domain: landing page ────────────────────────────────────────────
Route::get('/', fn() => view('landing'))->name('home');

// ─── Central Admin Panel ──────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'super.admin'])->group(function () {
    Route::get('/', fn() => redirect()->route('admin.tenants.index'));
    Route::resource('tenants', TenantController::class);
    Route::post('/tenants/{tenant}/renew', [TenantController::class, 'renewPlan'])->name('tenants.renew');
    Route::get('/tenants/{tenant}/payments', [TenantController::class, 'payments'])->name('tenants.payments');
    Route::get('/plans', fn() => view('admin.plans'))->name('plans');
});

// ─── Central domain login/logout (only when NOT a tenant subdomain) ───────────
// web.php loads for ALL domains, so guard with a host check so tenant subdomains
// use the login route registered inside routes/tenant.php (with tenancy middleware).
$centralDomains = config('tenancy.central_domains', ['127.0.0.1', 'localhost']);

if (in_array(request()->getHost(), $centralDomains)) {
    Route::middleware('guest')->group(function () use (&$centralDomains) {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });
    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
}
