<?php
namespace App\Http\Requests;
use Illuminate\Validation\Rule;
class UpdateKendaraanPelangganRequest extends \App\Http\Requests\StoreKendaraanPelangganRequest
{
    public function rules(): array
    {
        // Identitas kendaraan resmi hanya boleh diubah melalui proses verifikasi admin/mitra.
        return ['kilometer' => ['required', 'integer', 'min:0']];
    }
}
