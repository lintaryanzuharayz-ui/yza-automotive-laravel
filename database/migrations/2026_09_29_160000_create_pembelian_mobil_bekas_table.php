<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembelian_mobil_bekas', function (Blueprint $t): void {
            $t->id('id_pembelian');
            $t->string('kode_pembelian', 40)->unique('uq_pembelian_bekas_kode');
            $t->unsignedBigInteger('id_transaksi')->nullable()->unique('uq_pembelian_bekas_transaksi');
            $t->unsignedBigInteger('id_pengajuan');
            $t->unsignedBigInteger('id_inspeksi');
            $t->unsignedBigInteger('id_pelanggan');
            $t->unsignedBigInteger('id_petugas');
            $t->unsignedBigInteger('id_mobil')->nullable();
            $t->dateTime('tanggal_pembelian')->useCurrent();
            $t->decimal('harga_pembelian', 15, 2);
            $t->enum('status', ['proses', 'selesai', 'dibatalkan'])->default('proses');
            $t->text('catatan')->nullable();
            $t->timestamps();
            $t->index(['id_pengajuan', 'status'], 'idx_pembelian_bekas_pengajuan_status');
            $t->index('tanggal_pembelian', 'idx_pembelian_bekas_tanggal');
            $t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->nullOnDelete()->cascadeOnUpdate();
            $t->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan_mobil')->cascadeOnUpdate();
            $t->foreign('id_inspeksi')->references('id_inspeksi')->on('inspeksi_mobil')->cascadeOnUpdate();
            $t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->cascadeOnUpdate();
            $t->foreign('id_petugas')->references('id_petugas')->on('petugas')->cascadeOnUpdate();
            $t->foreign('id_mobil')->references('id_mobil')->on('mobil')->nullOnDelete()->cascadeOnUpdate();
        });
        DB::table('pembelian_mobil_bekas')->insert([
            'id_pembelian'=>1,'kode_pembelian'=>'BUY-202609-487E3F23','id_transaksi'=>6,
            'id_pengajuan'=>1,'id_inspeksi'=>1,'id_pelanggan'=>1,'id_petugas'=>2,'id_mobil'=>6,
            'tanggal_pembelian'=>'2026-09-23 15:29:46','harga_pembelian'=>100000000,
            'status'=>'selesai','catatan'=>'QA purchase','created_at'=>'2026-09-23 15:29:46','updated_at'=>'2026-09-23 15:29:46'
        ]);
    }
    public function down(): void { Schema::dropIfExists('pembelian_mobil_bekas'); }
};
