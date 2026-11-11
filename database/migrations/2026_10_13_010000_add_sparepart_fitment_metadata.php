<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sparepart', function (Blueprint $table): void {
            $table->string('nomor_part_oem', 80)->nullable()->after('merk');
            $table->string('nomor_part_aftermarket', 80)->nullable()->after('nomor_part_oem');
            $table->text('deskripsi_spesifikasi')->nullable()->after('nomor_part_aftermarket');
            $table->foreignId('id_supplier')->nullable()->after('id_kategori')->constrained('supplier', 'id_supplier')->nullOnDelete()->cascadeOnUpdate();
            $table->dateTime('harga_diperbarui_at')->nullable()->after('harga_jual');
        });

        Schema::create('sparepart_fitments', function (Blueprint $table): void {
            $table->id('id_sparepart_fitment');
            $table->foreignId('id_sparepart')->constrained('sparepart', 'id_sparepart')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('merk_kendaraan', 80)->nullable();
            $table->string('model_kendaraan', 120)->nullable();
            $table->unsignedSmallInteger('tahun_mulai')->nullable();
            $table->unsignedSmallInteger('tahun_akhir')->nullable();
            $table->string('varian', 120)->nullable();
            $table->string('kode_mesin', 80)->nullable();
            $table->string('nomor_part_referensi', 100)->nullable();
            $table->enum('status_verifikasi', ['verified_fit', 'unverified', 'verified_not_fit'])->default('unverified');
            $table->string('sumber_bukti', 255)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete()->cascadeOnUpdate();
            $table->dateTime('verified_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['id_sparepart', 'status_verifikasi']);
            $table->index(['model_kendaraan', 'kode_mesin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sparepart_fitments');
        Schema::table('sparepart', function (Blueprint $table): void {
            $table->dropForeign(['id_supplier']);
            $table->dropColumn(['nomor_part_oem', 'nomor_part_aftermarket', 'deskripsi_spesifikasi', 'id_supplier', 'harga_diperbarui_at']);
        });
    }
};
