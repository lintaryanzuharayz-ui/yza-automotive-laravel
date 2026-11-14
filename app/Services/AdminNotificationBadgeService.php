<?php

namespace App\Services;

use App\Models\{BookingMobil, MemberVehiclePreorder, Notifikasi, Pembayaran, PembayaranGateway, PesananMobilMember, Sparepart, TestDrive, TradeIn, User};

class AdminNotificationBadgeService
{
    public function for(int $userId): array
    {
        $user = User::with('role.permissions', 'petugas')->findOrFail($userId);
        $counts = ['notifications' => app(NotificationStateService::class)->query($user)->where('is_read', false)->count()];
        if (in_array($user->roleKey(), ['owner', 'pelanggan'], true)) return $counts;
        $super = $user->roleKey() === 'super_admin';
        $permissions = $user->role?->permissions->pluck('kode')->all() ?? [];
        $can = fn ($permission) => $super || in_array($permission, $permissions, true);
        if ($super) {
            $counts['orders'] = PesananMobilMember::where('status', 'menunggu_verifikasi')->count();
            $counts['preorders'] = MemberVehiclePreorder::active()->count();
            $counts['tradeIns'] = TradeIn::where('status', 'proses')->count();
        } elseif ($user->roleKey() === 'sales' && $can('sales.view')) {
            $counts['orders'] = PesananMobilMember::where('id_sales', $user->petugas?->id_petugas ?: 0)->where('status', 'menunggu_verifikasi')->count();
        }
        if ($can('finance.view')) $counts['payments'] = Pembayaran::where('arah', 'masuk')->where('status', 'menunggu')->count()
            + PembayaranGateway::where('status_provider', 'requires_review')->count();
        if ($can('service.view')) {
            $counts['bookings'] = BookingMobil::where('status', 'menunggu')->count();
            $counts['testDrives'] = TestDrive::where('status', 'menunggu')->count();
        }
        if ($can('inventory.view')) $counts['stock'] = Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->count();
        return $counts;
    }
}
