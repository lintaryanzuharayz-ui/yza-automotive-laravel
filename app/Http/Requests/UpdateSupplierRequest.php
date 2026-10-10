<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends StoreSupplierRequest
{
    public function rules(): array { $model = $this->route('supplier'); $id = $model instanceof \App\Models\Supplier ? $model->id_supplier : $model; return ['kode_supplier' => ['required', 'string', 'max:30', Rule::unique('supplier', 'kode_supplier')->ignore($id, 'id_supplier')], 'nama_supplier' => ['required', 'string', 'max:120'], 'no_hp' => ['nullable', 'string', 'max:25'], 'email' => ['nullable', 'email', 'max:120'], 'alamat' => ['nullable', 'string'], 'kontak_person' => ['nullable', 'string', 'max:120']]; }
}
