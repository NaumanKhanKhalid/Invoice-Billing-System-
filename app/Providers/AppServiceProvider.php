<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if (tenancy()->initialized) {
                return route('dashboard');
            }
            return route('admin.tenants.index');
        });
    }
}
