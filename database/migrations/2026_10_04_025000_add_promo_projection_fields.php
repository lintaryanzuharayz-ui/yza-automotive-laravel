<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('promo_member',function(Blueprint $t):void{$t->unsignedInteger('projection_transactions')->nullable();$t->decimal('projection_revenue',15,2)->nullable();$t->decimal('projection_discount',15,2)->nullable();$t->decimal('projection_margin',15,2)->nullable();$t->unsignedTinyInteger('spk_score')->nullable();$t->string('spk_recommendation',40)->nullable();$t->decimal('estimated_cost_rate',8,4)->nullable();});}
 public function down():void{Schema::table('promo_member',function(Blueprint $t):void{$t->dropColumn(['projection_transactions','projection_revenue','projection_discount','projection_margin','spk_score','spk_recommendation','estimated_cost_rate']);});}
};
