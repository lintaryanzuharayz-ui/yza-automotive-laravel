<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stok_masuk', function (Blueprint $table) {
            $table->id('id_stok_masuk');
            $table->string('kode_stok_masuk', 40)->unique('uq_stok_masuk_kode');
            $table->foreignId('id_supplier')->nullable()->constrained('supplier', 'id_supplier')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_petugas')->constrained('petugas', 'id_petugas')->cascadeOnUpdate();
            $table->dateTime('tanggal')->useCurrent();
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->enum('status', ['draft', 'diterima', 'dibatalkan'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('tanggal', 'idx_stok_masuk_tanggal');
            $table->index('status', 'idx_stok_masuk_status');
        });

        Schema::create('detail_stok_masuk', function (Blueprint $table) {
            $table->id('id_detail_stok_masuk');
            $table->foreignId('id_stok_masuk')->constrained('stok_masuk', 'id_stok_masuk')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_sparepart')->constrained('sparepart', 'id_sparepart')->cascadeOnUpdate();
            $table->unsignedInteger('jumlah');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->unique(['id_stok_masuk', 'id_sparepart'], 'uq_detail_stok_item');
            $table->index('id_sparepart', 'idx_detail_stok_sparepart');
        });

        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->foreignId('id_sparepart')->constrained('sparepart', 'id_sparepart')->cascadeOnUpdate();
            $table->foreignId('id_petugas')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete()->cascadeOnUpdate();
            $table->dateTime('tanggal')->useCurrent();
            $table->enum('jenis_transaksi', ['stok_masuk', 'penjualan', 'servis', 'penyesuaian']);
            $table->integer('jumlah_perubahan');
            $table->unsignedInteger('stok_sebelum');
            $table->unsignedInteger('stok_sesudah');
            $table->string('referensi_tipe', 50)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['id_sparepart', 'tanggal'], 'idx_riwayat_stok_barang');
            $table->index(['jenis_transaksi', 'tanggal'], 'idx_riwayat_stok_jenis');
            $table->index(['referensi_tipe', 'referensi_id'], 'idx_riwayat_referensi');
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('judul', 150);
            $table->text('pesan');
            $table->enum('tipe', ['info', 'sukses', 'peringatan', 'stok', 'booking', 'transaksi', 'servis', 'sistem'])->default('info');
            $table->string('referensi_tipe', 50)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['id_user', 'is_read', 'created_at']);
            $table->index(['referensi_tipe', 'referensi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('riwayat_stok');
        Schema::dropIfExists('detail_stok_masuk');
        Schema::dropIfExists('stok_masuk');
    }
};
