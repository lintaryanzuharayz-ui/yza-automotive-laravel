@extends('layouts.app')
@section('title', 'Permintaan Persetujuan · YZA Automotive')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/pages/service-detail-premium.css') }}?v={{ filemtime(public_path('assets/css/pages/service-detail-premium.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/pages/service-detail-premium-overrides.css') }}?v={{ filemtime(public_path('assets/css/pages/service-detail-premium-overrides.css')) }}">
@php
    $vehicleName = trim(($servis->kendaraan?->merk ?: '') . ' ' . ($servis->kendaraan?->model ?: '')) ?: 'Kendaraan Anda';
    $vehiclePlate = $servis->kendaraan?->nomor_polisi ?: 'Nomor polisi belum diisi';
    $serviceType = $servis->bookingServis?->jenisServis?->nama_servis ?: 'Servis kendaraan';
    $statusLabel = \App\Services\ServiceStatusPresenter::label($servis->status);
    $statusTone = \App\Services\ServiceStatusPresenter::tone($servis->status);
    if (($executionTracking['pending_approval_count'] ?? 0) > 0) {
        $statusLabel = 'Menunggu Persetujuan Anda';
        $statusTone = 'warning';
    }
    $pendingCount = $servis->approvalRequests->where('status', 'pending')->count();
    $timeline = collect($executionTracking['stages'] ?? []);
@endphp
<div class="service-detail-page member-approval-page">
    <div class="service-detail-toolbar member-approval-page-toolbar">
        <a class="service-detail-back" href="{{ route('pelanggan.servis.show', $servis) }}"><span aria-hidden="true">←</span> Kembali ke Detail Servis</a>
        <nav class="service-detail-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('pelanggan.servis.index', ['kendaraan' => $servis->id_kendaraan_pelanggan]) }}">Servis Saya</a><span>/</span><a href="{{ route('pelanggan.servis.show', $servis) }}">Detail Servis</a><span>/</span><strong>Persetujuan Pekerjaan</strong></nav>
    </div>
    <header class="service-detail-hero member-approval-hero">
        <div class="service-detail-hero-copy"><span class="service-detail-kicker">{{ $servis->kode_servis }}</span><h1>Detail Servis</h1><div class="service-detail-vehicle-meta"><span aria-hidden="true">▣</span><strong>{{ $vehicleName }}</strong><span aria-hidden="true">▤</span><strong>{{ $vehiclePlate }}</strong><span aria-hidden="true">⚒</span><strong>{{ $serviceType }}</strong><b class="service-detail-status is-{{ $statusTone }}">{{ $statusLabel }}</b></div></div>
        @if($vehicleImage)<div class="service-detail-hero-image"><img src="{{ $vehicleImage }}" alt="{{ $vehicleName }}"></div>@endif
    </header>
    @include('pelanggan.servis._timeline')
    <section id="approval" class="member-approval-section member-approval-server" aria-live="polite">
        <div class="member-approval-heading"><span class="member-approval-icon" aria-hidden="true">✓</span><div><span class="member-approval-eyebrow">⚠ TINDAKAN ANDA DIPERLUKAN</span><h2>PERSETUJUAN PEKERJAAN TAMBAHAN</h2><p>YZA menemukan pekerjaan tambahan yang membutuhkan persetujuan Anda sebelum dikerjakan.</p></div></div>
        @if($pendingCount > 1)<div class="member-approval-count">{{ $pendingCount }} permintaan persetujuan menunggu keputusan</div>@endif
        @forelse($servis->approvalRequests as $approval)
            <article class="member-approval-card">
                <h3>{{ $approval->title }}</h3>
                <p>{{ $approval->reason }}</p>
                <div class="member-approval-items">
                    @foreach($approval->items as $item)
                        <div class="member-approval-item"><div class="member-approval-item-icon" aria-hidden="true">▣</div><div class="member-approval-item-copy"><strong>{{ $item->description }}</strong><small>{{ $item->item_type === 'labor' ? 'Jasa' : 'Sparepart' }} · Qty {{ $item->quantity }}</small></div><div class="member-approval-item-prices"><span>Harga Satuan <b>Rp {{ number_format((float) $item->unit_price_snapshot, 0, ',', '.') }}</b></span><span>Subtotal <b>Rp {{ number_format((float) $item->total_snapshot, 0, ',', '.') }}</b></span></div></div>
                    @endforeach
                </div>
                <div class="member-approval-bottom">
                    <div class="member-approval-total"><span>Estimasi Tambahan</span><strong>Rp {{ number_format((float) $approval->items->sum('total_snapshot'), 0, ',', '.') }}</strong></div>
                    @if($approval->status === 'pending')
                        <div class="member-approval-actions"><button type="button" data-member-approval-decision="reject" data-approval-id="{{ $approval->id_service_approval_request }}">TOLAK</button><button type="button" data-member-approval-decision="approve" data-approval-id="{{ $approval->id_service_approval_request }}">SETUJUI PEKERJAAN</button></div>
                    @endif
                </div>
                @if($approval->status !== 'pending')
                    <div class="member-approval-resolved-content">
                @endif
                @if($approval->status !== 'pending')
                    <div class="member-approval-result is-{{ $approval->status === 'approved' ? 'approved' : 'rejected' }}"><strong>{{ $approval->status === 'approved' ? '✓ PERSETUJUAN BERHASIL' : 'PEKERJAAN TAMBAHAN DITOLAK' }}</strong><span>{{ $approval->status === 'approved' ? 'Pekerjaan tambahan telah Anda setujui. YZA Automotive dapat melanjutkan proses servis.' : 'YZA Automotive tidak akan melakukan pekerjaan tambahan tersebut.' }}</span></div>
                    @if($approval->customer_note)<p class="member-approval-note">Catatan: {{ $approval->customer_note }}</p>@endif
                    <a class="member-approval-detail-cta" href="{{ route('pelanggan.servis.show', $servis) }}">Kembali ke Detail Servis →</a>
                    </div>
                @endif
            </article>
        @empty
            <div class="member-approval-empty"><strong>Tidak Ada Persetujuan yang Menunggu</strong><span>Semua permintaan persetujuan untuk servis ini telah diselesaikan.</span><a href="{{ route('pelanggan.servis.show', $servis) }}">Kembali ke Detail Servis</a></div>
        @endforelse
    </section>
</div>
<dialog class="member-decision-dialog" data-member-decision-dialog>
    <form method="dialog" class="member-decision-content">
        <h3 data-decision-title>Konfirmasi keputusan</h3>
        <p data-decision-copy></p>
        <textarea maxlength="1000" placeholder="Catatan opsional" data-decision-note></textarea>
        <div class="member-decision-actions"><button type="button" class="member-decision-cancel" data-decision-cancel>Batal</button><button type="submit" class="member-decision-confirm" data-decision-submit>Konfirmasi</button></div>
    </form>
</dialog>
@endsection
@push('scripts')
<script>
(() => {
    const dialog = document.querySelector('[data-member-decision-dialog]');
    const endpointBase = @json(route('pelanggan.servis.approvals', $servis));
    const detailUrl = @json(route('pelanggan.servis.show', $servis));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let decision = null;
    document.querySelectorAll('[data-member-approval-decision]').forEach((button) => button.addEventListener('click', () => {
        decision = { id: button.dataset.approvalId, action: button.dataset.memberApprovalDecision };
        dialog.classList.toggle('is-approve', decision.action === 'approve');
        dialog.classList.toggle('is-reject', decision.action === 'reject');
        dialog.querySelector('[data-decision-title]').textContent = decision.action === 'approve' ? 'Setujui pekerjaan tambahan?' : 'Tolak pekerjaan tambahan?';
        dialog.querySelector('[data-decision-copy]').textContent = decision.action === 'approve' ? 'Dengan menyetujui, Anda mengizinkan YZA Automotive melakukan pekerjaan tambahan ini sesuai estimasi yang ditampilkan.' : 'Dengan menolak, YZA Automotive tidak akan mengerjakan pekerjaan tambahan ini.';
        dialog.querySelector('[data-decision-submit]').textContent = decision.action === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak';
        dialog.querySelector('[data-decision-note]').value = '';
        dialog.showModal();
    }));
    dialog?.querySelector('[data-decision-cancel]')?.addEventListener('click', () => dialog.close());
    dialog?.querySelector('[data-decision-submit]')?.addEventListener('click', async (event) => {
        event.preventDefault();
        if (!decision) return;
        const submit = event.currentTarget;
        submit.disabled = true;
        submit.textContent = 'Memproses…';
        try {
            const response = await fetch(`${endpointBase}/${decision.id}/decision`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ decision: decision.action, note: dialog.querySelector('[data-decision-note]').value }) });
            if (!response.ok) throw new Error('decision_failed');
            const approvalsResponse = await fetch(endpointBase, { headers: { Accept: 'application/json' } });
            if (!approvalsResponse.ok) throw new Error('approval_status_failed');
            const approvalsData = await approvalsResponse.json();
            const hasPendingApproval = (approvalsData.approvals || []).some((approval) => approval.status === 'pending');
            window.location.href = hasPendingApproval ? window.location.href : detailUrl;
        } catch (error) {
            console.error('Keputusan approval gagal diproses', error);
            dialog.close();
            window.alert('Keputusan belum dapat disimpan. Silakan coba lagi.');
        } finally { submit.disabled = false; submit.textContent = decision?.action === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak'; decision = null; }
    });
})();
</script>
@endpush
