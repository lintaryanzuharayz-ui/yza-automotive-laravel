<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pesanan_mobil_member', function (Blueprint $table): void {
            $table->id('id_pesanan');
            $table->string('nomor_pesanan', 40)->unique();
            $table->unsignedBigInteger('id_pelanggan');
            $table->unsignedBigInteger('id_mobil');
            $table->unsignedBigInteger('id_transaksi')->nullable()->unique();
            $table->unsignedBigInteger('id_penjualan')->nullable()->unique();
            $table->string('nama_pembeli', 120);
            $table->string('email_pembeli', 120)->nullable();
            $table->string('telepon_pembeli', 25)->nullable();
            $table->text('alamat_pembeli')->nullable();
            $table->string('snapshot_merk', 80);
            $table->string('snapshot_model', 100);
            $table->string('snapshot_tipe', 100)->nullable();
            $table->unsignedSmallInteger('snapshot_tahun')->nullable();
            $table->string('snapshot_warna', 50)->nullable();
            $table->decimal('snapshot_harga', 15, 2);
            $table->decimal('total_tagihan', 15, 2);
            $table->boolean('syarat_disetujui')->default(false);
            $table->string('status', 40)->default('menunggu_verifikasi')->index();
            $table->unsignedBigInteger('id_petugas_keputusan')->nullable();
            $table->timestamp('diputuskan_at')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('jadwal_pengambilan')->nullable();
            $table->timestamp('diserahkan_at')->nullable();
            $table->timestamps();
            $table->index(['id_pelanggan', 'created_at']);
            $table->index(['id_mobil', 'status']);
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_mobil')->references('id_mobil')->on('mobil')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualan_mobil')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('id_petugas_keputusan')->references('id_petugas')->on('petugas')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('pdi_pesanan_mobil', function (Blueprint $table): void {
            $table->id('id_pdi');
            $table->unsignedBigInteger('id_pesanan')->unique();
            $table->unsignedBigInteger('id_petugas')->nullable();
            foreach (['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'] as $item) {
                $table->boolean($item)->default(false);
            }
            $table->string('status', 20)->default('belum_diperiksa');
            $table->text('catatan')->nullable();
            $table->timestamp('diperiksa_at')->nullable();
            $table->timestamps();
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan_mobil_member')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_petugas')->references('id_petugas')->on('petugas')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('serah_terima_pesanan_mobil', function (Blueprint $table): void {
            $table->id('id_serah_terima');
            $table->unsignedBigInteger('id_pesanan')->unique();
            $table->unsignedBigInteger('id_petugas')->nullable();
            $table->string('nama_penerima', 120);
            $table->string('nomor_identitas', 80)->nullable();
            $table->boolean('pelunasan_terverifikasi')->default(false);
            $table->boolean('dokumen_lengkap')->default(false);
            $table->boolean('kendaraan_sesuai')->default(false);
            $table->text('catatan')->nullable();
            $table->timestamp('diserahkan_at');
            $table->timestamps();
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan_mobil_member')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_petugas')->references('id_petugas')->on('petugas')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serah_terima_pesanan_mobil');
        Schema::dropIfExists('pdi_pesanan_mobil');
        Schema::dropIfExists('pesanan_mobil_member');
    }
};
