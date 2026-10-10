<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('promo_redemptions',function(Blueprint $t):void{$t->enum('status',['reserved','completed','released'])->default('completed')->after('total_final');$t->index(['id_promo','status'],'idx_redemptions_promo_status');});}
 public function down():void{Schema::table('promo_redemptions',function(Blueprint $t):void{$t->dropIndex('idx_redemptions_promo_status');$t->dropColumn('status');});}
};
