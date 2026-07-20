<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'vendor' => \App\Http\Middleware\VendorMiddleware::class,
            'delivery.panel' => \App\Http\Middleware\DeliveryPanelMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('vendor') || $request->is('vendor/*')) {
                return route('vendor.login');
            }
            if ($request->is('delivery') || $request->is('delivery/*')) {
                return route('delivery.login');
            }

            return route('admin.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Token expired or invalid. Call POST /auth/refresh or login again.',
                    'error' => 'unauthenticated',
                ], 401);
            }
        });

        // 419 CSRF — send user back to the right login with a clear message.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Page expired. Refresh and try again.',
                    'error' => 'csrf_token_mismatch',
                ], 419);
            }

            $login = match (true) {
                $request->is('vendor') || $request->is('vendor/*') => 'vendor.login',
                $request->is('delivery') || $request->is('delivery/*') => 'delivery.login',
                default => 'admin.login',
            };

            return redirect()
                ->route($login)
                ->with('status', 'Your login session expired. Please sign in again.');
        });
    })->create();
