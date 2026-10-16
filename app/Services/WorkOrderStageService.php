<?php

namespace App\Services;

use App\Models\Servis;

class WorkOrderStageService
{
    public const STAGES = ['pemeriksaan', 'persetujuan', 'pengerjaan', 'quality-control', 'finalisasi'];

    public function currentStage(Servis $servis): string
    {
        $servis->loadMissing(['approvalRequests', 'qualityControl']);
        if ($servis->status === 'pemeriksaan') return 'pemeriksaan';
        if ($servis->status === 'dikerjakan' && $servis->approvalRequests->where('status', 'pending')->isNotEmpty()) return 'persetujuan';
        if ($servis->qualityControl && $servis->qualityControl->status !== 'passed') return 'quality-control';
        if ($servis->qualityControl?->status === 'passed' || $servis->status === 'selesai') return 'finalisasi';
        return 'pengerjaan';
    }

    public function canAccess(Servis $servis, string $stage): bool
    {
        $current = array_search($this->currentStage($servis), self::STAGES, true);
        $target = array_search($stage, self::STAGES, true);
        if ($current === false || $target === false) return false;
        if ($target <= $current) return true;
        if ($stage === 'quality-control') return $servis->status === 'dikerjakan' && $servis->approvalRequests->where('status', 'pending')->isEmpty();
        if ($stage === 'finalisasi') return $servis->status === 'dikerjakan' && $servis->approvalRequests->where('status', 'pending')->isEmpty() && (!$servis->qualityControl || $servis->qualityControl->status === 'passed');
        return false;
    }

    public function routeName(string $prefix, string $stage): string
    {
        return $prefix.'.servis.'.$stage;
    }

    public function redirectReason(string $stage): string
    {
        return match ($stage) {
            'persetujuan' => 'Selesaikan pemeriksaan sebelum membuka persetujuan customer.',
            'pengerjaan' => 'Selesaikan persetujuan customer sebelum membuka pengerjaan.',
            'quality-control' => 'Selesaikan persetujuan dan pekerjaan sebelum membuka Quality Control.',
            'finalisasi' => 'Selesaikan Quality Control sebelum melakukan finalisasi.',
            default => 'Tahap Work Order belum dapat dibuka.',
        };
    }

    public function nextAction(Servis $servis): array
    {
        return match ($this->currentStage($servis)) {
            'pemeriksaan' => ['stage' => 'persetujuan', 'description' => 'Hasil pemeriksaan tersimpan. Lanjutkan ke persetujuan pelanggan bila ada pekerjaan tambahan.', 'label' => 'Lanjut ke Persetujuan Pelanggan'],
            'persetujuan' => ['stage' => 'pengerjaan', 'description' => 'Seluruh persetujuan yang diperlukan telah diterima. Perintah Kerja siap memasuki tahap pengerjaan.', 'label' => 'Lanjut ke Pengerjaan'],
            'pengerjaan' => ['stage' => 'quality-control', 'description' => 'Pastikan seluruh pekerjaan, jasa, dan sparepart sudah tercatat sebelum melanjutkan ke pemeriksaan mutu.', 'label' => 'Lanjut ke Pemeriksaan Mutu'],
            'quality-control' => ['stage' => 'finalisasi', 'description' => 'Pastikan hasil pekerjaan telah diperiksa dan memenuhi standar kualitas bengkel YZA Automotive.', 'label' => 'Lanjut ke Finalisasi Servis'],
            default => ['stage' => null, 'description' => 'Periksa estimasi akhir, selesaikan finalisasi, lalu lanjutkan ke proses pembayaran resmi.', 'label' => null],
        };
    }
}
