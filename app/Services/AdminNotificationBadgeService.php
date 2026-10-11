<?php

namespace App\Services;

use App\Models\{BookingMobil, MemberVehiclePreorder, Notifikasi, Pembayaran, PembayaranGateway, PesananMobilMember, Sparepart, TestDrive, TradeIn};

class AdminNotificationBadgeService
{
    public function for(int $userId): array
    {
        return [
            'orders' => PesananMobilMember::where('status', 'menunggu_verifikasi')->count(),
            'preorders' => MemberVehiclePreorder::where('status', 'menunggu_stok')->count(),
            'payments' => Pembayaran::where('arah', 'masuk')->where('status', 'menunggu')->count() + PembayaranGateway::where('status_provider', 'requires_review')->count(),
            'bookings' => BookingMobil::where('status', 'menunggu')->count(),
            'testDrives' => TestDrive::where('status', 'menunggu')->count(),
            'tradeIns' => TradeIn::where('status', 'proses')->count(),
            'stock' => Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->count(),
            'notifications' => Notifikasi::where('id_user', $userId)->where('is_read', false)->count(),
        ];
    }
}
