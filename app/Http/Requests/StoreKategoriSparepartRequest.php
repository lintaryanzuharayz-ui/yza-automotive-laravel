<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKategoriSparepartRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleName() === 'admin'; }
    public function rules(): array { return ['nama_kategori' => ['required', 'string', 'max:80', Rule::unique('kategori_sparepart', 'nama_kategori')], 'deskripsi' => ['nullable', 'string', 'max:255']]; }
}
