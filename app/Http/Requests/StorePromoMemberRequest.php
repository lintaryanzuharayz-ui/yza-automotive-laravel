<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromoMemberRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->roleName() === 'admin'; }
    public function rules(): array
    {
        return [
            'kode_promo' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:promo_member,kode_promo'],
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'jenis_benefit' => ['required', Rule::in(['persentase', 'nominal', 'informasi'])],
            'nilai_benefit' => ['nullable', 'numeric', 'min:0'],
            'minimum_transaksi' => ['required', 'numeric', 'min:0'],
            'maksimum_benefit' => ['nullable', 'numeric', 'min:0'],
            'berlaku_untuk' => ['required', Rule::in(['semua', 'servis', 'cuci_mobil', 'penjualan_sparepart', 'penjualan_mobil', 'trade_in'])],
            'quota' => ['nullable', 'integer', 'min:1'],
            'usage_per_member' => ['nullable', 'integer', 'min:1'],
            'member_only' => ['nullable', 'boolean'],
            'projection_transactions' => ['nullable','integer','min:0'],
            'projection_revenue' => ['nullable','numeric','min:0'],
            'projection_discount' => ['nullable','numeric','min:0'],
            'projection_margin' => ['nullable','numeric'],
            'spk_score' => ['nullable','integer','between:0,100'],
            'spk_recommendation' => ['nullable','string','max:40'],
            'estimated_cost_rate' => ['nullable','numeric','between:0,1'],
            'approved_spk_simulation_id' => ['nullable','string','max:80'],
            'mulai_at' => ['required', 'date'],
            'selesai_at' => ['required', 'date', 'after_or_equal:mulai_at'],
            'syarat_ketentuan' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = $this->input('jenis_benefit'); $value = $this->input('nilai_benefit');
            if ($type === 'persentase' && ((float) $value <= 0 || (float) $value > 100)) $validator->errors()->add('nilai_benefit', 'Persentase harus lebih dari 0 dan maksimal 100.');
            if ($type === 'nominal' && (float) $value <= 0) $validator->errors()->add('nilai_benefit', 'Nominal benefit harus lebih dari 0.');
        });
    }
}
