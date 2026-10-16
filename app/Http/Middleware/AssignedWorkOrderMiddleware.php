<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AssignedWorkOrderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->roleKey() === 'mechanic') {
            $servis = $request->route('servis');
            $petugasId = $request->user()->petugas?->id_petugas;
            abort_unless($servis && ((int) $servis->id_petugas === (int) $petugasId || $servis->assignments()->where('id_petugas', $petugasId)->whereNull('unassigned_at')->exists()), 403);
        }
        return $next($request);
    }
}
