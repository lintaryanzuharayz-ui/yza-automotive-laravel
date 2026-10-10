<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembayaran_gateway', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_membership')->nullable()->after('id_transaksi');
            $table->index(['id_membership', 'status_provider'], 'idx_gateway_membership_status');
        });
        Schema::table('pembayaran_gateway', function (Blueprint $table): void {
            $table->foreign('id_membership')->references('id_membership')->on('memberships')->nullOnDelete()->cascadeOnUpdate();
        });
        Schema::table('pembayaran_gateway', function (Blueprint $table): void { $table->unsignedBigInteger('id_transaksi')->nullable()->change(); });
    }

    public function down(): void
    {
        Schema::table('pembayaran_gateway', function (Blueprint $table): void { $table->dropForeign(['id_membership']); $table->dropIndex('idx_gateway_membership_status'); $table->dropColumn('id_membership'); });
        Schema::table('pembayaran_gateway', function (Blueprint $table): void { $table->unsignedBigInteger('id_transaksi')->nullable(false)->change(); });
    }
};
