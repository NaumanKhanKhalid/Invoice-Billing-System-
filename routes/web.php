<?php

use App\Http\Controllers\Admin\TenantController;
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

// Only login/logout on central domain — no public registration
Route::middleware('guest')->group(function () {
    Route::get('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
});
Route::middleware('auth')->group(function () {
    Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

