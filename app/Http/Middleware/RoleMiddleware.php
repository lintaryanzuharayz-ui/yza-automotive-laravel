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
        $allowed = collect($roles)->flatMap(fn (string $role) => explode(',', $role))->map(fn (string $role) => strtolower(trim($role)));
        abort_unless($user && $allowed->contains(fn (string $role) => in_array($role, [$user->roleName(), $user->roleKey()], true)), 403);

        return $next($request);
    }
}
