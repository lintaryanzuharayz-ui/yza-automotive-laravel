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
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('petugas/login') || $request->is('admin/login') || $request->is('logout')) {
                return redirect()->route('petugas.login')
                    ->withErrors(['identity' => 'Sesi keamanan login telah disegarkan. Silakan masukkan kredensial kembali.']);
            }
            if ($request->is('login')) {
                return redirect()->route('login')
                    ->withErrors(['identity' => 'Sesi telah disegarkan untuk keamanan. Silakan coba login kembali.']);
            }

            return redirect()->back()->withErrors(['error' => 'Sesi telah kedaluwarsa. Silakan muat ulang halaman.']);
        });
    })->create();
