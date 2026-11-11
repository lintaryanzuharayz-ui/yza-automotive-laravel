<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->unsignedBigInteger('verified_by')->nullable()->after('status');
            $table->dateTime('verified_at')->nullable()->after('verified_by');
            $table->string('receipt_reference', 100)->nullable()->after('verified_at');
            $table->text('rejection_reason')->nullable()->after('receipt_reference');
            $table->foreign('verified_by')->references('id_petugas')->on('petugas')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verified_at', 'receipt_reference', 'rejection_reason']);
        });
    }
};
