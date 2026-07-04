<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth'                => \App\Http\Middleware\Authenticate::class,
            'admin'               => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'super.admin'         => \App\Http\Middleware\SuperAdminOnly::class,
            'tenant.subscription' => \App\Http\Middleware\CheckTenantSubscription::class,
            'tenant.onboarding'   => \App\Http\Middleware\TenantOnboarding::class,
            'feature'             => \App\Http\Middleware\EnsureFeatureEnabled::class,
            'owner'               => \App\Http\Middleware\OwnerOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Structured error context: every reported exception carries the
        // tenant, user and URL so multi-tenant debugging doesn't need guesswork
        $exceptions->context(fn () => array_filter([
            'tenant' => tenancy()->initialized ? tenant('id') : null,
            'user'   => auth()->id(),
            'url'    => request()?->fullUrl(),
        ]));
    })->create();
