<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
return new class extends Migration {
 public function up(): void {
  Schema::create('trade_in',function(Blueprint $t):void{
   $t->id('id_trade_in');$t->string('kode_trade_in',40)->unique('uq_trade_in_kode');$t->unsignedBigInteger('id_transaksi')->nullable()->unique('uq_trade_in_transaksi');
   $t->unsignedBigInteger('id_pelanggan');$t->unsignedBigInteger('id_pengajuan');$t->unsignedBigInteger('id_inspeksi');$t->unsignedBigInteger('id_mobil_lama_masuk')->nullable();$t->unsignedBigInteger('id_mobil_dibeli');$t->unsignedBigInteger('id_petugas');
   $t->decimal('nilai_mobil_lama',15,2);$t->decimal('harga_mobil_dibeli',15,2);$t->decimal('diskon',15,2)->default(0);$t->decimal('selisih_bayar',15,2);$t->dateTime('tanggal')->useCurrent();$t->enum('status',['proses','selesai','dibatalkan'])->default('proses');$t->text('catatan')->nullable();$t->timestamps();
   $t->index('id_pelanggan','idx_trade_in_pelanggan');$t->index('tanggal','idx_trade_in_tanggal');
   $t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->nullOnDelete()->cascadeOnUpdate();$t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->cascadeOnUpdate();$t->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan_mobil')->cascadeOnUpdate();$t->foreign('id_inspeksi')->references('id_inspeksi')->on('inspeksi_mobil')->cascadeOnUpdate();$t->foreign('id_mobil_lama_masuk')->references('id_mobil')->on('mobil')->nullOnDelete()->cascadeOnUpdate();$t->foreign('id_mobil_dibeli')->references('id_mobil')->on('mobil')->cascadeOnUpdate();$t->foreign('id_petugas')->references('id_petugas')->on('petugas')->cascadeOnUpdate();
  });
  if (DB::table('transaksi')->where('id_transaksi', 7)->exists()
   && DB::table('pelanggan')->where('id_pelanggan', 1)->exists()
   && DB::table('pengajuan_mobil')->where('id_pengajuan', 2)->exists()
   && DB::table('inspeksi_mobil')->where('id_inspeksi', 2)->exists()
   && DB::table('mobil')->where('id_mobil', 7)->exists()
   && DB::table('mobil')->where('id_mobil', 8)->exists()
   && DB::table('petugas')->where('id_petugas', 2)->exists()) {
   DB::table('trade_in')->insert(['id_trade_in'=>1,'kode_trade_in'=>'TRD-202609-032CEF4C','id_transaksi'=>7,'id_pelanggan'=>1,'id_pengajuan'=>2,'id_inspeksi'=>2,'id_mobil_lama_masuk'=>8,'id_mobil_dibeli'=>7,'id_petugas'=>2,'nilai_mobil_lama'=>50000000,'harga_mobil_dibeli'=>80000000,'diskon'=>0,'selisih_bayar'=>30000000,'tanggal'=>'2026-09-23 15:31:07','status'=>'selesai','catatan'=>'QA trade','created_at'=>'2026-09-23 15:31:07','updated_at'=>'2026-09-23 15:31:07']);
  }
 }
 public function down(): void { Schema::dropIfExists('trade_in'); }
};
