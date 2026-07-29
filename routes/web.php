<?php

use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// ─── Landing page ─────────────────────────────────────────────────────────────
Route::get('/', fn() => view('landing'))->name('home');

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
    Route::post('/tenants/{tenant}/impersonate', [ImpersonateController::class, 'start'])->name('tenants.impersonate');
    Route::get('/plans', fn() => view('admin.plans'))->name('plans');
    Route::get('/logs', [\App\Http\Controllers\Admin\LogViewerController::class, 'index'])->name('logs');
});
