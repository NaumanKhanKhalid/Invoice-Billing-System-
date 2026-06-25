<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Central domain → admin panel, tenant → dashboard
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if (app()->bound('tenant')) {
                return route('dashboard');
            }
            return route('admin.tenants.index');
        });
    }
}
