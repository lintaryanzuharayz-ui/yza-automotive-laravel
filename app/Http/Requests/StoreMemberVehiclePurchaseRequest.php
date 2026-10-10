<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberVehiclePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user(); }
    public function rules(): array
    {
        return ['nama_pembeli' => ['required', 'string', 'max:120'], 'email_pembeli' => ['nullable', 'email', 'max:120'], 'telepon_pembeli' => ['required', 'string', 'max:25'], 'alamat_pembeli' => ['required', 'string', 'max:2000'], 'syarat_disetujui' => ['accepted']];
    }
}
