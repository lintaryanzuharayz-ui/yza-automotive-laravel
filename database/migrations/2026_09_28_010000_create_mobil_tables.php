<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobil', function (Blueprint $table) {
            $table->id('id_mobil');
            $table->string('kode_mobil', 30)->unique('uq_mobil_kode');
            $table->string('merk', 80);
            $table->string('model', 100);
            $table->string('tipe', 100)->nullable();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('warna', 50)->nullable();
            $table->enum('transmisi', ['manual', 'automatic', 'cvt', 'dct', 'lainnya'])->nullable();
            $table->enum('bahan_bakar', ['bensin', 'diesel', 'hybrid', 'listrik', 'lainnya'])->nullable();
            $table->string('nomor_rangka', 100)->unique('uq_mobil_nomor_rangka');
            $table->string('nomor_mesin', 100)->unique('uq_mobil_nomor_mesin');
            $table->unsignedInteger('kilometer')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0)->index('idx_mobil_harga');
            $table->enum('kondisi', ['baru', 'bekas']);
            $table->enum('status', ['tersedia', 'dibooking', 'dalam_pemeriksaan', 'terjual', 'nonaktif'])->default('tersedia');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->enum('sumber_mobil', ['dealer', 'pembelian_bekas', 'trade_in', 'lainnya'])->default('dealer');
            $table->timestamps();
            $table->index(['status', 'kondisi', 'merk', 'tahun'], 'idx_mobil_filter');
        });

        Schema::create('foto_mobil', function (Blueprint $table) {
            $table->id('id_foto');
            $table->unsignedBigInteger('id_mobil');
            $table->string('nama_file', 255);
            $table->boolean('foto_utama')->default(false);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['id_mobil', 'urutan'], 'idx_foto_mobil');
            $table->foreign('id_mobil', 'fk_foto_mobil')->references('id_mobil')->on('mobil')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_mobil');
        Schema::dropIfExists('mobil');
    }
};
