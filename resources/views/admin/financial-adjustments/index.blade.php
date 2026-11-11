@extends('layouts.app')

@php
    $statusNames = ['requested' => 'Menunggu Review', 'under_review' => 'Sedang Ditinjau', 'approved' => 'Disetujui', 'processing' => 'Diproses', 'confirmed' => 'Confirmed', 'rejected' => 'Ditolak', 'failed' => 'Gagal'];
    $typeNames = ['refund' => 'Refund offline', 'reversal' => 'Reversal offline'];
@endphp

@section('title', 'Refund & Reversal · YZA Automotive')
@section('admin-page-title', 'Refund & Reversal Management')

@section('content')
<div class="admin-adjustments-page">
    <div class="adjustments-breadcrumb"><span>⌂</span><span>Pengajuan Internal</span><b>›</b><strong>Refund &amp; Reversal</strong></div>
    <header class="adjustments-page-header"><div><span class="adjustments-eyebrow">FINANCIAL CONTROL</span><h1>Refund &amp; Reversal Management</h1><p>Kelola pengajuan refund dan reversal pembayaran offline dengan proses approval dan audit yang terstruktur.</p></div></header>

    <section class="adjustments-hero">
        <div class="adjustments-hero-copy"><span>FINANCIAL CONTROL</span><h2>Refund <em>&amp; Reversal</em> Management</h2><p>Pembayaran asal tidak dihapus. Hanya refund offline dengan approval dan bukti dana keluar yang dapat berstatus confirmed.</p></div>
        <div class="adjustments-hero-points"><div><b>◈</b><span>Transaksi tetap aman<br>dengan proses approval</span></div><div><b>▣</b><span>Audit trail lengkap<br>untuk setiap pengajuan</span></div><div><b>⚙</b><span>Refund Midtrans online<br>belum diaktifkan</span></div></div>
    </section>

    <section class="adjustments-stats" aria-label="Ringkasan refund">
        @php $statCards = [
            ['label' => 'Pengajuan Refund', 'value' => $adjustmentStats['total'], 'caption' => 'Total pengajuan offline', 'tone' => 'red', 'icon' => '◧'],
            ['label' => 'Menunggu Review', 'value' => $adjustmentStats['review'], 'caption' => 'Dalam proses approval', 'tone' => 'amber', 'icon' => '◷'],
            ['label' => 'Disetujui', 'value' => $adjustmentStats['approved'], 'caption' => 'Telah dikonfirmasi', 'tone' => 'green', 'icon' => '✓'],
            ['label' => 'Ditolak', 'value' => $adjustmentStats['rejected'], 'caption' => 'Permohonan ditolak', 'tone' => 'rose', 'icon' => '×'],
        ]; @endphp
        @foreach($statCards as $card)<article class="adjustment-stat-card tone-{{ $card['tone'] }}"><span class="adjustment-stat-icon">{{ $card['icon'] }}</span><div><small>{{ $card['label'] }}</small><strong>{{ number_format($card['value'], 0, ',', '.') }}</strong><p>{{ $card['caption'] }}</p></div><b class="adjustment-stat-arrow">›</b></article>@endforeach
    </section>

    <div class="adjustments-main-grid">
        <section class="adjustment-form-panel">
            <div class="adjustment-panel-heading"><span class="adjustment-panel-icon">▣</span><div><h2>Ajukan Refund / Reversal Offline</h2><p>Buat pengajuan refund atau reversal untuk pembayaran yang eligible.</p></div></div>
            @if(session('status'))<div class="adjustment-alert is-success">✓ <span>{{ session('status') }}</span></div>@endif
            @if($errors->any())<div class="adjustment-alert is-error">! <span>{{ $errors->first() }}</span></div>@endif
            @if(auth()->user()->hasPermission('finance.verify'))
            <form method="post" action="{{ route('admin.financial-adjustments.store') }}" class="adjustment-form" data-adjustment-form>@csrf
                <div class="adjustment-form-grid">
                    <label><span>Pembayaran eligible</span><div class="adjustment-input-wrap"><i>▤</i><select name="id_pembayaran" required><option value="">Pilih pembayaran terverifikasi</option>@foreach($eligiblePayments as $payment)<option value="{{ $payment->id_pembayaran }}" @selected(old('id_pembayaran') == $payment->id_pembayaran)>{{ $payment->kode_pembayaran }} · {{ $payment->transaksi?->kode_transaksi }} · Rp {{ number_format((float)$payment->jumlah,0,',','.') }}</option>@endforeach</select></div></label>
                    <label><span>Jenis adjustment</span><div class="adjustment-input-wrap"><i>⇄</i><select name="adjustment_type" required><option value="refund" @selected(old('adjustment_type','refund') === 'refund')>Refund offline</option><option value="reversal" @selected(old('adjustment_type') === 'reversal')>Reversal offline</option></select></div></label>
                    <label><span>Nominal</span><div class="adjustment-input-wrap"><i>Rp</i><input type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount') }}" placeholder="Masukkan nominal" required></div></label>
                    <label><span>Referensi bukti dana keluar <small>(opsional)</small></span><div class="adjustment-input-wrap"><i>⌕</i><input type="text" name="evidence_reference" maxlength="255" value="{{ old('evidence_reference') }}" placeholder="Nomor referensi / keterangan"></div></label>
                    <label class="adjustment-form-wide"><span>Alasan pengajuan</span><div class="adjustment-textarea-wrap"><i>▤</i><textarea name="reason" maxlength="500" required placeholder="Jelaskan alasan pengajuan refund atau reversal...">{{ old('reason') }}</textarea><small>0/500</small></div></label>
                </div>
                <div class="adjustment-form-footer"><p><b>i</b>Pembayaran pending dan pembayaran online tidak dapat dipilih. Pengajuan tetap menunggu review; pengaju tidak dapat menyetujui atau mengonfirmasi adjustment miliknya sendiri.</p><button type="submit" data-adjustment-submit>➤ &nbsp; Ajukan Adjustment</button></div>
            </form>
            @else
                <div class="adjustment-permission-note">Anda tidak memiliki izin untuk mengajukan refund atau reversal.</div>
            @endif
        </section>

        <aside class="adjustment-side-panels">
            <section class="adjustment-info-panel"><div class="adjustment-info-heading"><span>▤</span><h2>Ringkasan Aturan Refund</h2></div><ul><li>Pembayaran asli tidak dihapus dari sistem.</li><li>Hanya refund offline dengan approval dan bukti dana keluar yang dapat berstatus confirmed.</li><li>Pembayaran pending dan pembayaran online tidak dapat dipilih.</li><li>Pengajuan menunggu review oleh pihak berwenang.</li><li>Pengaju tidak dapat menyetujui pengajuan miliknya sendiri.</li></ul></section>
            <section class="adjustment-info-panel"><div class="adjustment-info-heading"><span>i</span><h2>Informasi Penting</h2></div><div class="adjustment-warning"><b>⚠ &nbsp; Refund Midtrans Online Belum Diaktifkan</b><p>Pengajuan refund untuk pembayaran Midtrans (online) belum tersedia. Saat ini hanya refund offline yang dapat diproses melalui menu ini.</p></div></section>
        </aside>
    </div>

    <section class="adjustment-history-panel">
        <div class="adjustment-history-heading"><div class="adjustment-panel-heading"><span class="adjustment-panel-icon">▤</span><div><h2>Daftar Permohonan Refund &amp; Reversal</h2><p>Riwayat pengajuan refund dan reversal beserta status approval dan audit.</p></div></div><form method="get" class="adjustment-history-filters"><label>⌕<input name="q" value="{{ $q }}" placeholder="Cari transaksi, kode, atau pengusul..."></label><select name="status"><option value="">Semua Status</option>@foreach($statuses as $item)<option value="{{ $item }}" @selected($status === $item)>{{ $statusNames[$item] ?? ucfirst(str_replace('_',' ',$item)) }}</option>@endforeach</select><select name="type"><option value="">Semua Jenis</option><option value="refund" @selected($type === 'refund')>Refund offline</option><option value="reversal" @selected($type === 'reversal')>Reversal offline</option></select><button type="submit">Filter</button></form></div>
        <div class="adjustment-table-wrap"><table class="adjustment-history-table"><thead><tr><th>Transaksi / Kode</th><th>Jenis</th><th>Nominal</th><th>Status</th><th>Pengusul</th><th>Audit</th><th>Tanggal Pengajuan</th><th>Aksi</th></tr></thead><tbody>
        @forelse($items as $item)
            @php $statusTone = in_array($item->status, ['approved','confirmed'], true) ? 'success' : (in_array($item->status, ['requested','under_review','processing'], true) ? 'warning' : 'danger'); $lastEvent = $item->events->first(); @endphp
            <tr><td><strong>{{ $item->transaction?->kode_transaksi ?: '—' }}</strong><small>{{ $item->payment?->kode_pembayaran ?: 'Kode pembayaran tidak tersedia' }}</small></td><td><span class="adjustment-type-pill">{{ $typeNames[$item->adjustment_type] ?? ucfirst($item->adjustment_type) }}</span></td><td><strong>Rp {{ number_format((float)$item->amount,0,',','.') }}</strong></td><td><span class="adjustment-status status-{{ $statusTone }}"><i></i>{{ $statusNames[$item->status] ?? ucfirst(str_replace('_',' ',$item->status)) }}</span></td><td><strong>{{ $item->requester?->nama_lengkap ?: '—' }}</strong><small>{{ $item->requester?->jabatan ?: 'Petugas Finance' }}</small></td><td><small>{{ $item->events->count() }} event</small><small>{{ $lastEvent?->to_status ? ($statusNames[$lastEvent->to_status] ?? ucfirst(str_replace('_',' ',$lastEvent->to_status))) : 'Audit tercatat' }}</small></td><td><strong>{{ $item->created_at?->format('d M Y') }}</strong><small>{{ $item->created_at?->format('H:i') }} WIB</small></td><td>@if(in_array($item->status,['requested','under_review','approved','processing'],true))<form method="post" action="{{ route('admin.financial-adjustments.transition',$item) }}" class="adjustment-action-form">@csrf @method('patch')<select name="action" aria-label="Status baru">@foreach(['under_review','approved','processing','confirmed','rejected','failed'] as $action)<option value="{{ $action }}">{{ $statusNames[$action] ?? ucfirst($action) }}</option>@endforeach</select><input name="reason" placeholder="Alasan / bukti"><button type="submit">Simpan</button></form>@else<span class="adjustment-more">⋮</span>@endif</td></tr>
        @empty
            <tr><td colspan="8" class="adjustment-empty"><span>▤</span><strong>Belum ada permohonan refund atau reversal.</strong><small>Pengajuan yang masuk akan tampil di sini beserta status approval dan audit trail.</small></td></tr>
        @endforelse
        </tbody></table></div><div class="adjustment-pagination">Menampilkan {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} pengajuan <span>{{ $items->onEachSide(1)->links('pagination::bootstrap-4') }}</span></div>
    </section>
</div>
@endsection

@push('styles')
<style>
.admin-content:has(> .admin-adjustments-page)> .admin-page-heading{display:none}
</style>
@endpush
@push('scripts')
<script>document.querySelector('[data-adjustment-form]')?.addEventListener('submit',function(){const b=this.querySelector('[data-adjustment-submit]');if(b){b.disabled=true;b.textContent='Mengirim...';}});</script>
@endpush
