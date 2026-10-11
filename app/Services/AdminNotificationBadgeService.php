<?php

namespace App\Services;

use App\Models\{BookingMobil, MemberVehiclePreorder, Notifikasi, Pembayaran, PesananMobilMember, Sparepart, TestDrive, TradeIn};

class AdminNotificationBadgeService
{
    public function for(int $userId): array
    {
        return [
            'orders' => PesananMobilMember::where('status', 'menunggu_verifikasi')->count(),
            'preorders' => MemberVehiclePreorder::where('status', 'menunggu_stok')->count(),
            'payments' => Pembayaran::where('status', 'menunggu')->count(),
            'bookings' => BookingMobil::where('status', 'menunggu')->count(),
            'testDrives' => TestDrive::where('status', 'menunggu')->count(),
            'tradeIns' => TradeIn::where('status', 'proses')->count(),
            'stock' => Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->count(),
            'notifications' => Notifikasi::where('id_user', $userId)->where('is_read', false)->count(),
        ];
    }
}
