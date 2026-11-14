@extends('layouts.app')

@section('title', 'Buat Draft Stok Masuk · Admin YZA Automotive')
@section('admin-page-title', 'Buat Draft Stok Masuk')

@section('content')
<div class="admin-stok-masuk-create">
    <style>
        .admin-stok-masuk-create {
            display: flex;
            flex-direction: column;
            gap: 24px;
            max-width: 900px;
            margin: 0 auto;
        }
        .create-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .create-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }
        .create-breadcrumb a { color: #64748B; text-decoration: none; font-weight: 500; }
        .create-breadcrumb span.current { color: #0F172A; font-weight: 700; }
        .create-title h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .create-title p {
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
        .btn-premium-secondary:hover {
            background: #F8FAFC;
        }
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
        .form-group-full {
            grid-column: 1 / -1;
        }
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
            transition: all 0.2s ease;
            outline: none;
            box-sizing: border-box;
        }
        .form-control-custom:focus {
            border-color: #E11D48;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
        }
        textarea.form-control-custom {
            resize: vertical;
            min-height: 100px;
        }
        .info-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
        }
        .info-box i {
            color: #3B82F6;
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

    <div class="create-header">
        <div>
            <div class="create-breadcrumb">
                <a href="{{ route('admin.sparepart.index') }}">Inventori</a>
                <span>›</span>
                <a href="{{ route('admin.stok-masuk.index') }}">Stok Masuk</a>
                <span>›</span>
                <span class="current">Buat Draft</span>
            </div>
            <div class="create-title">
                <h1>Draft Penerimaan Stok Masuk</h1>
                <p>Langkah 1: Inisialisasi draft penerimaan sebelum memasukkan rincian barang fisik</p>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.stok-masuk.index') }}" class="btn-premium btn-premium-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 14px 18px; border-radius: 12px; font-size: 13px; font-weight: 500;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> {{ $errors->first() }}
        </div>
    @endif

    <div class="form-card">
        <form method="post" action="{{ route('admin.stok-masuk.store') }}">
            @csrf

            <div class="form-section-title">
                <i class="fas fa-file-invoice"></i> Informasi Pengadaan & Penerimaan
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Supplier / Vendor</label>
                    <select class="form-control-custom" name="id_supplier">
                        <option value="">-- Tanpa Supplier / Pembelian Langsung --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id_supplier }}" @selected(old('id_supplier') == $s->id_supplier)>
                                {{ $s->nama_supplier }} ({{ $s->kontak_person ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Tanggal & Jam Penerimaan <span class="required">*</span></label>
                    <input class="form-control-custom" type="datetime-local" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="form-group form-group-full">
                    <label>Catatan Pengiriman / Referensi Surat Jalan</label>
                    <textarea class="form-control-custom" name="catatan" placeholder="Contoh: No. Surat Jalan SJ-2026-0881 dari Distributor Resmi Shell / Toyota Genuine Parts...">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Alur Stok Masuk Terverifikasi:</strong> Setelah menyimpan draft ini, Anda dapat menambahkan daftar sparepart, kuantitas masuk, dan harga beli satuan. Stok fisik baru akan bertambah secara resmi setelah tombol <em>"Terima Stok"</em> ditekan.
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.stok-masuk.index') }}" class="btn-premium btn-premium-secondary">Batal</a>
                <button type="submit" class="btn-premium btn-premium-primary">
                    <i class="fas fa-arrow-right"></i> Simpan Draft & Lanjut Input Barang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
