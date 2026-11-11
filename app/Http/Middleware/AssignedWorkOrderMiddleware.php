<?php

namespace App\Http\Middleware;

use App\Services\WorkOrderAuthorizationService;
use Closure;
use Illuminate\Http\Request;

class AssignedWorkOrderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $service = $request->route('servis');
        abort_unless($request->user() && $service, 403);
        app(WorkOrderAuthorizationService::class)->authorize($request->user(), $service, 'view');
        foreach (['detail_servis', 'servis_sparepart', 'approval'] as $parameter) {
            $child = $request->route($parameter);
            if ($child) abort_unless((int) $child->id_servis === (int) $service->id_servis, 404);
        }
        return $next($request);
    }
}
