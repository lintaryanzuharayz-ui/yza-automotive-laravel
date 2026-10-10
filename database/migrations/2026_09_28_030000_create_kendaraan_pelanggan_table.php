<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kendaraan_pelanggan', function (Blueprint $table) {
            $table->id('id_kendaraan_pelanggan');
            $table->unsignedBigInteger('id_pelanggan');
            $table->string('merk', 80);
            $table->string('model', 100);
            $table->string('tipe', 100)->nullable();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('warna', 50)->nullable();
            $table->enum('transmisi', ['manual', 'automatic', 'cvt', 'dct', 'lainnya'])->nullable();
            $table->enum('bahan_bakar', ['bensin', 'diesel', 'hybrid', 'listrik', 'lainnya'])->nullable();
            $table->string('nomor_polisi', 20)->nullable();
            $table->string('nomor_rangka', 100)->nullable();
            $table->string('nomor_mesin', 100)->nullable();
            $table->unsignedInteger('kilometer')->default(0);
            $table->enum('status', ['aktif', 'dijual', 'ditukar', 'nonaktif'])->default('aktif');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('id_pelanggan', 'idx_kendaraan_pelanggan');
            $table->index('nomor_polisi', 'idx_kendaraan_nomor_polisi');
            $table->foreign('id_pelanggan', 'fk_kendaraan_pelanggan')->references('id_pelanggan')->on('pelanggan')->cascadeOnUpdate();
        });
    }

    public function down(): void { Schema::dropIfExists('kendaraan_pelanggan'); }
};
