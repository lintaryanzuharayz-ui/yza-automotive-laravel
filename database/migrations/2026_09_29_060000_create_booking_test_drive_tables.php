<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('booking_mobil', function(Blueprint $t){$t->id('id_booking');$t->string('kode_booking',40)->unique('uq_booking_mobil_kode');$t->foreignId('id_pelanggan')->constrained('pelanggan','id_pelanggan')->cascadeOnUpdate();$t->foreignId('id_mobil')->constrained('mobil','id_mobil')->cascadeOnUpdate();$t->dateTime('tanggal_booking')->useCurrent();$t->dateTime('tanggal_expired')->nullable();$t->text('catatan')->nullable();$t->enum('status',['menunggu','dikonfirmasi','dibatalkan','selesai','kadaluarsa'])->default('menunggu');$t->foreignId('id_petugas_konfirmasi')->nullable()->constrained('petugas','id_petugas')->nullOnDelete()->cascadeOnUpdate();$t->timestamps();$t->index(['id_mobil','status'],'idx_booking_mobil_status');$t->index(['id_pelanggan','status'],'idx_booking_pelanggan');});
  Schema::create('test_drive', function(Blueprint $t){$t->id('id_test_drive');$t->string('kode_test_drive',40)->unique('uq_test_drive_kode');$t->foreignId('id_pelanggan')->constrained('pelanggan','id_pelanggan')->cascadeOnUpdate();$t->foreignId('id_mobil')->constrained('mobil','id_mobil')->cascadeOnUpdate();$t->foreignId('id_petugas')->nullable()->constrained('petugas','id_petugas')->nullOnDelete()->cascadeOnUpdate();$t->date('tanggal');$t->time('jam');$t->string('lokasi',255)->nullable();$t->text('catatan')->nullable();$t->enum('status',['menunggu','disetujui','ditolak','selesai','dibatalkan'])->default('menunggu');$t->timestamps();$t->index(['tanggal','jam','status'],'idx_test_drive_jadwal');$t->index(['id_mobil','status'],'idx_test_drive_mobil');});
 }
 public function down(): void {Schema::dropIfExists('test_drive');Schema::dropIfExists('booking_mobil');}
};
