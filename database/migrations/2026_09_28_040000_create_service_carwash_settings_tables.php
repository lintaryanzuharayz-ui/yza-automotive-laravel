<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('jenis_servis', function(Blueprint $t){$t->id('id_jenis_servis');$t->string('nama_servis',120)->unique('uq_jenis_servis_nama');$t->text('deskripsi')->nullable();$t->decimal('estimasi_harga',15,2)->default(0);$t->enum('status',['aktif','nonaktif'])->default('aktif');$t->timestamp('created_at')->useCurrent();$t->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();});
  Schema::create('paket_cuci', function(Blueprint $t){$t->id('id_paket');$t->string('nama_paket',100)->unique('uq_paket_cuci_nama');$t->text('deskripsi')->nullable();$t->decimal('harga',15,2)->default(0);$t->unsignedSmallInteger('estimasi_menit')->nullable();$t->enum('status',['aktif','nonaktif'])->default('aktif');$t->timestamp('created_at')->useCurrent();$t->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();});
  Schema::create('pengaturan', function(Blueprint $t){$t->id('id_pengaturan');$t->string('kunci_pengaturan',100)->unique('uq_pengaturan_kunci');$t->text('nilai')->nullable();$t->string('deskripsi',255)->nullable();$t->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();});
 }
 public function down(): void { Schema::dropIfExists('pengaturan');Schema::dropIfExists('paket_cuci');Schema::dropIfExists('jenis_servis'); }
};
