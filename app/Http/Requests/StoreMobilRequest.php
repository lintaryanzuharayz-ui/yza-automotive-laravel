<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class StoreMobilRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleName() === 'admin'; }
    public function rules(): array
    {
        return self::baseRules() + ['kode_mobil' => ['required', 'string', 'max:30', Rule::unique('mobil', 'kode_mobil')], 'nomor_rangka' => ['required', 'string', 'max:100', Rule::unique('mobil', 'nomor_rangka')], 'nomor_mesin' => ['required', 'string', 'max:100', Rule::unique('mobil', 'nomor_mesin')]];
    }
    public static function baseRules(): array
    {
        return ['merk' => ['required', 'string', 'max:80'], 'model' => ['required', 'string', 'max:100'], 'tipe' => ['nullable', 'string', 'max:100'], 'tahun' => ['nullable', 'integer', 'between:1886,'.(now()->year + 1)], 'warna' => ['required', 'string', 'max:50'], 'transmisi' => ['nullable', Rule::in(['manual','automatic','cvt','dct','lainnya'])], 'bahan_bakar' => ['nullable', Rule::in(['bensin','diesel','hybrid','listrik','lainnya'])], 'kilometer' => ['required', 'integer', 'min:0'], 'harga_beli' => ['required', 'numeric', 'min:0'], 'harga_jual' => ['required', 'numeric', 'min:0'], 'kondisi' => ['required', Rule::in(['baru','bekas'])], 'status' => ['required', Rule::in(['tersedia','dibooking','dalam_pemeriksaan','terjual','nonaktif'])], 'is_limited_edition' => ['sometimes', 'boolean'], 'deskripsi' => ['nullable', 'string'], 'tanggal_masuk' => ['nullable', 'date_format:Y-m-d'], 'foto' => ['nullable', 'array'], 'foto.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120']];
    }
}
