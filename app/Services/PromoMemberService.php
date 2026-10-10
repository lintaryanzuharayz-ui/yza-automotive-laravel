<?php

namespace App\Services;

use App\Models\PromoMember;
use Illuminate\Support\Carbon;

class PromoMemberService
{
    public function visible(int $limit = 0)
    {
        $query = PromoMember::visible()->orderBy('selesai_at');
        return $limit > 0 ? $query->limit($limit)->get() : $query->paginate(12);
    }
    public function normalize(array $data, int $userId): array
    {
        $data['kode_promo'] = strtoupper(trim($data['kode_promo']));
        $data['created_by'] = $userId;
        $data['mulai_at'] = Carbon::parse($data['mulai_at'], 'Asia/Jakarta');
        $data['selesai_at'] = Carbon::parse($data['selesai_at'], 'Asia/Jakarta');
        if ($data['jenis_benefit'] === 'informasi') $data['nilai_benefit'] = null;
        return $data;
    }
}
