<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jenis_servis', function (Blueprint $table): void {
            $table->unsignedSmallInteger('duration_minutes')->default(60)->after('estimasi_harga');
        });

        // Conservative defaults for the six existing service types. Admin may tune these later.
        $durations = [
            'Servis Berkala' => 120,
            'Ganti Oli' => 60,
            'Tune Up' => 120,
            'Servis Rem' => 120,
            'Servis AC' => 120,
            'Kelistrikan' => 120,
        ];
        foreach ($durations as $name => $minutes) {
            DB::table('jenis_servis')->where('nama_servis', $name)->update(['duration_minutes' => $minutes]);
        }

        Schema::create('service_operating_hours', function (Blueprint $table): void {
            $table->id('id_service_operating_hour');
            $table->unsignedTinyInteger('day_of_week')->unique('uq_service_operating_day'); // ISO: 1 Monday ... 7 Sunday
            $table->boolean('is_open')->default(false);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->unsignedSmallInteger('slot_interval_minutes')->default(60);
            $table->timestamps();
        });

        $now = now();
        for ($day = 1; $day <= 7; $day++) {
            DB::table('service_operating_hours')->insert([
                'day_of_week' => $day,
                'is_open' => $day <= 5,
                'open_time' => $day <= 5 ? '08:00:00' : null,
                'close_time' => $day <= 5 ? '17:00:00' : null,
                'break_start' => $day <= 5 ? '12:00:00' : null,
                'break_end' => $day <= 5 ? '13:00:00' : null,
                'slot_interval_minutes' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::create('service_blackout_dates', function (Blueprint $table): void {
            $table->id('id_service_blackout_date');
            $table->date('date')->unique('uq_service_blackout_date');
            $table->string('reason', 255);
            $table->boolean('is_closed')->default(true);
            $table->time('custom_open_time')->nullable();
            $table->time('custom_close_time')->nullable();
            $table->timestamps();
        });

        DB::table('pengaturan')->insertOrIgnore([
            'kunci_pengaturan' => 'service_capacity_per_slot',
            'nilai' => '3',
            'deskripsi' => 'Kapasitas kendaraan aktif bengkel per slot waktu',
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('pengaturan')->where('kunci_pengaturan', 'service_capacity_per_slot')->delete();
        Schema::dropIfExists('service_blackout_dates');
        Schema::dropIfExists('service_operating_hours');
        Schema::table('jenis_servis', function (Blueprint $table): void {
            $table->dropColumn('duration_minutes');
        });
    }
};
