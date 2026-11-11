<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StorePenjualanMobilRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->roleKey() !== 'owner'
            && ($user->hasPermission('sales.manage') || ($this->routeIs('petugas.*') && $user->roleName() === 'petugas'));
    }
    public function rules(): array
    {
        return [
            'id_pelanggan' => ['required', 'integer', 'exists:pelanggan,id_pelanggan'],
            'id_mobil' => ['required', 'integer', 'exists:mobil,id_mobil'],
            'id_pesanan' => ['required', 'integer', 'exists:pesanan_mobil_member,id_pesanan'],
            'id_booking' => ['nullable', 'integer', 'exists:booking_mobil,id_booking'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'dp' => ['nullable', 'numeric', 'min:0'],
            'metode_pembelian' => ['nullable', 'in:tunai,kredit'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'promo_id' => ['nullable', 'integer', 'exists:promo_member,id_promo'],
            'submission_key' => ['nullable', 'uuid'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $customerId = $this->input('id_pelanggan');
            if ($customerId && !app(\App\Services\ActiveMemberCustomerService::class)->query()->whereKey($customerId)->exists()) {
                $validator->errors()->add('id_pelanggan', 'Transaksi penjualan mobil hanya dapat dibuat untuk YZA Member aktif dengan pembayaran yang sah.');
                return;
            }

            $orderId = $this->input('id_pesanan');
            if (!$orderId) {
                $validator->errors()->add('id_pesanan', 'Penjualan kendaraan baru wajib memiliki referensi pesanan/SPK Member yang sah.');
                return;
            }

            $order = \App\Models\PesananMobilMember::find($orderId);
            if (!$order || $order->status !== 'menunggu_pembayaran' || !$order->id_penjualan) {
                $validator->errors()->add('id_pesanan', 'Pesanan/SPK Member belum disetujui atau belum memiliki transaksi penjualan.');
                return;
            }

            if ($customerId && (int) $order->id_pelanggan !== (int) $customerId) {
                $validator->errors()->add('id_pesanan', 'ID Pelanggan tidak cocok dengan pemesan pada SPK.');
            }

            $carId = $this->input('id_mobil');
            if ($carId && (int) $order->id_mobil !== (int) $carId) {
                $validator->errors()->add('id_pesanan', 'Unit mobil tidak cocok dengan kendaraan pada SPK.');
            }
        });
    }
}
