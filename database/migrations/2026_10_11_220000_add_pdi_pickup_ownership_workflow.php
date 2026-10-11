<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pdi_pesanan_mobil', function (Blueprint $table): void {
            $table->timestamp('started_at')->nullable()->after('id_petugas');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->string('result', 30)->nullable()->after('status');
            $table->json('checklist')->nullable()->after('result');
            $table->string('nomor_rangka_snapshot', 100)->nullable()->after('checklist');
            $table->string('nomor_mesin_snapshot', 100)->nullable()->after('nomor_rangka_snapshot');
            $table->index(['status', 'completed_at'], 'idx_pdi_status_completed');
        });

        Schema::table('pesanan_mobil_member', function (Blueprint $table): void {
            $table->string('lokasi_pengambilan', 180)->nullable()->after('jadwal_pengambilan');
            $table->string('status_pengambilan', 30)->default('belum_dijadwalkan')->after('lokasi_pengambilan');
            $table->timestamp('jadwal_dikonfirmasi_at')->nullable()->after('status_pengambilan');
            $table->text('catatan_pengambilan')->nullable()->after('jadwal_dikonfirmasi_at');
            $table->index(['status_pengambilan', 'jadwal_pengambilan'], 'idx_member_pickup_status');
        });

        Schema::table('kendaraan_pelanggan', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_penjualan')->nullable()->after('id_pelanggan');
            $table->unsignedBigInteger('id_pesanan')->nullable()->after('id_penjualan');
            $table->unsignedBigInteger('id_serah_terima')->nullable()->after('id_pesanan');
            $table->foreign('id_penjualan', 'fk_kendaraan_source_penjualan')->references('id_penjualan')->on('penjualan_mobil')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('id_pesanan', 'fk_kendaraan_source_pesanan')->references('id_pesanan')->on('pesanan_mobil_member')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('id_serah_terima', 'fk_kendaraan_source_serah_terima')->references('id_serah_terima')->on('serah_terima_pesanan_mobil')->nullOnDelete()->cascadeOnUpdate();
            $table->unique('id_penjualan', 'uq_kendaraan_source_penjualan');
            $table->index('id_pesanan', 'idx_kendaraan_source_pesanan');
        });
    }

    public function down(): void
    {
        Schema::table('kendaraan_pelanggan', function (Blueprint $table): void {
            $table->dropUnique('uq_kendaraan_source_penjualan');
            $table->dropForeign('fk_kendaraan_source_penjualan');
            $table->dropForeign('fk_kendaraan_source_pesanan');
            $table->dropForeign('fk_kendaraan_source_serah_terima');
            $table->dropIndex('idx_kendaraan_source_pesanan');
            $table->dropColumn(['id_penjualan', 'id_pesanan', 'id_serah_terima']);
        });
        Schema::table('pesanan_mobil_member', function (Blueprint $table): void {
            $table->dropIndex('idx_member_pickup_status');
            $table->dropColumn(['lokasi_pengambilan', 'status_pengambilan', 'jadwal_dikonfirmasi_at', 'catatan_pengambilan']);
        });
        Schema::table('pdi_pesanan_mobil', function (Blueprint $table): void {
            $table->dropIndex('idx_pdi_status_completed');
            $table->dropColumn(['started_at', 'completed_at', 'result', 'checklist', 'nomor_rangka_snapshot', 'nomor_mesin_snapshot']);
        });
    }
};
