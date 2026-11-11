<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_adjustments', function (Blueprint $table): void {
            $table->id('id_financial_adjustment');
            $table->foreignId('id_pembayaran')->constrained('pembayaran', 'id_pembayaran')->restrictOnDelete();
            $table->foreignId('id_transaksi')->constrained('transaksi', 'id_transaksi')->restrictOnDelete();
            $table->string('adjustment_type', 30)->default('refund');
            $table->string('channel', 30)->default('offline');
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('requested');
            $table->string('reason', 500);
            $table->string('evidence_reference', 255)->nullable();
            $table->string('provider_reference', 150)->nullable();
            $table->text('failure_reason')->nullable();
            $table->foreignId('requested_by')->constrained('petugas', 'id_petugas')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['id_pembayaran', 'status'], 'idx_adjustment_payment_status');
            $table->index(['id_transaksi', 'status'], 'idx_adjustment_transaction_status');
        });

        Schema::create('financial_adjustment_events', function (Blueprint $table): void {
            $table->id('id_financial_adjustment_event');
            $table->foreignId('id_financial_adjustment')->constrained('financial_adjustments', 'id_financial_adjustment')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('actor_petugas_id')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->string('reason', 500)->nullable();
            $table->string('evidence_reference', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['id_financial_adjustment', 'created_at'], 'idx_adjustment_event_history');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_adjustment_events');
        Schema::dropIfExists('financial_adjustments');
    }
};
