<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('promo_member',function(Blueprint $t){$t->unsignedInteger('quota')->nullable()->after('maksimum_benefit');$t->unsignedInteger('usage_per_member')->nullable()->after('quota');$t->boolean('member_only')->default(true)->after('usage_per_member');});Schema::create('promo_redemptions',function(Blueprint $t){$t->id('id_redemption');$t->unsignedBigInteger('id_promo');$t->unsignedBigInteger('id_pelanggan');$t->unsignedBigInteger('id_transaksi');$t->decimal('subtotal',15,2);$t->decimal('discount',15,2);$t->decimal('total_final',15,2);$t->dateTime('used_at');$t->timestamps();$t->foreign('id_promo')->references('id_promo')->on('promo_member')->cascadeOnDelete();$t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->cascadeOnDelete();$t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->cascadeOnDelete();$t->unique(['id_promo','id_transaksi'],'uq_promo_transaction');$t->index(['id_promo','id_pelanggan'],'idx_promo_customer');});}
 public function down():void{Schema::dropIfExists('promo_redemptions');Schema::table('promo_member',function(Blueprint $t){$t->dropColumn(['quota','usage_per_member','member_only']);});}
};
