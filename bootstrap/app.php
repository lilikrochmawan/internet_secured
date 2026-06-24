<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\CheckLicense::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'payment/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            $isAdmin = str_contains($request->getPathInfo(), 'administrator') || str_contains($request->getRequestUri(), 'administrator');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir, silakan muat ulang halaman.',
                    'redirect' => $isAdmin ? route('admin.login') : route('login')
                ], 419);
            }

            if ($isAdmin) {
                return redirect()->route('admin.login')->withErrors([
                    'csrf' => 'Sesi Anda telah berakhir (CSRF token kedaluwarsa). Silakan masuk kembali.'
                ]);
            }

            return redirect()->route('login')->withErrors([
                'csrf' => 'Sesi Anda telah berakhir (CSRF token kedaluwarsa). Silakan masuk kembali.'
            ]);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                $isAdmin = str_contains($request->getPathInfo(), 'administrator') || str_contains($request->getRequestUri(), 'administrator');

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Sesi Anda telah berakhir, silakan muat ulang halaman.',
                        'redirect' => $isAdmin ? route('admin.login') : route('login')
                    ], 419);
                }

                if ($isAdmin) {
                    return redirect()->route('admin.login')->withErrors([
                        'csrf' => 'Sesi Anda telah berakhir (CSRF token kedaluwarsa). Silakan masuk kembali.'
                    ]);
                }

                return redirect()->route('login')->withErrors([
                    'csrf' => 'Sesi Anda telah berakhir (CSRF token kedaluwarsa). Silakan masuk kembali.'
                ]);
            }
        });
    })->create();
