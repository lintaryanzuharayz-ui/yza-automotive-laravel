<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  Schema::create('penjualan_sparepart', function(Blueprint $t): void {
   $t->id('id_penjualan_sparepart'); $t->string('kode_penjualan',40)->unique('uq_penjualan_sp_kode');
   $t->unsignedBigInteger('id_transaksi'); $t->unsignedBigInteger('id_pelanggan')->nullable(); $t->unsignedBigInteger('id_petugas');
   $t->dateTime('tanggal')->useCurrent(); $t->decimal('subtotal',15,2)->default(0); $t->decimal('diskon',15,2)->default(0); $t->decimal('total',15,2)->default(0);
   $t->enum('status',['draft','proses','selesai','dibatalkan'])->default('draft'); $t->text('catatan')->nullable(); $t->timestamps();
   $t->unique('id_transaksi','uq_penjualan_sp_transaksi'); $t->index('tanggal','idx_penjualan_sp_tanggal');
   $t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->cascadeOnUpdate();
   $t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->nullOnDelete()->cascadeOnUpdate();
   $t->foreign('id_petugas')->references('id_petugas')->on('petugas')->cascadeOnUpdate();
  });
  Schema::create('detail_penjualan_sparepart', function(Blueprint $t): void {
   $t->id('id_detail'); $t->unsignedBigInteger('id_penjualan_sparepart'); $t->unsignedBigInteger('id_sparepart'); $t->unsignedInteger('jumlah');
   $t->decimal('harga_satuan',15,2); $t->decimal('subtotal',15,2);
   $t->unique(['id_penjualan_sparepart','id_sparepart'],'uq_penjualan_sp_item'); $t->index('id_sparepart','idx_detail_penjualan_sp_barang');
   $t->foreign('id_penjualan_sparepart')->references('id_penjualan_sparepart')->on('penjualan_sparepart')->cascadeOnDelete()->cascadeOnUpdate();
   $t->foreign('id_sparepart')->references('id_sparepart')->on('sparepart')->cascadeOnUpdate();
  });
 }
 public function down(): void { Schema::dropIfExists('detail_penjualan_sparepart'); Schema::dropIfExists('penjualan_sparepart'); }
};
