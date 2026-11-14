<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pre_order_mobil_member', function (Blueprint $table) {
            $table->unique(['id_pelanggan', 'product_key', 'warna'], 'uq_preorder_customer_product_color');
            $table->dropUnique('uq_preorder_customer_product');
        });
    }

    public function down(): void
    {
        if (DB::table('pre_order_mobil_member')->select('id_pelanggan', 'product_key')->groupBy('id_pelanggan', 'product_key')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Rollback diblokir: ada beberapa warna Pre-Order pada produk yang sama. Rekonsiliasi manual diperlukan.');
        }
        Schema::table('pre_order_mobil_member', function (Blueprint $table) {
            $table->unique(['id_pelanggan', 'product_key'], 'uq_preorder_customer_product');
            $table->dropUnique('uq_preorder_customer_product_color');
        });
    }
};
