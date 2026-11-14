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

        $userRole = $user->roleName();

        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $allowedRoles[] = strtolower(trim($subRole));
            }
        }

        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        if (in_array('admin', $allowedRoles, true) && $user->isAdmin()) {
            return $next($request);
        }

        if (in_array('petugas', $allowedRoles, true) && $user->isStaff()) {
            return $next($request);
        }

        abort(403);
    }
}
