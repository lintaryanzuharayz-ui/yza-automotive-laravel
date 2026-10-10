<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('transaksi',function(Blueprint $t):void{$t->unsignedBigInteger('id_promo')->nullable()->after('id_petugas');$t->string('promo_code_snapshot',50)->nullable();$t->string('promo_name_snapshot',150)->nullable();$t->string('promo_type_snapshot',30)->nullable();$t->decimal('promo_value_snapshot',15,2)->nullable();$t->index('id_promo','idx_transaksi_promo');$t->foreign('id_promo','fk_transaksi_promo')->references('id_promo')->on('promo_member')->nullOnDelete()->cascadeOnUpdate();});}
 public function down():void{Schema::table('transaksi',function(Blueprint $t):void{$t->dropForeign('fk_transaksi_promo');$t->dropIndex('idx_transaksi_promo');$t->dropColumn(['id_promo','promo_code_snapshot','promo_name_snapshot','promo_type_snapshot','promo_value_snapshot']);});}
};
