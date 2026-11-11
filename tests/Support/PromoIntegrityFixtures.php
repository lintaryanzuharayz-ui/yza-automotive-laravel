<?php

namespace Tests\Support;

use App\Models\{BookingCuci, InspeksiMobil, Mobil, PengajuanMobil, PromoMember, Servis, User};
use App\Services\{ServisService, ServisSparepartService};

trait PromoIntegrityFixtures
{
    use CompletesQualityChecks;

    private function stage4Actor(): int
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', 'petugas'))->firstOrFail()->petugas->id_petugas;
    }

    private function stage4Promo(array $changes = []): PromoMember
    {
        return PromoMember::forceCreate(array_replace(['kode_promo' => 'S4-'.bin2hex(random_bytes(5)), 'nama' => 'Stage 4 QA',
            'jenis_benefit' => 'persentase', 'nilai_benefit' => '10.00', 'minimum_transaksi' => 0,
            'member_only' => false, 'berlaku_untuk' => 'semua', 'status' => 'aktif',
            'mulai_at' => now()->subDay(), 'selesai_at' => now()->addDay()], $changes));
    }

    private function stage4Work(?int $part = 1): Servis
    {
        $s = Servis::firstOrFail()->replicate();
        $v = $s->pelanggan->kendaraan()->create(['merk' => 'QA', 'model' => 'Atomic promo', 'status' => 'aktif']);
        $s->forceFill(['kode_servis' => 'S4-'.bin2hex(random_bytes(5)), 'id_booking_servis' => null, 'id_transaksi' => null,
            'id_petugas' => $this->stage4Actor(), 'id_kendaraan_pelanggan' => $v->getKey(), 'status' => 'dikerjakan',
            'hasil_pemeriksaan' => 'Diagnosis QA', 'tanggal_selesai' => null])->save();
        app(ServisService::class)->addDetail($s->getKey(), null, 'Jasa QA', '100000', null);
        if ($part) app(ServisSparepartService::class)->add($s->getKey(), $part, 1);
        $this->passQc($s);
        return $s;
    }

    private function stage4Wash(): BookingCuci
    {
        $b = BookingCuci::firstOrFail()->replicate();
        $b->forceFill(['kode_booking' => 'S4-'.bin2hex(random_bytes(5)), 'id_transaksi' => null, 'status' => 'proses', 'harga' => '100000'])->save();
        return $b;
    }

    private function stage4Car(): Mobil
    {
        $car = Mobil::where('status', 'tersedia')->firstOrFail()->replicate();
        $id = bin2hex(random_bytes(5));
        $car->forceFill(['kode_mobil' => 'S4-'.$id, 'model' => 'S4-'.$id, 'nomor_rangka' => 'S4-VIN-'.$id,
            'nomor_mesin' => 'S4-ENG-'.$id, 'harga_jual' => '100000000', 'status' => 'tersedia'])->save();
        return $car;
    }

    private function stage4SaleInput(Mobil $car): array
    {
        return ['id_pelanggan' => User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail()->pelanggan->id_pelanggan,
            'id_mobil' => $car->getKey(), 'diskon' => '0', 'dp' => '0', 'metode_pembelian' => 'tunai'];
    }

    private function stage4Trade(): array
    {
        $p = PengajuanMobil::firstOrFail()->replicate(); $id = bin2hex(random_bytes(5));
        $p->forceFill(['kode_pengajuan' => 'S4-'.$id, 'nomor_rangka' => 'S4-OLD-VIN-'.$id,
            'nomor_mesin' => 'S4-OLD-ENG-'.$id, 'status' => 'penawaran_diberikan'])->save();
        $i = InspeksiMobil::forceCreate(['id_pengajuan' => $p->getKey(), 'id_petugas' => $this->stage4Actor(), 'kilometer' => 1000, 'harga_penawaran' => '25000000']);
        return [$p, $i, $this->stage4Car()];
    }
}
