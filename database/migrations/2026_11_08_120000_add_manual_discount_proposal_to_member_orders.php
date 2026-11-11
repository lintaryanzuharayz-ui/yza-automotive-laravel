<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pesanan_mobil_member', function (Blueprint $table) {
            $table->string('manual_discount_type', 20)->default('none');
            $table->decimal('manual_discount_value', 15, 2)->default(0);
            $table->decimal('manual_discount_amount', 15, 2)->default(0);
            $table->string('manual_discount_status', 20)->default('none');
            $table->text('manual_discount_reason')->nullable();
            $table->unsignedBigInteger('manual_discount_requested_by')->nullable();
            $table->unsignedBigInteger('manual_discount_reviewed_by')->nullable();
            $table->dateTime('manual_discount_reviewed_at')->nullable();
            $table->json('manual_discount_history')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('pesanan_mobil_member', fn (Blueprint $table) => $table->dropColumn([
            'manual_discount_type','manual_discount_value','manual_discount_amount','manual_discount_status',
            'manual_discount_reason','manual_discount_requested_by','manual_discount_reviewed_by',
            'manual_discount_reviewed_at','manual_discount_history',
        ]));
    }
};
