<?php

namespace App\Services;

use App\Models\{Notifikasi, PesananMobilMember, Servis};

class ServiceNotificationService
{
    public function forService(Servis $service, string $title, string $message): void
    {
        $userId = $service->pelanggan?->id_user
            ?? $service->pelanggan()->value('id_user');

        if (!$userId) {
            return;
        }

        $exists = Notifikasi::query()
            ->where('id_user', $userId)
            ->where('tipe', 'servis')
            ->where('referensi_tipe', 'servis')
            ->where('referensi_id', $service->id_servis)
            ->where('judul', $title)
            ->exists();

        if (!$exists) {
            Notifikasi::create([
                'id_user' => $userId,
                'judul' => $title,
                'pesan' => $message,
                'tipe' => 'servis',
                'referensi_tipe' => 'servis',
                'referensi_id' => $service->id_servis,
                'is_read' => false,
            ]);
        }
    }

    public function forBooking(int $userId, int $bookingId, string $title, string $message): void
    {
        $exists = Notifikasi::query()
            ->where('id_user', $userId)
            ->where('tipe', 'servis')
            ->where('referensi_tipe', 'booking_servis')
            ->where('referensi_id', $bookingId)
            ->where('judul', $title)
            ->exists();

        if (!$exists) {
            Notifikasi::create([
                'id_user' => $userId,
                'judul' => $title,
                'pesan' => $message,
                'tipe' => 'servis',
                'referensi_tipe' => 'booking_servis',
                'referensi_id' => $bookingId,
                'is_read' => false,
            ]);
        }
    }

    public function forMemberVehicleOrder(PesananMobilMember $order, string $title, string $message): void
    {
        $userId = $order->pelanggan?->id_user ?? $order->pelanggan()->value('id_user');
        if (!$userId) return;

        $exists = Notifikasi::query()
            ->where('id_user', $userId)
            ->where('tipe', 'transaksi')
            ->where('referensi_tipe', 'pesanan_mobil_member')
            ->where('referensi_id', $order->id_pesanan)
            ->where('judul', $title)
            ->exists();

        if (!$exists) {
            Notifikasi::create([
                'id_user' => $userId,
                'judul' => $title,
                'pesan' => $message,
                'tipe' => 'transaksi',
                'referensi_tipe' => 'pesanan_mobil_member',
                'referensi_id' => $order->id_pesanan,
                'is_read' => false,
            ]);
        }
    }
}
