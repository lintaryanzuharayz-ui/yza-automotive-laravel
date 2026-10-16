<?php

namespace App\Services;

use App\Models\{Servis, ServisSparepart, Sparepart};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ServisSparepartService
{
    private function subtotal(string $price, int $qty): string
    {
        return (string) DB::selectOne('SELECT CAST(? * ? AS DECIMAL(15,2)) AS value', [$price, $qty])->value;
    }

    public function add(int $serviceId, int $sparepartId, int $qty): ServisSparepart
    {
        return DB::transaction(function () use ($serviceId, $sparepartId, $qty): ServisSparepart {
            if ($qty <= 0) throw new RuntimeException('Jumlah harus lebih dari 0.');
            $s = Servis::with(['approvalRequests.items', 'kendaraan'])->lockForUpdate()->findOrFail($serviceId);
            if ($s->status !== 'dikerjakan' || $s->id_transaksi !== null) throw new RuntimeException('Spare part hanya dapat diubah saat servis dikerjakan.');
            $p = Sparepart::where('status', 'aktif')->lockForUpdate()->findOrFail($sparepartId);
            app(SparepartFitmentService::class)->assertUsableForVehicle($p, $s->kendaraan);

            $approvalItems = $s->approvalRequests->flatMap(fn ($request) => $request->items->map(function ($item) use ($request) {
                $item->approval_status = $request->status;
                return $item;
            }));
            if ($approvalItems->isNotEmpty() && !$approvalItems->contains(fn ($item) => $item->approval_status === 'approved' && (int) $item->id_sparepart === $sparepartId)) {
                throw new RuntimeException('Item suku cadang belum memiliki persetujuan yang sesuai.');
            }

            $item = ServisSparepart::where('id_servis', $serviceId)->where('id_sparepart', $sparepartId)->lockForUpdate()->first();
            $newQty = $qty + ($item ? (int) $item->jumlah : 0);
            if ($newQty > (int) $p->stok) throw new RuntimeException('Jumlah melebihi stok tersedia.');
            if ($item) {
                $item->forceFill(['jumlah' => $newQty, 'harga_satuan' => $p->harga_jual, 'subtotal' => $this->subtotal((string) $p->harga_jual, $newQty)])->save();
                return $item;
            }
            $item = new ServisSparepart();
            $item->forceFill(['id_servis' => $serviceId, 'id_sparepart' => $p->id_sparepart, 'jumlah' => $qty, 'harga_satuan' => $p->harga_jual, 'subtotal' => $this->subtotal((string) $p->harga_jual, $qty)])->save();
            return $item;
        });
    }

    public function remove(int $serviceId, int $itemId): void
    {
        DB::transaction(function () use ($serviceId, $itemId): void {
            $s = Servis::lockForUpdate()->findOrFail($serviceId);
            if ($s->status !== 'dikerjakan' || $s->id_transaksi !== null) throw new RuntimeException('Spare part tidak dapat dihapus pada status servis ini.');
            ServisSparepart::where('id_servis', $serviceId)->whereKey($itemId)->firstOrFail()->delete();
        });
    }
}
