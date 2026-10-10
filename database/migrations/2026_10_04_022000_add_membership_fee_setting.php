<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void { DB::table('pengaturan')->insertOrIgnore(['kunci_pengaturan'=>'membership_activation_fee','nilai'=>'249000','deskripsi'=>'Biaya aktivasi YZA Member (sekali bayar)','updated_at'=>now()]); }
    public function down(): void { DB::table('pengaturan')->where('kunci_pengaturan','membership_activation_fee')->delete(); }
};
