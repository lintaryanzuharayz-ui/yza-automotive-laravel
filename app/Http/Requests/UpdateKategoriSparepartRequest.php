<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateKategoriSparepartRequest extends StoreKategoriSparepartRequest
{
    public function rules(): array { $model = $this->route('kategori_sparepart'); $id = $model instanceof \App\Models\KategoriSparepart ? $model->id_kategori : $model; return ['nama_kategori' => ['required', 'string', 'max:80', Rule::unique('kategori_sparepart', 'nama_kategori')->ignore($id, 'id_kategori')], 'deskripsi' => ['nullable', 'string', 'max:255']]; }
}
