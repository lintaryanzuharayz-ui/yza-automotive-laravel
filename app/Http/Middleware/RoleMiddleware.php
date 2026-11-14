<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $allowed = collect($roles)->flatMap(fn (string $role) => explode(',', $role))->map(fn (string $role) => strtolower(trim($role)))->values()->all();

        $userRole = $user->roleName();
        $userKey = $user->roleKey();

        if (in_array($userRole, $allowed, true) || in_array($userKey, $allowed, true)) {
            return $next($request);
        }

        if (in_array('admin', $allowed, true) && $user->isAdmin()) {
            return $next($request);
        }

        if (in_array('petugas', $allowed, true) && $user->isStaff()) {
            return $next($request);
        }

        abort(403);
    }
}
