<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->fresh()?->status !== 'aktif') {
            // Revoke this request's authentication without cycling anyone's password/token.
            Auth::guard('web')->logoutCurrentDevice();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            abort(403, 'Akun tidak aktif. Silakan hubungi administrator.');
        }
        return $next($request);
    }
}
