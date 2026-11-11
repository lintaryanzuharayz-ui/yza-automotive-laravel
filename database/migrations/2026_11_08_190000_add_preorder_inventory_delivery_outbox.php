<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pre_order_mobil_member', function (Blueprint $table) {
            $table->boolean('stock_available')->default(false);
            $table->unsignedInteger('stock_generation')->default(0);
            $table->unsignedBigInteger('matched_mobil_id')->nullable();
            $table->timestamp('stock_checked_at')->nullable();
        });
        // Durable inventory events and real pre-order activity share one append-only stream.
        Schema::create('preorder_inventory_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 160)->unique();
            $table->string('kind', 40);
            $table->unsignedBigInteger('preorder_id')->nullable()->index();
            $table->unsignedBigInteger('mobil_id')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->unsignedBigInteger('notification_id')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('preorder_inventory_events');
        Schema::table('pre_order_mobil_member', fn (Blueprint $table) => $table->dropColumn(['stock_available', 'stock_generation', 'matched_mobil_id', 'stock_checked_at']));
    }
};
