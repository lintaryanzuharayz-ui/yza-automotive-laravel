@if((float) $order->manual_discount_value > 0)
<section class="spk-review-card">
    <strong>Pengajuan diskon SPK — {{ $order->manual_discount_status === 'rejected' ? 'Ditolak' : 'Menunggu persetujuan' }}</strong>
    <p>Potongan manual: Rp {{ number_format((float) $order->manual_discount_amount,2,',','.') }}. Estimasi pengajuan: Rp {{ number_format((float) $order->snapshot_harga - (float) $order->manual_discount_amount,2,',','.') }}.</p>
    <p>Alasan: {{ $order->manual_discount_reason }}<br>Pengaju: akun #{{ $order->manual_discount_requested_by }}. Harga minimum/margin belum ditetapkan; potongan belum disahkan dan tidak dapat dipakai untuk menerbitkan invoice.</p>
    @if($order->status === 'menunggu_verifikasi' && !$order->id_transaksi && auth()->user()->hasPermission('sales.manage') && auth()->user()->roleKey() !== 'owner')
        @if((int) auth()->id() === (int) $order->manual_discount_requested_by)
            <form method="post" action="{{ route('admin.pesanan-mobil.discount',$order) }}">@csrf
                <input type="hidden" name="decision" value="revise">
                <label>Jenis pengajuan <select name="manual_discount_type"><option value="none">Tarik diskon (kembali ke harga dasar)</option><option value="percentage">Persentase (%)</option><option value="nominal">Nominal Rupiah</option></select></label>
                <label>Nilai baru <input type="number" name="manual_discount_value" value="0" min="0" step="0.01" required></label>
                <label>Alasan perubahan <textarea name="manual_discount_reason" maxlength="1000" required></textarea></label><button type="submit">Perbarui pengajuan</button>
            </form>
        @elseif(auth()->user()->roleKey() === 'super_admin' && $order->manual_discount_status === 'pending')
            <form method="post" action="{{ route('admin.pesanan-mobil.discount',$order) }}">@csrf
                <input type="hidden" name="decision" value="reject"><label>Alasan penolakan <textarea name="manual_discount_reason" maxlength="1000" required></textarea></label><button type="submit">Tolak pengajuan diskon</button>
            </form>
        @endif
    @endif
</section>
@endif
