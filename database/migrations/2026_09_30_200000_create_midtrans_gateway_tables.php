<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembayaran_gateway', function (Blueprint $table) {
            $table->id('id_pembayaran_gateway');
            $table->unsignedBigInteger('id_transaksi');
            $table->unsignedBigInteger('id_pembayaran')->nullable()->unique('uq_gateway_payment');
            $table->string('provider', 30)->default('midtrans');
            $table->string('order_id', 50)->unique('uq_gateway_order');
            $table->decimal('gross_amount', 15, 2);
            $table->string('status_provider', 40)->default('created');
            $table->string('payment_type', 50)->nullable();
            $table->string('provider_transaction_id', 100)->nullable();
            $table->string('snap_token', 255)->nullable();
            $table->string('redirect_url', 500)->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('initiated_by')->references('id_user')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->index(['id_transaksi', 'status_provider'], 'idx_gateway_transaction_status');
        });
        Schema::create('event_pembayaran_gateway', function (Blueprint $table) {
            $table->id('id_event_gateway');
            $table->unsignedBigInteger('id_pembayaran_gateway');
            $table->string('event_key', 190)->unique('uq_gateway_event_key');
            $table->string('transaction_status', 40);
            $table->string('status_code', 10);
            $table->string('fraud_status', 30)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('provider_transaction_id', 100)->nullable();
            $table->string('payload_hash', 128)->nullable();
            $table->dateTime('received_at');
            $table->timestamps();
            $table->foreign('id_pembayaran_gateway')->references('id_pembayaran_gateway')->on('pembayaran_gateway')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }
    public function down(): void { Schema::dropIfExists('event_pembayaran_gateway'); Schema::dropIfExists('pembayaran_gateway'); }
};
