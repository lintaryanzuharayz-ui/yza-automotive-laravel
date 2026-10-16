@extends('layouts.app')

@section('title', 'Penyesuaian Stok Fisik · Admin YZA Automotive')
@section('admin-page-title', 'Penyesuaian Stok')

@section('content')
<div class="admin-inventory-adjustment">
    <style>
        .admin-inventory-adjustment {
            display: flex;
            flex-direction: column;
            gap: 24px;
            max-width: 920px;
            margin: 0 auto;
        }
        .adj-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .adj-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }
        .adj-breadcrumb a { color: #64748B; text-decoration: none; font-weight: 500; }
        .adj-breadcrumb span.current { color: #0F172A; font-weight: 700; }
        .adj-title h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .adj-title p {
            font-size: 13px;
            color: #64748B;
            margin: 4px 0 0 0;
        }
        .btn-premium {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-premium-secondary {
            background: #FFFFFF;
            color: #334155 !important;
            border: 1px solid #E2E8F0;
        }
        .btn-premium-secondary:hover { background: #F8FAFC; }
        .btn-premium-primary {
            background: #E11D48;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
        }
        .btn-premium-primary:hover {
            background: #BE123C;
            transform: translateY(-1px);
        }
        .form-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .form-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-section-title i { color: #E11D48; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        .form-group-full { grid-column: 1 / -1; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        .form-group label span.required { color: #E11D48; }
        .form-control-custom {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            font-size: 14px;
            color: #0F172A;
            background: #FFFFFF;
            outline: none;
            box-sizing: border-box;
        }
        .form-control-custom:focus {
            border-color: #E11D48;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
        }
        .info-box {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            color: #92400E;
            line-height: 1.5;
        }
        .info-box i {
            color: #D97706;
            font-size: 18px;
            margin-top: 2px;
        }
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid #F1F5F9;
        }
    </style>

    <div class="adj-header">
        <div>
            <div class="adj-breadcrumb">
                <a href="{{ route('admin.sparepart.index') }}">Inventori</a>
                <span>›</span>
                <span class="current">Penyesuaian Stok (Stock Opname)</span>
            </div>
            <div class="adj-title">
                <h1>Penyesuaian Stok Fisik</h1>
                <p>Koreksi resmi saldo inventori sparepart dengan audit trail terenkripsi</p>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.inventory.history') }}" class="btn-premium btn-premium-secondary">
                <i class="fas fa-history"></i> Lihat Riwayat Perubahan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 14px 18px; border-radius: 12px; font-size: 13px; font-weight: 600;">
            <i class="fas fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 14px 18px; border-radius: 12px; font-size: 13px; font-weight: 500;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> {{ $errors->first() }}
        </div>
    @endif

    <div class="form-card">
        <form method="post" action="{{ route('admin.inventory.adjust') }}">
            @csrf

            <div class="form-section-title">
                <i class="fas fa-sliders-h"></i> Parameter Penyesuaian Saldo Fisik
            </div>

            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label>Pilih Sparepart yang Dikoreksi <span class="required">*</span></label>
                    <select class="form-control-custom" name="id_sparepart" required id="select_adj_part">
                        <option value="">-- Pilih Sparepart --</option>
                        @foreach($parts as $p)
                            <option value="{{ $p->id_sparepart }}" data-current="{{ $p->stok }}" data-unit="{{ $p->satuan }}" @selected(old('id_sparepart', $selected?->id_sparepart) == $p->id_sparepart)>
                                {{ $p->kode_sparepart }} · {{ $p->nama_sparepart }} (Saldo Sistem Saat Ini: {{ $p->stok }} {{ $p->satuan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Stok Baru Aktual (Hasil Fisik Opname) <span class="required">*</span></label>
                    <input class="form-control-custom" type="number" min="0" name="stok_baru" value="{{ old('stok_baru') }}" required placeholder="Contoh: 15" id="input_stok_baru">
                </div>

                <div class="form-group">
                    <label>Alasan Penyesuaian <span class="required">*</span></label>
                    <input class="form-control-custom" name="alasan" value="{{ old('alasan') }}" required placeholder="Contoh: Stock Opname Bulanan, Barang Rusak / Kadaluarsa, Selisih Hitung">
                </div>

                <div class="form-group form-group-full">
                    <label>Catatan Lengkap & Nomor Berita Acara <span class="required">*</span></label>
                    <textarea class="form-control-custom" name="catatan" required rows="3" placeholder="Jelaskan kronologi atau verifikasi fisik oleh kepala bengkel / supervisor gudang...">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <div class="info-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Peringatan Audit Resmi:</strong> Selisih antara saldo sistem dan saldo baru akan otomatis dicatat sebagai transaksi jenis <em>"penyesuaian"</em> pada tabel Riwayat Stok dengan timestamp, user pelaksana, dan alasan resmi.
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.sparepart.index') }}" class="btn-premium btn-premium-secondary">Batal</a>
                <button type="submit" class="btn-premium btn-premium-primary">
                    <i class="fas fa-save"></i> Simpan Penyesuaian Saldo Fisik
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
