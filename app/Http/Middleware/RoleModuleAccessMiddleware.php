<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleModuleAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->roleKey() === 'super_admin') return $next($request);

        $route = (string) $request->route()?->getName();
        $permission = match (true) {
            str_starts_with($route, 'admin.pembayaran'), str_contains($route, 'payment'), str_contains($route, 'payments'), str_contains($route, 'invoice') => 'finance.view',
            str_starts_with($route, 'admin.servis'), str_starts_with($route, 'admin.booking-servis'), str_starts_with($route, 'admin.booking-cuci'), str_contains($route, 'schedule'), str_contains($route, 'personnel') => 'service.view',
            str_starts_with($route, 'admin.sparepart'), str_starts_with($route, 'admin.inventory'), str_starts_with($route, 'admin.stok-masuk'), str_starts_with($route, 'admin.mobil') => 'inventory.view',
            str_starts_with($route, 'admin.penjualan'), str_starts_with($route, 'admin.pesanan-mobil'), str_starts_with($route, 'admin.preorder') => 'sales.view',
            str_starts_with($route, 'admin.pelanggan'), str_starts_with($route, 'admin.membership') => 'customers.view',
            str_starts_with($route, 'admin.laporan'), str_starts_with($route, 'admin.promo') => 'reports.view',
            default => 'dashboard.view',
        };

        $required = $permission;
        if (!$request->isMethodSafe()) {
            // Mechanics may mutate only their assigned work orders. The
            // assigned.wo middleware and controller-level checks remain the
            // authoritative scope guard for those routes.
            $required = match ($permission) {
                'finance.view' => 'finance.verify',
                'inventory.view' => 'inventory.manage',
                'sales.view' => 'sales.manage',
                'service.view' => 'service.manage',
                default => $permission,
            };

            if ($user?->roleKey() === 'mechanic' && str_starts_with($route, 'admin.servis')) {
                $required = 'service.assigned';
            }

            if ($user?->roleKey() === 'quality_control' && str_contains($route, '.servis.qc.')) {
                $required = 'qc.manage';
            }
        }

        abort_unless($user?->hasPermission($required), 403);
        return $next($request);
    }
}
