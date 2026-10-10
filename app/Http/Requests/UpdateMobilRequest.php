<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateMobilRequest extends StoreMobilRequest
{
    public function rules(): array
    {
        $mobil = $this->route('mobil');
        $id = $mobil instanceof \App\Models\Mobil ? $mobil->id_mobil : $mobil;
        $rules = self::baseRules() + ['kode_mobil' => ['required', 'string', 'max:30', Rule::unique('mobil', 'kode_mobil')->ignore($id, 'id_mobil')], 'nomor_rangka' => ['required', 'string', 'max:100', Rule::unique('mobil', 'nomor_rangka')->ignore($id, 'id_mobil')], 'nomor_mesin' => ['required', 'string', 'max:100', Rule::unique('mobil', 'nomor_mesin')->ignore($id, 'id_mobil')]];
        if ($mobil instanceof \App\Models\Mobil && $mobil->status === 'terjual') $rules['status'] = ['required', Rule::in(['terjual'])];
        return $rules;
    }
}
