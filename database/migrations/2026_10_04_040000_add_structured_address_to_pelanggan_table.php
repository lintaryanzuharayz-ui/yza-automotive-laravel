<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table): void {
            $table->string('province_code', 10)->nullable()->after('alamat');
            $table->string('province_name', 120)->nullable()->after('province_code');
            $table->string('city_code', 10)->nullable()->after('province_name');
            $table->string('city_name', 160)->nullable()->after('city_code');
            $table->string('district_code', 12)->nullable()->after('city_name');
            $table->string('district_name', 160)->nullable()->after('district_code');
            $table->string('village_code', 16)->nullable()->after('district_name');
            $table->string('village_name', 160)->nullable()->after('village_code');
            $table->string('address_detail', 500)->nullable()->after('village_name');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table): void {
            $table->dropColumn(['province_code', 'province_name', 'city_code', 'city_name', 'district_code', 'district_name', 'village_code', 'village_name', 'address_detail']);
        });
    }
};
