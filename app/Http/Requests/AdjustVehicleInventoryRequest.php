<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustVehicleInventoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->status === 'aktif' && $this->user()->roleKey() !== 'owner' && $this->user()->hasPermission('inventory.manage'); }
    public function rules(): array { return ['stok_baru' => ['required', 'integer', 'min:0'], 'alasan' => ['required', 'string', 'min:5', 'max:160'], 'bukti' => ['required', 'string', 'min:5', 'max:500'], 'catatan' => ['nullable', 'string', 'max:2000']]; }
}
