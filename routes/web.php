<?php

use App\Http\Controllers\CreditSaleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DailyRateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DayEndController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::post('/customers/{customer}/toggle-blacklist', [CustomerController::class, 'toggleBlacklist'])->name('customers.toggle-blacklist');

    // Daily Rates
    Route::get('/daily-rates', [DailyRateController::class, 'index'])->name('daily-rates.index');
    Route::post('/daily-rates', [DailyRateController::class, 'store'])->name('daily-rates.store');
    Route::get('/daily-rates/today', [DailyRateController::class, 'today'])->name('daily-rates.today');

    // Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'storePayment'])->name('purchases.payment');

    // Supply Orders (Hotels / Companies)
    Route::get('/supply', [SupplyController::class, 'index'])->name('supply.index');
    Route::get('/supply/schedule', [SupplyController::class, 'schedule'])->name('supply.schedule');
    Route::get('/supply/create', [SupplyController::class, 'create'])->name('supply.create');
    Route::post('/supply', [SupplyController::class, 'store'])->name('supply.store');
    Route::get('/supply/{supply}', [SupplyController::class, 'show'])->name('supply.show');
    Route::get('/supply/{supply}/edit', [SupplyController::class, 'edit'])->name('supply.edit');
    Route::put('/supply/{supply}', [SupplyController::class, 'update'])->name('supply.update');
    Route::delete('/supply/{supply}', [SupplyController::class, 'destroy'])->name('supply.destroy');
    Route::post('/supply/{supply}/payment', [SupplyController::class, 'storePayment'])->name('supply.payment');
    Route::patch('/supply/{supply}/deliver', [SupplyController::class, 'markDelivered'])->name('supply.deliver');

    // Day End Entry (Din Band Karo — CORE)
    Route::get('/day-end', [DayEndController::class, 'index'])->name('day-end.index');
    Route::get('/day-end/create', [DayEndController::class, 'create'])->name('day-end.create');
    Route::post('/day-end', [DayEndController::class, 'store'])->name('day-end.store');
    Route::get('/day-end/{dayEnd}', [DayEndController::class, 'show'])->name('day-end.show');
    Route::post('/day-end/{dayEnd}/close', [DayEndController::class, 'close'])->name('day-end.close');

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['show']);

    // Staff
    Route::resource('staff', StaffController::class);
    Route::post('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    Route::post('/staff/{staff}/salary', [StaffController::class, 'storeSalary'])->name('staff.salary');

    // Udhar Book (Credit Sales)
    Route::get('/udhar', [CreditSaleController::class, 'index'])->name('udhar.index');
    Route::get('/udhar/create', [CreditSaleController::class, 'create'])->name('udhar.create');
    Route::post('/udhar', [CreditSaleController::class, 'store'])->name('udhar.store');
    Route::get('/udhar/{creditSale}', [CreditSaleController::class, 'show'])->name('udhar.show');
    Route::post('/udhar/{creditSale}/payment', [CreditSaleController::class, 'storePayment'])->name('udhar.payment');
    Route::delete('/udhar/{creditSale}', [CreditSaleController::class, 'destroy'])->name('udhar.destroy');
});

require __DIR__.'/auth.php';
