<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::create('booking_cuci',function(Blueprint $t){$t->id('id_booking_cuci');$t->string('kode_booking',40)->unique('uq_booking_cuci_kode');$t->unsignedBigInteger('id_transaksi')->nullable();$t->foreignId('id_pelanggan')->constrained('pelanggan','id_pelanggan')->cascadeOnUpdate();$t->foreignId('id_kendaraan_pelanggan')->constrained('kendaraan_pelanggan','id_kendaraan_pelanggan')->cascadeOnUpdate();$t->foreignId('id_paket')->constrained('paket_cuci','id_paket')->cascadeOnUpdate();$t->foreignId('id_petugas')->nullable()->constrained('petugas','id_petugas')->nullOnDelete()->cascadeOnUpdate();$t->date('tanggal');$t->time('jam');$t->decimal('harga',15,2);$t->enum('status',['menunggu','diterima','proses','selesai','dibatalkan'])->default('menunggu');$t->text('catatan')->nullable();$t->timestamps();$t->unique('id_transaksi','uq_booking_cuci_transaksi');$t->index(['tanggal','jam','status'],'idx_booking_cuci_jadwal');});}
 public function down():void{Schema::dropIfExists('booking_cuci');}
};
