<?php

namespace App\Http\Middleware;

use App\Services\MemberAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveMembership
{
    public function __construct(private readonly MemberAccessService $access)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 401);
        abort_unless($user->roleName() === 'pelanggan', 403);

        // Pending/cancelled customers must retain a secure path to payment
        // recovery. These routes do not expose member services.
        if ($request->routeIs('pelanggan.membership.*')) {
            return $next($request);
        }

        if ($this->access->hasActiveMembership($user)) {
            return $next($request);
        }

        return redirect()
            ->route('pelanggan.membership.activation')
            ->with('warning', 'Aktifkan membership YZA terlebih dahulu untuk menggunakan layanan member.');
    }
}
