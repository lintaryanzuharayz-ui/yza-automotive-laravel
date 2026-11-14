<section class="sale-promo" data-sale-promo data-url="{{ route($salesRoutePrefix.'.penjualan-mobil.promo-preview') }}">
    <h3>Promo YZA Member</h3><p>Pilih pelanggan dan unit terlebih dahulu. Kelayakan dan kuota diperiksa kembali saat penjualan disimpan.</p>
    <div class="sale-promo-options">
    @forelse($promos as $promo)
        <label class="sale-promo-option"><input type="radio" name="promo_id" value="{{ $promo->id_promo }}" @checked(old('promo_id') == $promo->id_promo)><span><strong>{{ $promo->nama }}</strong>
            <small>{{ $promo->jenis_benefit === 'persentase' ? rtrim(rtrim(number_format((float)$promo->nilai_benefit, 2, ',', '.'), '0'), ',').'%' : 'Rp '.number_format((float)$promo->nilai_benefit, 0, ',', '.') }} · Minimum Rp {{ number_format((float)$promo->minimum_transaksi, 0, ',', '.') }}
                @if($promo->member_only)
                    · Member aktif
                @endif
            </small><small>Berlaku sampai {{ $promo->selesai_at?->format('d M Y') }}
                @if($promo->maksimum_benefit)
                    · Maksimum Rp {{ number_format((float)$promo->maksimum_benefit, 0, ',', '.') }}
                @endif
            </small></span></label>
    @empty<p>Belum ada promo tersedia.</p>@endforelse
    </div>
    <div class="sale-promo-status" aria-live="polite">Belum ada promo dipilih.</div>
    <div class="sale-promo-summary" hidden><span>Subtotal <b data-sale-promo-subtotal></b></span><span>Diskon promo <b data-sale-promo-discount></b></span><span>Total estimasi <b data-sale-promo-total></b></span></div>
    <button type="button" data-sale-promo-remove hidden>Hapus Promo</button>
</section>
