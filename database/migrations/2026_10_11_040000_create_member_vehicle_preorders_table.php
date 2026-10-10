<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pre_order_mobil_member', function (Blueprint $table) {
            $table->id('id_pre_order');
            $table->foreignId('id_pelanggan')->constrained('pelanggan', 'id_pelanggan')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_mobil')->nullable()->constrained('mobil', 'id_mobil')->nullOnDelete()->cascadeOnUpdate();
            $table->string('product_key', 120);
            $table->string('showcase_slug', 80)->nullable();
            $table->string('nama_mobil', 180);
            $table->string('varian', 120)->nullable();
            $table->string('tahun', 10)->nullable();
            $table->string('warna', 100)->nullable();
            $table->decimal('harga_referensi', 15, 2)->nullable();
            $table->enum('status', ['menunggu_stok', 'stok_tersedia', 'notifikasi_terkirim', 'dilanjutkan_ke_checkout', 'dibatalkan'])->default('menunggu_stok');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('id_pesanan')->nullable();
            $table->timestamps();
            $table->index(['product_key', 'status'], 'idx_preorder_product_status');
            $table->index(['id_pelanggan', 'status'], 'idx_preorder_customer_status');
            $table->unique(['id_pelanggan', 'product_key'], 'uq_preorder_customer_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_order_mobil_member');
    }
};
