<?php

namespace App\Providers;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if (AuthenticatedSessionController::isTenantDomain()) {
                return route('dashboard');
            }
            return route('admin.tenants.index');
        });
    }
}
