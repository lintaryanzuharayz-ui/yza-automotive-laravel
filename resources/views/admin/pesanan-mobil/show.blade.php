@extends('layouts.app')

@section('title', 'Detail Pesanan ' . ($order->penjualan?->kode_penjualan ?: $order->nomor_pesanan) . ' - Admin YZA Automotive')
@section('admin-page-title', 'Detail Pesanan Mobil Member')

@section('content')
@php
    $car = $order->mobil;
    $customer = $order->pelanggan;
    $fotoMobil = $car?->foto?->first()?->url();
    if (!$fotoMobil) {
        if (str_contains(strtolower($order->snapshot_model ?? ''), 'supra')) {
            $fotoMobil = asset('assets/images/vehicles/toyota-gr-supra/black/front.png');
        } else {
            $fotoMobil = asset('assets/images/hero/yza-hero-black-v2.png');
        }
    }
    $customerIdentifier = $car?->kode_mobil ?? ($customer?->kode_pelanggan ?: ($order->telepon_pembeli ?: 'ID: PEL-'.str_pad($order->id_pelanggan, 4, '0', STR_PAD_LEFT)));
    $orderIdDisplay = $order->penjualan?->kode_penjualan ?: $order->nomor_pesanan;
    $cleanStatus = strtolower((string) $order->status);
    $transaksi = $order->transaksi;
    $isPaid = ($transaksi?->status === 'lunas') || in_array($cleanStatus, ['lunas', 'pdi_lulus', 'siap_diambil', 'diserahkan']);
    $isWaitingPayment = ($cleanStatus === 'menunggu_pembayaran') || ($transaksi?->status === 'belum_bayar' && !$isPaid);
    $isPendingVerify = $transaksi?->pembayarans?->contains(fn($p) => $p->status === 'menunggu_verifikasi');
    $pdiDefinition = app(\App\Services\MemberVehiclePurchaseService::class)->checklistDefinition();
    $pdiLabels = ['identitas' => 'Identitas Unit', 'mesin_fluid' => 'Mesin & Cairan', 'kaki_kaki' => 'Kaki-kaki', 'electrical' => 'Kelistrikan', 'cabin' => 'Kabin & Interior', 'exterior' => 'Eksterior', 'feature' => 'Fitur & Keamanan', 'completeness' => 'Kelengkapan', 'final' => 'Uji Final'];
    $pdiStatus = $order->pdi?->status ?: 'belum_diperiksa';
@endphp

<div class="admin-sales-page">
    {{-- Breadcrumb & Back Action --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Dashboard</span>
            </a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('admin.pesanan-mobil.index') }}" class="breadcrumb-link">Pesanan Mobil</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Detail #{{ $orderIdDisplay }}</span>
        </nav>

        <a href="{{ route('admin.pesanan-mobil.index') }}" class="btn-filter-reset" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 9px; text-decoration: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Kembali ke Daftar Pesanan</span>
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; font-size: 14px; font-weight: 600;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; font-size: 14px; font-weight: 600;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Main Luxury Card Container --}}
    <div style="background: #FFFFFF; border-radius: 20px; border: 1px solid var(--yza-border, #E2E8F0); box-shadow: 0 10px 30px rgba(0,0,0,0.04); overflow: hidden;">
        <div style="height: 4px; background: linear-gradient(90deg, #ED172B 0%, #FF3850 50%, #991B1B 100%); width: 100%;"></div>

        {{-- Header Section --}}
        <div style="padding: 24px 30px; display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap; border-bottom: 1px solid #F1F5F9;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: inline-block; padding: 3px 9px; border-radius: 6px; background: #FEE2E2; color: #ED172B; font-size: 11px; font-weight: 800; letter-spacing: 0.08em;">YZA AUTOMOTIVE</span>
                    <span style="font-family: monospace; font-size: 13px; font-weight: 700; color: #475569; background: #F1F5F9; padding: 3px 9px; border-radius: 6px;">#{{ $orderIdDisplay }}</span>
                </div>
                <h1 style="margin: 0; font-size: 26px; font-weight: 800; color: #0F172A;">Detail Pesanan Member</h1>
                <div style="display: flex; align-items: center; gap: 6px; margin-top: 6px; color: #64748B; font-size: 13px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Dipesan pada {{ $order->created_at?->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                @if($cleanStatus === 'menunggu_verifikasi')
                    <form method="post" action="{{ route('admin.pesanan-mobil.reject', $order) }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="catatan_admin" value="Pesanan ditolak oleh admin.">
                        <button type="submit" class="btn-filter-reset" style="color: #DC2626; border-color: #FECACA; background: #FEF2F2;" onclick="return confirm('Yakin ingin menolak pesanan ini?')">
                            Tolak Pesanan
                        </button>
                    </form>
                    <form method="post" action="{{ route('admin.pesanan-mobil.approve', $order) }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="catatan_admin" value="Pesanan disetujui oleh admin.">
                        <button type="submit" class="btn-filter-apply is-red" style="background: #16A34A;" onclick="return confirm('Yakin ingin menyetujui pesanan ini?')">
                            Setujui Pesanan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Status Ribbon --}}
        <div style="padding: 16px 30px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 600; color: #64748B;">Status Pesanan:</span>
                    <span class="badge-status {{ $cleanStatus === 'menunggu_pembayaran' ? 'is-confirmed' : ($cleanStatus === 'menunggu_verifikasi' ? 'is-waiting' : (in_array($cleanStatus, ['lunas','pdi_lulus','siap_diambil']) ? 'is-processing' : ($cleanStatus === 'diserahkan' ? 'is-success' : 'is-cancelled'))) }}">
                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 600; color: #64748B;">Status Pembayaran:</span>
                    <span class="badge-status {{ $isPaid ? 'is-paid' : ($isPendingVerify ? 'is-verify' : 'is-waiting') }}">
                        {{ $isPaid ? 'Pembayaran Lunas' : ($isPendingVerify ? 'Menunggu Verifikasi' : 'Menunggu Pembayaran') }}
                    </span>
                </div>
            </div>

            <div style="text-align: right;">
                <span style="display: block; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: #94A3B8;">TOTAL TAGIHAN</span>
                <span style="font-size: 24px; font-weight: 900; color: #ED172B;">Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Two-Column Content Grid --}}
        <div style="padding: 30px; display: grid; grid-template-columns: 1.15fr 1fr; gap: 28px;">
            {{-- Kolom Kiri: Kendaraan --}}
            <div>
                <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 10px;">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: grid; place-items: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 11 2 11.4V16c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
                    </span>
                    Spesifikasi Unit Mobil
                </h3>

                <div style="border-radius: 14px; border: 1px solid #E2E8F0; overflow: hidden; background: #FFFFFF; margin-bottom: 16px;">
                    <div style="height: 220px; background: radial-gradient(circle at center, #1E293B 0%, #0F172A 100%); display: grid; place-items: center; position: relative;">
                        <img src="{{ $fotoMobil }}" alt="{{ $order->snapshot_merk }} {{ $order->snapshot_model }}" style="max-width: 85%; max-height: 85%; object-fit: contain;">
                        <span style="position: absolute; top: 12px; left: 12px; background: rgba(15,23,42,0.75); color: #FFF; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">{{ $order->snapshot_tipe ?: 'Official Unit' }}</span>
                    </div>
                    <div style="padding: 14px 18px; border-top: 1px solid #F1F5F9;">
                        <h4 style="margin: 0; font-size: 18px; font-weight: 800; color: #0F172A;">{{ $order->snapshot_merk }} {{ $order->snapshot_model }} {{ $order->snapshot_tahun ?: '' }}</h4>
                        <span style="color: #64748B; font-size: 13px;">{{ $order->snapshot_merk }} &bull; Sport / Luxury Collection</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px;">
                    <div style="padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                        <span style="display: block; font-size: 11px; color: #64748B; font-weight: 600;">Merk</span>
                        <strong style="font-size: 13px; color: #0F172A;">{{ $order->snapshot_merk }}</strong>
                    </div>
                    <div style="padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                        <span style="display: block; font-size: 11px; color: #64748B; font-weight: 600;">Model</span>
                        <strong style="font-size: 13px; color: #0F172A;">{{ $order->snapshot_model }}</strong>
                    </div>
                    <div style="padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                        <span style="display: block; font-size: 11px; color: #64748B; font-weight: 600;">Tahun Pembuatan</span>
                        <strong style="font-size: 13px; color: #0F172A;">{{ $order->snapshot_tahun ?: '-' }}</strong>
                    </div>
                    <div style="padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                        <span style="display: block; font-size: 11px; color: #64748B; font-weight: 600;">Warna Pilihan</span>
                        <strong style="font-size: 13px; color: #0F172A;">{{ $order->snapshot_warna ?: 'Sesuai Standar' }}</strong>
                    </div>
                    <div style="padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                        <span style="display: block; font-size: 11px; color: #64748B; font-weight: 600;">Tipe Kendaraan</span>
                        <strong style="font-size: 13px; color: #0F172A;">{{ $order->snapshot_tipe ?: 'New Car' }}</strong>
                    </div>
                    <div style="padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                        <span style="display: block; font-size: 11px; color: #64748B; font-weight: 600;">Kode Mobil</span>
                        <strong style="font-size: 13px; color: #0F172A;">{{ $car?->kode_mobil ?: $customerIdentifier }}</strong>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Pelanggan & Operasional PDI --}}
            <div>
                <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 10px;">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: grid; place-items: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    Data Pembeli & Transaksi
                </h3>

                <div style="padding: 16px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #EFF6FF; color: #2563EB; display: grid; place-items: center; font-weight: 800;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 15px; color: #0F172A;">{{ $customer?->nama_lengkap ?: $order->nama_pembeli }}</strong>
                            <span style="font-size: 12px; color: #64748B;">{{ $customerIdentifier }}</span>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <div><span style="color: #64748B;">WhatsApp:</span> <strong>{{ $order->telepon_pembeli ?: ($customer?->no_hp ?: '-') }}</strong></div>
                        <div><span style="color: #64748B;">Email:</span> <strong>{{ $order->email_pembeli ?: ($customer?->user?->email ?: '-') }}</strong></div>
                        <div><span style="color: #64748B;">Alamat:</span> <strong>{{ $order->alamat_pembeli ?: ($customer?->alamat ?: '-') }}</strong></div>
                    </div>
                </div>

                @if($order->transaksi && $order->transaksi->status === 'lunas')
                    <div style="padding: 18px; background: linear-gradient(145deg,#F8FAFC,#FFFFFF); border: 1px solid #E2E8F0; border-radius: 16px; margin-bottom: 16px; box-shadow: 0 10px 26px rgba(15,23,42,.05);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px;">
                            <div>
                                <div style="font-size:10px; letter-spacing:.16em; text-transform:uppercase; color:#EF233C; font-weight:900;">Quality Gate 01</div>
                                <h4 style="margin:4px 0 4px; font-size:17px; font-weight:900; color:#0F172A;">Pre-Delivery Inspection (PDI)</h4>
                                <p style="margin:0; font-size:12px; color:#64748B;">Pastikan seluruh unit siap sebelum jadwal pengambilan.</p>
                            </div>
                            <span style="padding:7px 10px; border-radius:999px; background:{{ $pdiStatus === 'lulus' ? '#DCFCE7' : ($pdiStatus === 'perlu_perbaikan' ? '#FEF3C7' : '#E2E8F0') }}; color:{{ $pdiStatus === 'lulus' ? '#15803D' : ($pdiStatus === 'perlu_perbaikan' ? '#B45309' : '#475569') }}; font-size:10px; font-weight:900; white-space:nowrap;">{{ strtoupper(str_replace('_',' ', $pdiStatus)) }}</span>
                        </div>

                        @if(!$order->pdi || $pdiStatus === 'belum_diperiksa')
                            <form method="post" action="{{ route('admin.pesanan-mobil.pdi.start', $order) }}">
                                @csrf
                                <button type="submit" class="btn-filter-apply is-red" style="width:100%; font-size:13px; padding:11px 16px;">Mulai Pemeriksaan PDI <span style="margin-left:6px;">→</span></button>
                            </form>
                        @elseif(in_array($pdiStatus, ['sedang_diperiksa','perlu_perbaikan','gagal'], true))
                            <form method="post" action="{{ route('admin.pesanan-mobil.pdi', $order) }}">
                                @csrf
                                <div style="display:grid; gap:10px;">
                                    @foreach($pdiDefinition as $section => $items)
                                        <div style="padding:12px; background:#FFFFFF; border:1px solid #E2E8F0; border-radius:12px;">
                                            <div style="font-size:11px; font-weight:900; color:#334155; margin-bottom:8px;">{{ $pdiLabels[$section] ?? ucwords(str_replace('_',' ', $section)) }}</div>
                                            <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px;">
                                                @foreach($items as $item)
                                                    @php $currentResult = data_get($order->pdi?->checklist, $section.'.'.$item.'.result', $order->pdi?->$item ? 'pass' : 'issue'); @endphp
                                                    <div style="padding:9px; border:1px solid #F1F5F9; border-radius:9px; background:#F8FAFC;">
                                                        <div style="font-size:11px; font-weight:700; color:#0F172A; margin-bottom:5px;">{{ ucwords(str_replace('_',' ', $item)) }}</div>
                                                        <select name="checklist[{{ $section }}][{{ $item }}][result]" style="width:100%; padding:7px; border:1px solid #CBD5E1; border-radius:7px; font-size:11px; background:#fff;">
                                                            <option value="pass" @selected($currentResult === 'pass')>PASS</option>
                                                            <option value="issue" @selected($currentResult === 'issue')>ISSUE</option>
                                                            <option value="na" @selected($currentResult === 'na')>N/A</option>
                                                        </select>
                                                        <input name="checklist[{{ $section }}][{{ $item }}][notes]" value="{{ data_get($order->pdi?->checklist, $section.'.'.$item.'.notes') }}" placeholder="Catatan jika ISSUE" style="width:100%; margin-top:5px; padding:7px; border:1px solid #E2E8F0; border-radius:7px; font-size:10px; box-sizing:border-box;">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <textarea style="width:100%; padding:10px; border:1px solid #CBD5E1; border-radius:9px; font-size:12px; margin:12px 0 10px; box-sizing:border-box;" name="catatan" placeholder="Catatan umum pemeriksaan..." rows="2">{{ $order->pdi?->catatan }}</textarea>
                                <button type="submit" class="btn-filter-apply is-red" style="width:100%; font-size:13px; padding:11px 16px;">Simpan Hasil PDI <span style="margin-left:6px;">→</span></button>
                            </form>
                        @else
                            <div style="padding:12px 14px; background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; color:#166534; font-size:12px;">PDI lulus pada {{ $order->pdi?->completed_at?->format('d M Y, H:i') ?: $order->pdi?->diperiksa_at?->format('d M Y, H:i') }}. Unit siap dijadwalkan untuk pengambilan.</div>
                        @endif
                    </div>
                @endif

                @if(in_array($order->status, ['pdi_lulus','siap_diambil'], true))
                    <div style="padding: 18px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 800; color: #0F172A;">Jadwal Pengambilan &amp; Serah Terima</h4>
                        <form method="post" action="{{ route('admin.pesanan-mobil.schedule', $order) }}" style="margin-bottom: 14px;">
                            @csrf
                            <input class="form-control" type="datetime-local" name="jadwal_pengambilan" value="{{ $order->jadwal_pengambilan?->format('Y-m-d\TH:i') }}" required style="width: 100%; padding: 9px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-sizing: border-box;">
                            <input type="text" name="lokasi_pengambilan" value="{{ $order->lokasi_pengambilan ?: 'YZA Automotive' }}" placeholder="Lokasi pengambilan" required style="width: 100%; padding: 9px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-sizing: border-box;">
                            <textarea name="catatan_pengambilan" placeholder="Catatan untuk pelanggan" rows="2" style="width: 100%; padding: 9px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-sizing: border-box;">{{ $order->catatan_pengambilan }}</textarea>
                            <button type="submit" class="btn-filter-reset" style="font-size: 12.5px; padding: 7px 16px;">Simpan Jadwal</button>
                        </form>

                        @if($order->status === 'siap_diambil')
                            <form method="post" action="{{ route('admin.pesanan-mobil.handover', $order) }}" style="border-top: 1px dashed #CBD5E1; padding-top: 12px;">
                                @csrf
                                <input style="width: 100%; padding: 9px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-sizing: border-box;" name="nama_penerima" placeholder="Nama penerima kendaraan" required>
                                <input style="width: 100%; padding: 9px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-sizing: border-box;" name="nomor_identitas" placeholder="Nomor KTP / Identitas">
                                <textarea style="width: 100%; padding: 9px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 13px; margin-bottom: 8px; box-sizing: border-box;" name="catatan" placeholder="Catatan serah terima" rows="2"></textarea>
                                <button type="submit" class="btn-filter-apply is-red" style="font-size: 13px; padding: 9px 18px;">Finalisasi Serah Terima Unit</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
