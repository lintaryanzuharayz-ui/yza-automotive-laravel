<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleName() === 'admin'; }
    public function rules(): array { return ['kode_supplier' => ['nullable', 'string', 'max:30', Rule::unique('supplier', 'kode_supplier')], 'nama_supplier' => ['required', 'string', 'max:120'], 'no_hp' => ['nullable', 'string', 'max:25'], 'email' => ['nullable', 'email', 'max:120'], 'alamat' => ['nullable', 'string'], 'kontak_person' => ['nullable', 'string', 'max:120']]; }
}
