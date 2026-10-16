<?php

namespace App\Services;

use App\Models\{BookingServis, Notifikasi, PesananMobilMember, Servis, User};

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

    public function forAdminBooking(BookingServis $booking): void
    {
        $booking->loadMissing(['pelanggan', 'kendaraan', 'jenisServis']);

        $customer = $booking->pelanggan?->nama_lengkap ?: 'Member YZA';
        $vehicle = trim(($booking->kendaraan?->merk ?: '') . ' ' . ($booking->kendaraan?->model ?: '')) ?: 'kendaraan member';
        $plate = $booking->kendaraan?->nomor_polisi ?: 'tanpa nomor polisi';
        $service = $booking->jenisServis?->nama_servis ?: 'servis kendaraan';
        $date = $booking->tanggal?->format('d M Y') ?: '-';
        $time = substr((string) $booking->jam, 0, 5);
        $message = $customer . ' mengajukan ' . $service . ' untuk ' . $vehicle . ' · ' . $plate . ' pada ' . $date . ' pukul ' . $time . ' WIB.';

        $adminIds = User::query()
            ->where('status', 'aktif')
            ->whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))
            ->pluck('id_user');

        foreach ($adminIds as $adminId) {
            $exists = Notifikasi::query()
                ->where('id_user', $adminId)
                ->where('tipe', 'booking')
                ->where('referensi_tipe', 'booking_servis')
                ->where('referensi_id', $booking->id_booking_servis)
                ->where('judul', 'Booking Servis Baru')
                ->exists();

            if (!$exists) {
                Notifikasi::create([
                    'id_user' => $adminId,
                    'judul' => 'Booking Servis Baru',
                    'pesan' => $message,
                    'tipe' => 'booking',
                    'referensi_tipe' => 'booking_servis',
                    'referensi_id' => $booking->id_booking_servis,
                    'is_read' => false,
                ]);
            }
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
