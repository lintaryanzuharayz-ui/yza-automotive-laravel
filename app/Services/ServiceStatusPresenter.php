<?php

namespace App\Services;

class ServiceStatusPresenter
{
    public static function label(?string $status): string
    {
        return match ($status) {
            'menunggu' => 'Menunggu Konfirmasi',
            'diterima' => 'Booking Diterima',
            'diproses' => 'Work Order Dibuka',
            'pemeriksaan' => 'Pemeriksaan Kendaraan',
            'dikerjakan' => 'Sedang Dikerjakan',
            'menunggu_bayar' => 'Menunggu Pembayaran',
            'belum_bayar' => 'Menunggu Pembayaran',
            'dp' => 'Pembayaran Sebagian',
            'selesai' => 'Servis Selesai',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default => $status ? ucwords(str_replace('_', ' ', $status)) : 'Belum Ditentukan',
        };
    }

    public static function tone(?string $status): string
    {
        return match ($status) {
            'selesai' => 'success',
            'dikerjakan' => 'brand',
            'pemeriksaan' => 'warning',
            'menunggu_bayar', 'belum_bayar', 'dp' => 'dark',
            'ditolak', 'dibatalkan' => 'muted',
            default => 'neutral',
        };
    }

    public static function timeline(?string $status): array
    {
        $steps = [
            ['key' => 'booking', 'label' => 'Booking'],
            ['key' => 'diterima', 'label' => 'Diterima'],
            ['key' => 'pemeriksaan', 'label' => 'Pemeriksaan'],
            ['key' => 'dikerjakan', 'label' => 'Pengerjaan'],
            ['key' => 'pembayaran', 'label' => 'Pembayaran'],
            ['key' => 'selesai', 'label' => 'Selesai'],
        ];

        $position = match ($status) {
            'menunggu' => 0,
            'ditolak', 'dibatalkan' => 0,
            'diterima', 'diproses' => 1,
            'pemeriksaan' => 2,
            'dikerjakan' => 3,
            'menunggu_bayar', 'belum_bayar', 'dp' => 4,
            'selesai' => 5,
            default => 0,
        };

        return collect($steps)->map(function (array $step, int $index) use ($position, $status): array {
            return $step + [
                'complete' => $index < $position || ($index === 0 && $status !== null),
                'current' => $index === $position && !in_array($status, ['ditolak', 'dibatalkan'], true),
            ];
        })->all();
    }
}
