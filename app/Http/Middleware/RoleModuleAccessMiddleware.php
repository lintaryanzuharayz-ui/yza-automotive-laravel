<?php

namespace App\Http\Middleware;

use App\Services\WorkOrderAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleModuleAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->status === 'aktif', 403);
        $name = (string) $request->route()?->getName();
        $parts = explode('.', $name);
        $module = $parts[1] ?? '';
        $safe = $request->isMethodSafe();
        $selfNotification = in_array($name, ['admin.notifikasi.read', 'petugas.notifikasi.read'], true);
        abort_if(!$safe && $user->roleKey() === 'owner' && !$selfNotification, 403);

        if ($module === 'servis' && $request->route('servis')) {
            $action = implode('.', array_slice($parts, 2));
            $ability = match ($action) {
                'show', 'pemeriksaan', 'persetujuan', 'pengerjaan', 'quality-control', 'finalisasi', 'sparepart-search' => $safe ? 'view' : null,
                'inspection', 'detail.store', 'detail.destroy', 'sparepart.store', 'sparepart.destroy', 'findings.store', 'approvals.store', 'approvals.clarification' => !$safe ? 'work' : null,
                'finalize', 'assignment.update' => !$safe ? 'manage' : null,
                'qc.start', 'qc.update' => !$safe ? 'qc' : null,
                default => null,
            };
            abort_unless($ability, 403);
            app(WorkOrderAuthorizationService::class)->authorize($user, $request->route('servis'), $ability);
            return $next($request);
        }

        if ($user->roleKey() === 'super_admin') return $next($request);
        if (in_array($name, ['admin.pesanan-mobil.pdi.start', 'admin.pesanan-mobil.pdi'], true)) {
            abort_unless($user->hasPermission('qc.manage'), 403);
            return $next($request);
        }
        if ($safe && in_array($name, ['admin.pdi.index', 'admin.pesanan-mobil.index', 'admin.pesanan-mobil.show'], true) && $user->hasPermission('qc.manage')) return $next($request);
        // Preserve the legacy role only on its explicitly allowed surface.
        // Modern advisor accounts using this alias still use the RBAC mapping.
        if (($parts[0] ?? '') === 'petugas' && $user->roleName() === 'petugas') return $next($request);

        [$view, $write] = match ($module) {
            'payment', 'payments', 'car-sale', 'used-car', 'financial-adjustments', 'finance-dashboard', 'transaksi', 'invoice', 'invoices' => ['finance.view', 'finance.verify'],
            'booking-servis', 'booking-cuci', 'servis', 'jenis-servis', 'paket-cuci' => ['service.view', 'service.manage'],
            'sparepart', 'kategori-sparepart', 'supplier', 'inventory', 'stok-masuk', 'mobil', 'katalog-showroom' => ['inventory.view', 'inventory.manage'],
            'booking', 'test-drive', 'pengajuan-mobil', 'pembelian-mobil', 'trade-in', 'penjualan-mobil', 'penjualan-sparepart', 'pesanan-mobil', 'preorder-mobil', 'pdi', 'pengambilan', 'serah-terima' => ['sales.view', 'sales.manage'],
            'pelanggan', 'membership', 'garage' => ['customers.view', 'staff.manage'],
            'laporan-servis', 'promo-analytics', 'promo-member', 'promo-simulations' => ['reports.view', 'staff.manage'],
            'petugas', 'pengaturan', 'roles' => ['staff.manage', 'staff.manage'],
            'audit-log' => ['audit.view', null],
            'aktivitas' => ['reports.view', null],
            'dashboard' => ['dashboard.view', null],
            'notifikasi' => ['dashboard.view', $selfNotification ? 'dashboard.view' : null],
            default => [null, null],
        };
        if (in_array('payment', array_slice($parts, 2), true)) {
            [$view, $write] = ['finance.view', 'finance.verify'];
        }
        $required = $safe ? $view : $write;
        abort_unless($required && $user->hasPermission($required), 403);
        return $next($request);
    }
}
