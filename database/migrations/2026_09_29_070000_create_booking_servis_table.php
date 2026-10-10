<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::create('booking_servis',function(Blueprint $t){$t->id('id_booking_servis');$t->string('kode_booking',40)->unique('uq_booking_servis_kode');$t->foreignId('id_pelanggan')->constrained('pelanggan','id_pelanggan')->cascadeOnUpdate();$t->foreignId('id_kendaraan_pelanggan')->constrained('kendaraan_pelanggan','id_kendaraan_pelanggan')->cascadeOnUpdate();$t->foreignId('id_jenis_servis')->nullable()->constrained('jenis_servis','id_jenis_servis')->nullOnDelete()->cascadeOnUpdate();$t->date('tanggal');$t->time('jam');$t->text('keluhan')->nullable();$t->text('catatan')->nullable();$t->enum('status',['menunggu','diterima','ditolak','dibatalkan','diproses','selesai'])->default('menunggu');$t->timestamps();$t->index(['tanggal','jam','status'],'idx_booking_servis_jadwal');$t->index('id_pelanggan','idx_booking_servis_pelanggan');});}
 public function down():void{Schema::dropIfExists('booking_servis');}
};
