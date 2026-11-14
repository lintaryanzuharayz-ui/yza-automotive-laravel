<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSparepartFitmentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleKey() === 'super_admin'; }
    public function rules(): array { return ['id_kendaraan_pelanggan' => ['nullable', 'integer', 'exists:kendaraan_pelanggan,id_kendaraan_pelanggan'], 'merk_kendaraan' => ['nullable', 'string', 'max:80'], 'model_kendaraan' => ['nullable', 'string', 'max:120'], 'tahun_mulai' => ['nullable', 'integer', 'min:1900', 'max:2200'], 'tahun_akhir' => ['nullable', 'integer', 'min:1900', 'max:2200', 'gte:tahun_mulai'], 'varian' => ['nullable', 'string', 'max:120'], 'kode_mesin' => ['nullable', 'string', 'max:80'], 'nomor_part_referensi' => ['nullable', 'string', 'max:100', 'required_if:status_verifikasi,verified_fit'], 'status_verifikasi' => ['required', 'in:verified_fit,unverified,verified_not_fit'], 'sumber_bukti' => ['nullable', 'string', 'max:255', 'required_if:status_verifikasi,verified_fit'], 'catatan' => ['nullable', 'string']]; }
}
