<?php

namespace App\Support;

use Illuminate\Support\Str;

/** Presentation metadata only. Middleware and domain services retain authorization. */
class AdminNavigation
{
    public const ICONS = [
        'admin.dashboard' => 'dashboard', 'staff.dashboard' => 'dashboard', 'owner.dashboard' => 'dashboard',
        'admin.notifikasi.index' => 'bell', 'admin.aktivitas.index' => 'list-todo',
        'admin.pesanan-mobil.index' => 'clipboard-list', 'admin.penjualan-mobil.index' => 'cart',
        'admin.preorder-mobil.index' => 'calendar-clock', 'admin.mobil.index' => 'car',
        'admin.katalog-showroom.index' => 'grid', 'admin.pdi.index' => 'clipboard-check',
        'admin.pengambilan.index' => 'calendar', 'admin.serah-terima.index' => 'key',
        'admin.finance-dashboard.index' => 'chart', 'admin.payments.index' => 'card',
        'admin.transaksi.index' => 'trade', 'admin.invoices.index' => 'receipt',
        'admin.financial-adjustments.index' => 'scale', 'admin.booking.index' => 'calendar-clock',
        'admin.test-drive.index' => 'steering', 'admin.servis.index' => 'wrench',
        'admin.servis.schedule' => 'calendar', 'admin.servis.personnel' => 'users',
        'admin.booking-servis.index' => 'calendar-clock', 'admin.booking-cuci.index' => 'droplets',
        'admin.sparepart.index' => 'box', 'admin.stok-masuk.index' => 'package-in',
        'admin.trade-in.index' => 'repeat', 'admin.pengajuan-mobil.index' => 'car',
        'admin.pelanggan.index' => 'users', 'admin.membership.index' => 'id-card',
        'admin.garage.index' => 'car', 'admin.promo-member.index' => 'ticket-percent',
        'admin.petugas.index' => 'users', 'admin.roles.index' => 'shield',
        'owner.sales-report' => 'chart', 'owner.payment-report' => 'chart',
        'owner.performance-report' => 'chart', 'owner.service-report' => 'chart',
        'owner.inventory-report' => 'warehouse', 'owner.sales-awards' => 'star',
        'admin.laporan-servis.index' => 'chart', 'admin.audit-log.index' => 'scroll',
        'admin.pengaturan.index' => 'gear', 'account.password' => 'user',
        'sales.dashboard' => 'dashboard', 'sales.awards' => 'star', 'sales.leads.index' => 'users',
        'sales.test-drives.index' => 'steering', 'sales.orders.index' => 'clipboard-list',
        'sales.sales.index' => 'cart', 'sales.targets.index' => 'target',
    ];

    public static function icon(string $route): string
    {
        return self::ICONS[$route] ?? 'info';
    }

    public static function active(string $target, ?string $current = null): bool
    {
        $current ??= (string) request()->route()?->getName();
        if ($target === 'admin.servis.index' && in_array($current, ['admin.servis.schedule', 'admin.servis.personnel'], true)) return false;
        if (str_ends_with($target, '.index')) return Str::is(substr($target, 0, -6).'.*', $current);
        $exports = ['owner.sales-report' => 'owner.sales-export', 'owner.payment-report' => 'owner.payment-export',
            'owner.service-report' => 'owner.service-export', 'owner.performance-report' => 'owner.performance-export',
            'owner.inventory-report' => 'owner.inventory-export'];
        return $target === $current || ($exports[$target] ?? null) === $current;
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin', 'owner' => 'Owner', 'sales' => 'Sales',
            'service_advisor' => 'Service Advisor', 'mechanic' => 'Mekanik', 'warehouse_staff' => 'Staf Gudang',
            'finance' => 'Finance', 'operational_staff' => 'Staf Operasional', 'quality_control' => 'Quality Control',
            'pelanggan' => 'Member', default => Str::headline($role),
        };
    }
}
