<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustVehicleInventoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleName() === 'admin'; }
    public function rules(): array { return ['stok_baru' => ['required', 'integer', 'min:0'], 'alasan' => ['required', 'string', 'max:160'], 'catatan' => ['nullable', 'string', 'max:2000']]; }
}
