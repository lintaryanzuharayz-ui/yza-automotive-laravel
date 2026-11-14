<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_leads', function (Blueprint $table): void {
            $table->id('id_sales_lead');
            $table->foreignId('id_pelanggan')->nullable()->constrained('pelanggan', 'id_pelanggan')->nullOnDelete();
            $table->foreignId('id_mobil')->nullable()->constrained('mobil', 'id_mobil')->nullOnDelete();
            $table->foreignId('id_sales')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->string('nama_prospek', 120);
            $table->string('kontak', 120)->nullable();
            $table->string('sumber', 60)->nullable();
            $table->string('status', 30)->default('baru');
            $table->timestamp('follow_up_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['id_sales', 'status']);
            $table->index(['status', 'follow_up_at']);
        });

        Schema::create('sales_lead_activities', function (Blueprint $table): void {
            $table->id('id_sales_lead_activity');
            $table->foreignId('id_sales_lead')->constrained('sales_leads', 'id_sales_lead')->cascadeOnDelete();
            $table->foreignId('id_petugas')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->string('jenis', 40)->default('catatan');
            $table->text('catatan');
            $table->timestamp('follow_up_at')->nullable();
            $table->timestamps();
            $table->index(['id_sales_lead', 'created_at']);
        });

        Schema::create('sales_targets', function (Blueprint $table): void {
            $table->id('id_sales_target');
            $table->foreignId('id_sales')->constrained('petugas', 'id_petugas')->cascadeOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->unsignedInteger('target_unit')->default(0);
            $table->decimal('target_nominal', 15, 2)->default(0);
            $table->string('status', 20)->default('aktif');
            $table->timestamps();
            $table->unique(['id_sales', 'periode_mulai', 'periode_selesai'], 'uq_sales_target_period');
            $table->index(['periode_mulai', 'periode_selesai', 'status']);
        });

        Schema::create('sales_assignment_audits', function (Blueprint $table): void {
            $table->id('id_sales_assignment_audit');
            $table->string('entity_type', 40);
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('previous_sales_id')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->foreignId('new_sales_id')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->foreignId('actor_petugas_id')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->string('reason', 500)->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::table('penjualan_mobil', function (Blueprint $table): void {
            $table->foreignId('id_sales')->nullable()->after('id_petugas')->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->index(['id_sales', 'status'], 'idx_penjualan_mobil_sales_status');
        });

        Schema::table('pesanan_mobil_member', function (Blueprint $table): void {
            $table->foreignId('id_sales')->nullable()->after('id_penjualan')->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->index(['id_sales', 'status'], 'idx_pesanan_mobil_sales_status');
        });

        Schema::table('test_drive', function (Blueprint $table): void {
            $table->foreignId('id_sales')->nullable()->after('id_petugas')->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->string('hasil', 60)->nullable()->after('status');
            $table->text('catatan_sales')->nullable()->after('hasil');
            $table->timestamp('follow_up_at')->nullable()->after('catatan_sales');
            $table->index(['id_sales', 'status'], 'idx_test_drive_sales_status');
        });
    }

    public function down(): void
    {
        Schema::table('test_drive', function (Blueprint $table): void {
            $table->dropForeign(['id_sales']);
            $table->dropIndex('idx_test_drive_sales_status');
            $table->dropColumn(['id_sales', 'hasil', 'catatan_sales', 'follow_up_at']);
        });
        Schema::table('pesanan_mobil_member', function (Blueprint $table): void {
            $table->dropForeign(['id_sales']);
            $table->dropIndex('idx_pesanan_mobil_sales_status');
            $table->dropColumn('id_sales');
        });
        Schema::table('penjualan_mobil', function (Blueprint $table): void {
            $table->dropForeign(['id_sales']);
            $table->dropIndex('idx_penjualan_mobil_sales_status');
            $table->dropColumn('id_sales');
        });
        Schema::dropIfExists('sales_assignment_audits');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('sales_lead_activities');
        Schema::dropIfExists('sales_leads');
    }
};
