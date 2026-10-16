@extends('layouts.app')
@section('title','Refund & Reversal · YZA Automotive')
@section('admin-page-title','Refund & Reversal Management')
@section('content')
<section class="admin-panel"><p class="admin-muted">Pembayaran asal tidak dihapus. Hanya refund offline dengan approval dan bukti dana keluar yang dapat berstatus confirmed. Refund Midtrans online belum diaktifkan.</p></section>
<section class="admin-panel">
    <div class="admin-panel-heading"><div><small>PENGAJUAN INTERNAL</small><h2>Ajukan Refund / Reversal Offline</h2></div></div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
    @if(auth()->user()->hasPermission('finance.verify'))
        <form method="post" action="{{ route('admin.financial-adjustments.store') }}" class="financial-adjustment-form" data-adjustment-form>
            @csrf
            <div class="financial-adjustment-grid">
                <label>Pembayaran eligible
                    <select name="id_pembayaran" required>
                        <option value="">Pilih pembayaran terverifikasi</option>
                        @foreach($eligiblePayments as $payment)
                            <option value="{{ $payment->id_pembayaran }}" @selected(old('id_pembayaran') == $payment->id_pembayaran)>
                                {{ $payment->kode_pembayaran }} · {{ $payment->transaksi?->kode_transaksi }} · Rp {{ number_format((float)$payment->jumlah,0,',','.') }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>Jenis adjustment
                    <select name="adjustment_type" required>
                        <option value="refund" @selected(old('adjustment_type','refund') === 'refund')>Refund offline</option>
                        <option value="reversal" @selected(old('adjustment_type') === 'reversal')>Reversal offline</option>
                    </select>
                </label>
                <label>Nominal
                    <input type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                </label>
                <label>Referensi bukti dana keluar (opsional)
                    <input type="text" name="evidence_reference" maxlength="255" value="{{ old('evidence_reference') }}">
                </label>
                <label class="financial-adjustment-wide">Alasan pengajuan
                    <textarea name="reason" maxlength="500" required>{{ old('reason') }}</textarea>
                </label>
            </div>
            <p class="admin-muted">Pembayaran pending dan pembayaran online tidak dapat dipilih. Pengajuan tetap menunggu review; pengaju tidak dapat menyetujui atau mengonfirmasi adjustment miliknya sendiri.</p>
            <button type="submit" class="admin-button admin-button-primary" data-adjustment-submit>Ajukan Adjustment</button>
        </form>
    @endif
</section>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Transaksi</th><th>Pembayaran</th><th>Jenis</th><th>Nominal</th><th>Status</th><th>Pengusul</th><th>Audit</th><th>Aksi</th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->transaction?->kode_transaksi }}</td><td>{{ $item->payment?->kode_pembayaran }}</td><td>{{ ucfirst($item->adjustment_type) }}</td><td>Rp {{ number_format((float)$item->amount,0,',','.') }}</td><td>{{ ucfirst(str_replace('_',' ',$item->status)) }}</td><td>{{ $item->requester?->nama_lengkap }}</td><td>{{ $item->events->count() }} event</td><td>@if(in_array($item->status,['requested','under_review','approved','processing'],true))<form method="post" action="{{ route('admin.financial-adjustments.transition',$item) }}" class="admin-toolbar">@csrf @method('patch')<select name="action" class="admin-filter-input">@foreach(['under_review','approved','processing','confirmed','rejected','failed'] as $action)<option value="{{ $action }}">{{ ucfirst($action) }}</option>@endforeach</select><input name="reason" class="admin-filter-input" placeholder="Alasan / bukti"><button class="admin-button admin-button-secondary">Simpan</button></form>@else—@endif</td></tr>@empty<tr><td colspan="8" class="admin-empty">Belum ada permohonan refund atau reversal.</td></tr>@endforelse</tbody></table></div>{{ $items->links() }}</section>
@endsection
@push('styles')
<style>
.financial-adjustment-form{display:grid;gap:18px;margin-top:20px}.financial-adjustment-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.financial-adjustment-form label{display:grid;gap:7px;color:#334155;font-size:12px;font-weight:750}.financial-adjustment-form input,.financial-adjustment-form select,.financial-adjustment-form textarea{width:100%;border:1px solid #d7dee8;border-radius:10px;background:#fff;color:#0f172a;padding:11px 12px;font:inherit;font-weight:500}.financial-adjustment-form textarea{min-height:90px;resize:vertical}.financial-adjustment-wide{grid-column:1/-1}.financial-adjustment-form .admin-muted{margin:0}.financial-adjustment-form button[disabled]{opacity:.65;cursor:wait}@media(max-width:700px){.financial-adjustment-grid{grid-template-columns:1fr}.financial-adjustment-wide{grid-column:auto}}
</style>
@endpush
@push('scripts')
<script>
document.querySelector('[data-adjustment-form]')?.addEventListener('submit', function () {
    const button = this.querySelector('[data-adjustment-submit]');
    if (button) { button.disabled = true; button.textContent = 'Mengirim...'; }
});
</script>
@endpush
