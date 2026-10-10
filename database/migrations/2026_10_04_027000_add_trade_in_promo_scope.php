<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration { public function up():void{DB::statement("ALTER TABLE promo_member MODIFY berlaku_untuk ENUM('semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in') NOT NULL DEFAULT 'semua'");} public function down():void{DB::statement("ALTER TABLE promo_member MODIFY berlaku_untuk ENUM('semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil') NOT NULL DEFAULT 'semua'");} };
