<?php

use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// ─── Landing page ─────────────────────────────────────────────────────────────
Route::get('/', fn() => view('landing'))->name('home');

// ─── Language switch (English / Roman Urdu) ───────────────────────────────────
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::SUPPORTED, true)) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

// ─── Admin Login (separate URL — no conflict with tenant /login) ───────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1')->name('admin.login.post');
});
Route::middleware('auth')->group(function () {
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});

// ─── Central Admin Panel ──────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'super.admin'])->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class);
    Route::post('/tenants/{tenant}/renew', [TenantController::class, 'renewPlan'])->name('tenants.renew');
    Route::post('/tenants/{tenant}/toggle-active', [TenantController::class, 'toggleActive'])->name('tenants.toggle-active');
    Route::get('/tenants/{tenant}/payments', [TenantController::class, 'payments'])->name('tenants.payments');
    Route::get('/payments/{payment}/receipt', [TenantController::class, 'paymentReceipt'])->name('payments.receipt');
    Route::post('/tenants/{tenant}/impersonate', [ImpersonateController::class, 'start'])->name('tenants.impersonate');
    Route::get('/plans', [\App\Http\Controllers\Admin\PlanController::class, 'index'])->name('plans');
    Route::post('/plans', [\App\Http\Controllers\Admin\PlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'destroy'])->name('plans.destroy');
    Route::get('/logs', [\App\Http\Controllers\Admin\LogViewerController::class, 'index'])->name('logs');
});
