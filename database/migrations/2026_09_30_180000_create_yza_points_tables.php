<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('poin_member', function (Blueprint $table) {
            $table->id('id_poin_member');
            $table->unsignedBigInteger('id_pelanggan')->unique('uq_poin_member_pelanggan');
            $table->unsignedBigInteger('saldo')->default(0);
            $table->unsignedBigInteger('total_diperoleh')->default(0);
            $table->timestamps();
            $table->foreign('id_pelanggan', 'fk_poin_member_pelanggan')
                ->references('id_pelanggan')->on('pelanggan')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create('riwayat_poin', function (Blueprint $table) {
            $table->id('id_riwayat_poin');
            $table->unsignedBigInteger('id_pelanggan');
            $table->unsignedBigInteger('id_transaksi')->nullable();
            $table->enum('jenis', ['perolehan', 'pembalikan']);
            $table->unsignedBigInteger('perubahan_poin');
            $table->unsignedBigInteger('saldo_setelah');
            $table->string('idempotency_key', 120)->unique('uq_riwayat_poin_idempotency');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
            $table->foreign('id_pelanggan', 'fk_riwayat_poin_pelanggan')
                ->references('id_pelanggan')->on('pelanggan')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_transaksi', 'fk_riwayat_poin_transaksi')
                ->references('id_transaksi')->on('transaksi')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->index(['id_pelanggan', 'created_at'], 'idx_riwayat_poin_member_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_poin');
        Schema::dropIfExists('poin_member');
    }
};
