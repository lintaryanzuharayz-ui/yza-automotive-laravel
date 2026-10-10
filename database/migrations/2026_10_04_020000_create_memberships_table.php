<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table): void {
            $table->id('id_membership');
            $table->unsignedBigInteger('id_pelanggan')->unique('uq_membership_customer');
            $table->string('member_id', 30)->unique('uq_membership_member_id');
            $table->enum('status', ['pending_payment', 'active', 'cancelled'])->default('pending_payment');
            $table->decimal('activation_fee', 15, 2);
            $table->dateTime('registered_at');
            $table->dateTime('activated_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->unsignedBigInteger('gateway_payment_id')->nullable()->unique('uq_membership_gateway');
            $table->timestamps();
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('gateway_payment_id')->references('id_pembayaran_gateway')->on('pembayaran_gateway')->nullOnDelete()->cascadeOnUpdate();
            $table->index(['status', 'registered_at'], 'idx_membership_status_date');
        });
        foreach (\Illuminate\Support\Facades\DB::table('pelanggan')->pluck('id_pelanggan') as $customerId) {
            \Illuminate\Support\Facades\DB::table('memberships')->insertOrIgnore([
                'id_pelanggan' => $customerId,
                'member_id' => 'YZA-MBR-'.str_pad((string) $customerId, 6, '0', STR_PAD_LEFT),
                'status' => 'active', 'activation_fee' => 249000, 'registered_at' => now(),
                'activated_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void { Schema::dropIfExists('memberships'); }
};
