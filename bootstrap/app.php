<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

require_once __DIR__.'/../app/Support/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\EnsureActiveAccount::class]);
        $middleware->validateCsrfTokens(except: ['payment/midtrans/notification']);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'module.access' => \App\Http\Middleware\RoleModuleAccessMiddleware::class,
            'assigned.wo' => \App\Http\Middleware\AssignedWorkOrderMiddleware::class,
            'member' => \App\Http\Middleware\EnsureActiveMembership::class,
            'verified.owner' => \App\Http\Middleware\EnsureVerifiedCarOwnership::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handleCsrf = function (\Illuminate\Http\Request $request) {
            try {
                if ($request->hasSession()) {
                    $request->session()->regenerateToken();
                }
            } catch (\Throwable) {}

            if ($request->is('petugas/login') || $request->is('admin/login') || $request->is('petugas/*') || $request->is('admin/*') || $request->is('logout')) {
                return redirect()->route('petugas.login')
                    ->withErrors(['identity' => 'Sesi keamanan login telah diperbarui. Silakan masukkan kredensial kembali.']);
            }
            if ($request->is('login') || $request->is('pelanggan/*')) {
                return redirect()->route('login')
                    ->withErrors(['identity' => 'Sesi login telah diperbarui. Silakan coba masuk kembali.']);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Sesi Anda telah diperbarui. Silakan ulangi aksi Anda.']);
        };

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) use ($handleCsrf) {
            return $handleCsrf($request);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) use ($handleCsrf) {
            if ($e->getStatusCode() === 419) {
                return $handleCsrf($request);
            }
        });
    })->create();
