<?php

use App\Http\Controllers\Admin\TenantController;
use Illuminate\Support\Facades\Route;

// ─── Central Domain: redirect to admin ───────────────────────────────────────
Route::get('/', fn() => redirect()->route('admin.tenants.index'));

// ─── Central Admin Panel ──────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'super.admin'])->group(function () {
    Route::get('/', fn() => redirect()->route('admin.tenants.index'));
    Route::resource('tenants', TenantController::class);
    Route::post('/tenants/{tenant}/renew', [TenantController::class, 'renewPlan'])->name('tenants.renew');
    Route::get('/tenants/{tenant}/payments', [TenantController::class, 'payments'])->name('tenants.payments');
    Route::get('/plans', fn() => view('admin.plans'))->name('plans');
});

require __DIR__.'/auth.php';
