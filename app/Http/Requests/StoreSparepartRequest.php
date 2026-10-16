<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSparepartRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleName() === 'admin'; }
    public function rules(): array { return ['kode_sparepart' => ['required', 'string', 'max:30', Rule::unique('sparepart', 'kode_sparepart')], 'id_kategori' => ['required', 'integer', Rule::exists('kategori_sparepart', 'id_kategori')], 'id_supplier' => ['nullable', 'integer', Rule::exists('supplier', 'id_supplier')], 'nama_sparepart' => ['required', 'string', 'max:150'], 'merk' => ['nullable', 'string', 'max:100'], 'nomor_part_oem' => ['nullable', 'string', 'max:80'], 'nomor_part_aftermarket' => ['nullable', 'string', 'max:80'], 'deskripsi_spesifikasi' => ['nullable', 'string'], 'harga_beli' => ['required', 'numeric', 'min:0'], 'harga_jual' => ['required', 'numeric', 'min:0'], 'stok_minimum' => ['required', 'integer', 'min:0'], 'satuan' => ['required', 'string', 'max:30'], 'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]; }
}
