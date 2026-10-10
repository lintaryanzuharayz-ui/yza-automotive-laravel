<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('promo_simulations', function(Blueprint $t){$t->id('id_promo_simulation');$t->string('simulation_id',80)->unique();$t->unsignedBigInteger('created_by')->nullable();$t->string('scope',40)->default('semua');$t->string('discount_type',20);$t->decimal('discount_value',15,2);$t->decimal('max_discount',15,2)->nullable();$t->decimal('min_transaction',15,2)->default(0);$t->json('input_payload')->nullable();$t->json('result_payload');$t->unsignedTinyInteger('score')->nullable();$t->string('status',40)->nullable();$t->string('recommendation',255)->nullable();$t->timestamps();$t->index(['scope','status']);}); } public function down(): void { Schema::dropIfExists('promo_simulations'); } };
