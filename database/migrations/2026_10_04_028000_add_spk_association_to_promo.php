<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void{Schema::table('promo_member',function(Blueprint $t):void{$t->string('approved_spk_simulation_id',80)->nullable()->index();});} public function down():void{Schema::table('promo_member',function(Blueprint $t):void{$t->dropIndex(['approved_spk_simulation_id']);$t->dropColumn('approved_spk_simulation_id');});} };
