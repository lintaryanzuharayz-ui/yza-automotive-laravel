<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo_member', function (Blueprint $table) {
            $table->id('id_promo');
            $table->string('kode_promo', 50)->unique('uq_promo_member_kode');
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_benefit', ['persentase', 'nominal', 'informasi']);
            $table->decimal('nilai_benefit', 15, 2)->nullable();
            $table->decimal('minimum_transaksi', 15, 2)->default(0);
            $table->decimal('maksimum_benefit', 15, 2)->nullable();
            $table->enum('berlaku_untuk', ['semua', 'servis', 'cuci_mobil', 'penjualan_sparepart', 'penjualan_mobil'])->default('semua');
            $table->dateTime('mulai_at');
            $table->dateTime('selesai_at');
            $table->text('syarat_ketentuan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by', 'fk_promo_member_creator')->references('id_user')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->index(['status', 'mulai_at', 'selesai_at'], 'idx_promo_member_visibility');
        });
    }

    public function down(): void { Schema::dropIfExists('promo_member'); }
};
