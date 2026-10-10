<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kategori_sparepart', function (Blueprint $table) {
            $table->id('id_kategori');
            $table->string('nama_kategori', 80)->unique('uq_kategori_sparepart_nama');
            $table->string('deskripsi', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('supplier', function (Blueprint $table) {
            $table->id('id_supplier');
            $table->string('kode_supplier', 30)->unique('uq_supplier_kode');
            $table->string('nama_supplier', 120);
            $table->string('no_hp', 25)->nullable();
            $table->string('email', 120)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kontak_person', 120)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('nama_supplier', 'idx_supplier_nama');
        });

        Schema::create('sparepart', function (Blueprint $table) {
            $table->id('id_sparepart');
            $table->string('kode_sparepart', 30)->unique('uq_sparepart_kode');
            $table->unsignedBigInteger('id_kategori');
            $table->string('nama_sparepart', 150);
            $table->string('merk', 100)->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->string('satuan', 30)->default('pcs');
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('nama_sparepart', 'idx_sparepart_nama');
            $table->index(['stok', 'stok_minimum'], 'idx_sparepart_stok');
            $table->index('id_kategori', 'idx_sparepart_kategori');
            $table->foreign('id_kategori', 'fk_sparepart_kategori')->references('id_kategori')->on('kategori_sparepart')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sparepart');
        Schema::dropIfExists('supplier');
        Schema::dropIfExists('kategori_sparepart');
        Schema::enableForeignKeyConstraints();
    }
};
